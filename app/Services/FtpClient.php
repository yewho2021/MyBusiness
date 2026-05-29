<?php

namespace App\Services;

/**
 * FTP Client wrapper.
 * Uses PHP ftp_* extension if available, falls back to cURL for FTP.
 *
 * PERFORMANCE: cURL mode keeps a PERSISTENT handle — one TCP+TLS connection
 * for all operations. Directory creation is cached to avoid duplicate MKD calls.
 */
class FtpClient
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected bool $ssl;
    protected string $driver;

    protected $ftpConn = null;
    protected $curlHandle = null;
    protected array $dirCache = [];

    public function __construct(string $host, int $port, string $username, string $password, bool $ssl = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->ssl = $ssl;

        if (function_exists('ftp_connect')) {
            $this->driver = 'ftp';
        } elseif (function_exists('curl_init')) {
            $this->driver = 'curl';
        } else {
            throw new \Exception('Neither PHP FTP extension nor cURL is available.');
        }
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    // ═══════════════════════════════════════════
    // CONNECT
    // ═══════════════════════════════════════════

    public function connect(): void
    {
        if ($this->driver === 'ftp') {
            $this->ftpConn = $this->ssl
                ? @ftp_ssl_connect($this->host, $this->port, 30)
                : @ftp_connect($this->host, $this->port, 30);

            if (!$this->ftpConn) {
                throw new \Exception("FTP: Cannot connect to {$this->host}:{$this->port}");
            }
            if (!@ftp_login($this->ftpConn, $this->username, $this->password)) {
                throw new \Exception("FTP: Login failed");
            }
            ftp_pasv($this->ftpConn, true);
        } else {
            $this->curlHandle = $this->createCurlHandle('/');
            curl_setopt($this->curlHandle, CURLOPT_NOBODY, true);
            curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_exec($this->curlHandle);
            $err = curl_error($this->curlHandle);
            if ($err) throw new \Exception("FTP cURL: {$err}");
        }
    }

    // ═══════════════════════════════════════════
    // DISCONNECT
    // ═══════════════════════════════════════════

    public function disconnect(): void
    {
        if ($this->driver === 'ftp' && $this->ftpConn) {
            @ftp_close($this->ftpConn);
            $this->ftpConn = null;
        }
        if ($this->curlHandle) {
            curl_close($this->curlHandle);
            $this->curlHandle = null;
        }
        $this->dirCache = [];
    }

    // ═══════════════════════════════════════════
    // LIST DIRECTORY
    // ═══════════════════════════════════════════

    public function nlist(string $path): array|false
    {
        if ($this->driver === 'ftp') {
            return @ftp_nlist($this->ftpConn, $path);
        }

        $ch = $this->getCurl($path . '/');
        $this->resetCurlOpts($ch);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $err = curl_error($ch);

        if ($err || $result === false) return false;

        $lines = array_filter(explode("\n", trim($result)));
        $files = [];
        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', trim($line));
            $name = end($parts);
            if ($name && $name !== '.' && $name !== '..') {
                $files[] = rtrim($path, '/') . '/' . $name;
            }
        }
        return $files;
    }

    // ═══════════════════════════════════════════
    // MKDIR (recursive + cached)
    // ═══════════════════════════════════════════

    public function mkdirRecursive(string $dir): void
    {
        if (isset($this->dirCache[$dir])) return;

        $parts = array_filter(explode('/', $dir));
        $path = '';

        foreach ($parts as $part) {
            $path .= '/' . $part;
            if (isset($this->dirCache[$path])) continue;

            if ($this->driver === 'ftp') {
                @ftp_mkdir($this->ftpConn, $path);
            } else {
                $ch = $this->getCurl('/');
                $this->resetCurlOpts($ch);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_QUOTE, ["*MKD {$path}"]);
                @curl_exec($ch);
            }

            $this->dirCache[$path] = true;
        }
    }

    // ═══════════════════════════════════════════
    // UPLOAD FILE
    // ═══════════════════════════════════════════

    public function upload(string $remotePath, string $localPath): bool
    {
        if ($this->driver === 'ftp') {
            return @ftp_put($this->ftpConn, $remotePath, $localPath, FTP_BINARY);
        }

        $fh = fopen($localPath, 'r');
        if (!$fh) return false;

        $size = filesize($localPath);

        $ch = $this->getCurl($remotePath);
        $this->resetCurlOpts($ch);
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILE, $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $err = curl_error($ch);
        fclose($fh);

        return !$err;
    }

    /**
     * Upload from string content.
     */
    public function uploadContent(string $remotePath, string $content): bool
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'ftpup');
        file_put_contents($tmpFile, $content);
        $ok = $this->upload($remotePath, $tmpFile);
        @unlink($tmpFile);
        return $ok;
    }

    // ═══════════════════════════════════════════
    // DELETE FILE
    // ═══════════════════════════════════════════

    public function delete(string $path): bool
    {
        if ($this->driver === 'ftp') {
            return @ftp_delete($this->ftpConn, $path);
        }

        $ch = $this->getCurl('/');
        $this->resetCurlOpts($ch);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_QUOTE, ["DELE {$path}"]);
        @curl_exec($ch);
        $err = curl_error($ch);
        return !$err;
    }

    // ═══════════════════════════════════════════
    // RMDIR
    // ═══════════════════════════════════════════

    public function rmdir(string $path): bool
    {
        if ($this->driver === 'ftp') {
            return @ftp_rmdir($this->ftpConn, $path);
        }

        $ch = $this->getCurl('/');
        $this->resetCurlOpts($ch);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_QUOTE, ["RMD {$path}"]);
        @curl_exec($ch);
        return true;
    }

    // ═══════════════════════════════════════════
    // DELETE RECURSIVE
    // ═══════════════════════════════════════════

    public function deleteRecursive(string $path, ?callable $onDelete = null): int
    {
        $count = 0;
        $list = $this->nlist($path);
        if ($list === false) return 0;

        foreach ($list as $item) {
            $basename = basename($item);
            if ($basename === '.' || $basename === '..') continue;

            $fullPath = rtrim($path, '/') . '/' . $basename;

            if ($this->delete($fullPath)) {
                $count++;
                if ($onDelete) $onDelete($fullPath, 'file');
                continue;
            }

            $count += $this->deleteRecursive($fullPath, $onDelete);
            $this->rmdir($fullPath);
            $count++;
            if ($onDelete) $onDelete($fullPath, 'dir');
        }

        return $count;
    }

    // ═══════════════════════════════════════════
    // WRITE TEST
    // ═══════════════════════════════════════════

    public function testWrite(string $path): bool
    {
        $testFile = rtrim($path, '/') . '/.deploy_test_' . time();
        if ($this->uploadContent($testFile, 'test')) {
            $this->delete($testFile);
            return true;
        }
        return false;
    }

    // ═══════════════════════════════════════════
    // CURL HELPERS (persistent handle)
    // ═══════════════════════════════════════════

    /**
     * Create a fresh cURL handle with base FTP settings.
     */
    protected function createCurlHandle(string $path): \CurlHandle
    {
        $url = "ftp://{$this->host}:{$this->port}" . $path;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true);

        // Keep TCP connection alive between requests
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
        curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 30);
        curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 15);

        if ($this->ssl) {
            curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        return $ch;
    }

    /**
     * Get persistent cURL handle with URL updated.
     * Reuses the same TCP+TLS connection across all operations.
     */
    protected function getCurl(string $path): \CurlHandle
    {
        $url = "ftp://{$this->host}:{$this->port}" . $path;

        if ($this->curlHandle === null) {
            $this->curlHandle = $this->createCurlHandle($path);
        } else {
            curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        }

        return $this->curlHandle;
    }

    /**
     * Reset cURL options that vary between operations.
     * Keeps connection-level settings (auth, SSL, keepalive) intact.
     */
    protected function resetCurlOpts(\CurlHandle $ch): void
    {
        curl_setopt($ch, CURLOPT_UPLOAD, false);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_QUOTE, []);
        curl_setopt($ch, CURLOPT_POSTQUOTE, []);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, null);
        curl_setopt($ch, CURLOPT_INFILE, null);
        curl_setopt($ch, CURLOPT_INFILESIZE, -1);
    }
}
