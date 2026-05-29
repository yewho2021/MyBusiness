<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Services\ConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigurationController extends Controller
{
    protected ConfigurationService $uploadService;

    public function __construct(ConfigurationService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Show the configuration settings page.
     */
    public function index(Request $request)
    {
        $groups = [
            'brand'        => ['label' => 'Brand',        'icon' => 'fas fa-palette'],
            'colors'       => ['label' => 'Colors',       'icon' => 'fas fa-swatchbook'],
            'sidebar'      => ['label' => 'Sidebar',      'icon' => 'fas fa-columns'],
            'header'       => ['label' => 'Header',       'icon' => 'fas fa-window-maximize'],
            'typography'   => ['label' => 'Typography',   'icon' => 'fas fa-font'],
            'layout'       => ['label' => 'Layout',       'icon' => 'fas fa-th-large'],
            'login'        => ['label' => 'Login Page',   'icon' => 'fas fa-sign-in-alt'],
            'login_access' => ['label' => 'Login Access', 'icon' => 'fas fa-shield-alt'],
            'email'        => ['label' => 'Email',        'icon' => 'fas fa-envelope'],
            'cache'        => ['label' => 'Cache',        'icon' => 'fas fa-broom'],
            'advanced'     => ['label' => 'Advanced',     'icon' => 'fas fa-cog'],
        ];

        $activeTab = $request->get('tab', 'brand');
        if (!isset($groups[$activeTab])) $activeTab = 'brand';

        // Load all rows grouped
        $configData = [];
        foreach (array_keys($groups) as $group) {
            $configData[$group] = Configuration::getGroupRows($group);
        }

        return view('admin.pages.settings.configuration', compact('groups', 'activeTab', 'configData'));
    }

    /**
     * Bulk save settings from the form.
     */
    public function update(Request $request)
    {
        $adminId = $request->attributes->get('admin_id');
        $tab = $request->input('_tab', 'brand');

        // Collect all key-value pairs from the form
        $allGroups = ['brand', 'colors', 'sidebar', 'header', 'typography', 'layout', 'login', 'login_access', 'email', 'cache', 'advanced'];
        $updated = 0;
        $validationErrors = [];

        foreach ($allGroups as $group) {
            $groupData = $request->input($group, []);
            if (is_array($groupData)) {
                foreach ($groupData as $key => $value) {
                    $row = Configuration::where('group', $group)->where('key', $key)->first();
                    if ($row) {
                        // Skip image fields — handled separately via AJAX
                        if ($row->type === 'image') continue;

                        // Validate by type before saving
                        $error = $this->validateConfigValue($row, $value);
                        if ($error) {
                            $validationErrors[] = "{$row->label}: {$error}";
                            continue; // Skip this value, don't save
                        }

                        // Encrypt sensitive keys (e.g. mail_password) before saving
                        $row->value = Configuration::encryptIfSensitive($key, $value);
                        $row->updated_at = now();
                        $row->updated_by = $adminId;
                        $row->save();
                        $updated++;
                    }
                }
            }
        }

        Configuration::clearCache();

        if (!empty($validationErrors)) {
            return redirect()
                ->route('admin.settings.configuration', ['tab' => $tab])
                ->with('warning', "Saved {$updated} values, but " . count($validationErrors) . " skipped: " . implode(' | ', $validationErrors));
        }

        return redirect()
            ->route('admin.settings.configuration', ['tab' => $tab])
            ->with('success', "Settings saved successfully ({$updated} values updated).");
    }

    /**
     * AJAX: Upload an image (logo, favicon, login bg).
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'key'  => 'required|string',
            'file' => 'required|file|mimes:png,jpg,jpeg,gif,svg,ico,webp|max:2048',
        ]);

        $key = $request->input('key');
        $row = Configuration::where('key', $key)->where('type', 'image')->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Invalid config key.'], 400);
        }

        try {
            $oldPath = $row->value;
            $prefix = str_replace('_', '-', $key);
            $newPath = $this->uploadService->handleUpload($request->file('file'), $oldPath, $prefix);

            $row->value = $newPath;
            $row->updated_at = now();
            $row->updated_by = $request->attributes->get('admin_id');
            $row->save();

            // Auto-switch logo_type when logo image is uploaded
            if ($key === 'logo_image') {
                Configuration::set('logo_type', 'image', $request->attributes->get('admin_id'));
            }

            Configuration::clearCache();

            return response()->json([
                'success' => true,
                'path'    => $newPath,
                'url'     => asset($newPath),
                'message' => 'Image uploaded successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Remove an uploaded image.
     */
    public function removeImage(Request $request)
    {
        $key = $request->input('key');
        $row = Configuration::where('key', $key)->where('type', 'image')->first();

        if (!$row || !$row->value) {
            return response()->json(['success' => false, 'message' => 'No image to remove.'], 400);
        }

        $this->uploadService->deleteImage($row->value);
        $row->value = null;
        $row->updated_at = now();
        $row->save();

        // Auto-switch logo_type back to icon when logo image is removed
        if ($key === 'logo_image') {
            Configuration::set('logo_type', 'icon');
        }

        Configuration::clearCache();

        return response()->json(['success' => true, 'message' => 'Image removed.']);
    }

    /**
     * Reset a group to its default values.
     */
    public function resetGroup(Request $request)
    {
        $group = $request->input('group');
        $mode = $request->input('mode', 'defaults');
        $validGroups = ['brand', 'colors', 'sidebar', 'header', 'typography', 'layout', 'login', 'login_access', 'email', 'cache', 'advanced'];

        if (!in_array($group, $validGroups)) {
            return back()->with('error', 'Invalid group.');
        }

        if ($mode === 'blank') {
            $count = DB::table('tbl_configuration')
                ->where('group', $group)
                ->update(['value' => '', 'updated_at' => now()]);
            Configuration::clearCache();
            return redirect()
                ->route('admin.settings.configuration', ['tab' => $group])
                ->with('success', "Cleared {$count} settings in '{$group}' to blank.");
        }

        $count = Configuration::resetGroup($group);

        return redirect()
            ->route('admin.settings.configuration', ['tab' => $group])
            ->with('success', "Reset {$count} settings in '{$group}' to defaults.");
    }

    /**
     * Export all settings as JSON.
     */
    public function exportConfig()
    {
        $rows = Configuration::where('is_active', 1)
            ->where('type', '!=', 'image')
            ->get(['group', 'key', 'value']);

        $data = [];
        foreach ($rows as $r) {
            $data[$r->group][$r->key] = $r->value;
        }

        $filename = 'config_export_' . date('Y-m-d_His') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Content-Type', 'application/json');
    }

    /**
     * Import settings from JSON.
     */
    public function importConfig(Request $request)
    {
        $request->validate(['config_file' => 'required|file|mimes:json,txt|max:1024']);

        try {
            $json = file_get_contents($request->file('config_file')->getRealPath());
            $data = json_decode($json, true);

            if (!is_array($data)) {
                return back()->with('error', 'Invalid JSON format.');
            }

            $updated = 0;
            $adminId = $request->attributes->get('admin_id');

            foreach ($data as $group => $keys) {
                if (!is_array($keys)) continue;
                foreach ($keys as $key => $value) {
                    $row = Configuration::where('group', $group)->where('key', $key)->first();
                    if ($row && $row->type !== 'image') {
                        // Encrypt sensitive keys (e.g. mail_password) before saving
                        $row->value = Configuration::encryptIfSensitive($key, $value);
                        $row->updated_at = now();
                        $row->updated_by = $adminId;
                        $row->save();
                        $updated++;
                    }
                }
            }

            Configuration::clearCache();

            return redirect()
                ->route('admin.settings.configuration')
                ->with('success', "Imported {$updated} settings from JSON.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Test email server connection.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'protocol'     => 'required|in:smtp,imap,pop3',
            'host'         => 'required|string|max:255',
            'port'         => 'required|integer|min:1|max:65535',
            'encryption'   => 'required|in:none,ssl,tls',
            'auth_enabled' => 'nullable|boolean',
            'username'     => 'nullable|string|max:255',
            'password'     => 'nullable|string|max:255',
        ]);

        $protocol     = $request->input('protocol');
        $host         = $request->input('host');
        $port         = (int) $request->input('port');
        $encryption   = $request->input('encryption');
        $authEnabled  = (bool) $request->input('auth_enabled', true);
        $username     = $request->input('username', '');
        $password     = $request->input('password', '');

        $steps = [];
        $startTime = microtime(true);

        try {
            // ── Step 1: Socket connection ────────────────────
            $steps[] = ['step' => 'Connecting', 'detail' => "{$host}:{$port} ({$encryption})"];

            $socketHost = $host;
            if ($encryption === 'ssl') {
                $socketHost = 'ssl://' . $host;
            }

            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                    'allow_self_signed'=> true,
                ],
            ]);

            $errno  = 0;
            $errstr = '';
            $socket = @stream_socket_client(
                "{$socketHost}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$socket) {
                throw new \Exception("Connection failed: {$errstr} (error {$errno})");
            }

            stream_set_timeout($socket, 10);
            $steps[] = ['step' => 'Connected', 'detail' => 'Socket opened successfully'];

            // ── Step 2: Read server banner (may be multiline: 220-... / 220 ...) ──
            $banner = $this->socketReadMulti($socket);
            $steps[] = ['step' => 'Banner', 'detail' => trim($banner)];

            // ── Step 3: Protocol handshake ───────────────────
            if ($protocol === 'smtp') {
                $steps = array_merge($steps, $this->testSmtp($socket, $host, $encryption, $authEnabled, $username, $password));
            } elseif ($protocol === 'imap') {
                $steps = array_merge($steps, $this->testImap($socket, $username, $password));
            } elseif ($protocol === 'pop3') {
                $steps = array_merge($steps, $this->testPop3($socket, $username, $password));
            }

            fclose($socket);

            $elapsed = round((microtime(true) - $startTime) * 1000);
            $steps[] = ['step' => 'Success', 'detail' => "Connection test passed ({$elapsed}ms)"];

            return response()->json(['success' => true, 'steps' => $steps]);

        } catch (\Exception $e) {
            if (isset($socket) && is_resource($socket)) {
                fclose($socket);
            }

            $elapsed = round((microtime(true) - $startTime) * 1000);
            $steps[] = ['step' => 'Error', 'detail' => $e->getMessage() . " ({$elapsed}ms)"];

            return response()->json(['success' => false, 'steps' => $steps, 'error' => $e->getMessage()], 422);
        }
    }

    // ── Email test helpers ───────────────────────────────────

    protected function testSmtp($socket, string $host, string $encryption, bool $authEnabled, string $username, string $password): array
    {
        $steps = [];

        // EHLO
        $this->socketWrite($socket, "EHLO {$host}");
        $ehloReply = $this->socketReadMulti($socket);
        $steps[] = ['step' => 'EHLO', 'detail' => trim($ehloReply)];

        // Parse EHLO capabilities
        $capabilities = strtoupper($ehloReply);

        // STARTTLS (for TLS on non-SSL ports)
        if ($encryption === 'tls') {
            if (strpos($capabilities, 'STARTTLS') === false) {
                $steps[] = ['step' => 'Warning', 'detail' => 'Server did not advertise STARTTLS — attempting anyway'];
            }

            $this->socketWrite($socket, "STARTTLS");
            $tlsReply = $this->socketRead($socket);
            $steps[] = ['step' => 'STARTTLS', 'detail' => trim($tlsReply)];

            if (!str_starts_with(trim($tlsReply), '220')) {
                throw new \Exception("STARTTLS rejected: " . trim($tlsReply));
            }

            $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$crypto) {
                throw new \Exception("TLS handshake failed — server may not support STARTTLS on this port.");
            }
            $steps[] = ['step' => 'TLS', 'detail' => 'Encryption established'];

            // Re-EHLO after TLS (capabilities may change)
            $this->socketWrite($socket, "EHLO {$host}");
            $ehloReply2 = $this->socketReadMulti($socket);
            $capabilities = strtoupper($ehloReply2);
            $steps[] = ['step' => 'EHLO (TLS)', 'detail' => trim($ehloReply2)];
        }

        // AUTH — only if user toggled authentication ON
        if ($authEnabled && $username && $password) {
            // Check which AUTH methods the server supports
            $hasAuthLogin = (bool) preg_match('/AUTH[= ].*\bLOGIN\b/i', $capabilities);
            $hasAuthPlain = (bool) preg_match('/AUTH[= ].*\bPLAIN\b/i', $capabilities);

            $steps[] = ['step' => 'Auth Check', 'detail' => 'Server supports: '
                . ($hasAuthLogin ? 'LOGIN ' : '')
                . ($hasAuthPlain ? 'PLAIN ' : '')
                . (!$hasAuthLogin && !$hasAuthPlain ? 'NONE detected (will attempt LOGIN)' : '')
            ];

            $authenticated = false;

            // Try AUTH PLAIN first (more widely supported, single roundtrip)
            if ($hasAuthPlain && !$authenticated) {
                $authString = base64_encode("\0{$username}\0{$password}");
                $this->socketWrite($socket, "AUTH PLAIN {$authString}");
                $plainReply = $this->socketRead($socket);
                $steps[] = ['step' => 'AUTH PLAIN', 'detail' => trim($plainReply)];

                if (str_starts_with(trim($plainReply), '235')) {
                    $authenticated = true;
                    $steps[] = ['step' => 'Authenticated', 'detail' => 'SMTP login successful (PLAIN)'];
                } else {
                    $steps[] = ['step' => 'AUTH PLAIN', 'detail' => 'Failed — ' . trim($plainReply)];
                }
            }

            // Try AUTH LOGIN as fallback
            if (!$authenticated) {
                $this->socketWrite($socket, "AUTH LOGIN");
                $authReply = $this->socketRead($socket);

                if (str_starts_with(trim($authReply), '334')) {
                    $steps[] = ['step' => 'AUTH LOGIN', 'detail' => trim($authReply)];

                    $this->socketWrite($socket, base64_encode($username));
                    $userReply = $this->socketRead($socket);

                    $this->socketWrite($socket, base64_encode($password));
                    $passReply = $this->socketRead($socket);
                    $steps[] = ['step' => 'Login', 'detail' => trim($passReply)];

                    if (str_starts_with(trim($passReply), '235')) {
                        $authenticated = true;
                        $steps[] = ['step' => 'Authenticated', 'detail' => 'SMTP login successful (LOGIN)'];
                    } else {
                        throw new \Exception("Authentication failed: " . trim($passReply));
                    }
                } else {
                    // Neither AUTH method worked
                    if (!$authenticated) {
                        throw new \Exception("Server does not support AUTH LOGIN or AUTH PLAIN. Reply: " . trim($authReply));
                    }
                }
            }
        } else {
            $steps[] = ['step' => 'Auth', 'detail' => 'Skipped — authentication not enabled'];
        }

        $this->socketWrite($socket, "QUIT");

        return $steps;
    }

    protected function testImap($socket, string $username, string $password): array
    {
        $steps = [];

        if ($username && $password) {
            $tag = 'A001';
            $this->socketWrite($socket, "{$tag} LOGIN " . $this->imapQuote($username) . " " . $this->imapQuote($password));
            $loginReply = $this->socketReadTagged($socket, $tag);
            $steps[] = ['step' => 'LOGIN', 'detail' => trim($loginReply)];

            if (stripos($loginReply, "{$tag} OK") === false) {
                throw new \Exception("IMAP authentication failed: " . trim($loginReply));
            }
            $steps[] = ['step' => 'Authenticated', 'detail' => 'IMAP login successful'];

            // List mailboxes
            $tag = 'A002';
            $this->socketWrite($socket, "{$tag} LIST \"\" \"*\"");
            $listReply = $this->socketReadTagged($socket, $tag);
            $mailboxes = substr_count($listReply, '* LIST');
            $steps[] = ['step' => 'Mailboxes', 'detail' => "{$mailboxes} mailbox(es) found"];

            $this->socketWrite($socket, "A003 LOGOUT");
        } else {
            $steps[] = ['step' => 'Skip Auth', 'detail' => 'No credentials — connection-only test'];
        }

        return $steps;
    }

    protected function testPop3($socket, string $username, string $password): array
    {
        $steps = [];

        if ($username && $password) {
            $this->socketWrite($socket, "USER {$username}");
            $userReply = $this->socketRead($socket);
            $steps[] = ['step' => 'USER', 'detail' => trim($userReply)];

            if (!str_starts_with(trim($userReply), '+OK')) {
                throw new \Exception("POP3 USER rejected: " . trim($userReply));
            }

            $this->socketWrite($socket, "PASS {$password}");
            $passReply = $this->socketRead($socket);
            $steps[] = ['step' => 'PASS', 'detail' => trim($passReply)];

            if (!str_starts_with(trim($passReply), '+OK')) {
                throw new \Exception("POP3 authentication failed: " . trim($passReply));
            }
            $steps[] = ['step' => 'Authenticated', 'detail' => 'POP3 login successful'];

            // STAT
            $this->socketWrite($socket, "STAT");
            $statReply = $this->socketRead($socket);
            $steps[] = ['step' => 'STAT', 'detail' => trim($statReply)];

            $this->socketWrite($socket, "QUIT");
        } else {
            $steps[] = ['step' => 'Skip Auth', 'detail' => 'No credentials — connection-only test'];
        }

        return $steps;
    }

    protected function socketWrite($socket, string $cmd): void
    {
        fwrite($socket, $cmd . "\r\n");
    }

    protected function socketRead($socket): string
    {
        $response = '';
        $line = @fgets($socket, 4096);
        if ($line !== false) $response = $line;
        return $response;
    }

    protected function socketReadMulti($socket): string
    {
        $response = '';
        while ($line = @fgets($socket, 4096)) {
            $response .= $line;
            // SMTP multi-line: continues if char 4 is '-', ends when char 4 is ' '
            if (isset($line[3]) && $line[3] === ' ') break;
            if (feof($socket)) break;
        }
        return $response;
    }

    protected function socketReadTagged($socket, string $tag): string
    {
        $response = '';
        $maxLines = 200;
        while ($maxLines-- > 0) {
            $line = @fgets($socket, 4096);
            if ($line === false) break;
            $response .= $line;
            if (str_starts_with($line, $tag . ' ')) break;
            if (feof($socket)) break;
        }
        return $response;
    }

    /**
     * AJAX: Clear system caches.
     */
    public function clearSystemCache(Request $request)
    {
        $request->validate([
            'targets' => 'required|array',
            'targets.*' => 'string|in:views,cache,sessions,config,routes,logs,patch_backups,opcache,db_cache',
        ]);

        $targets = $request->input('targets');
        $results = [];

        foreach ($targets as $target) {
            $result = ['target' => $target, 'label' => '', 'cleared' => 0, 'size_freed' => 0, 'status' => 'ok', 'error' => null];

            try {
                switch ($target) {
                    case 'views':
                        $result['label'] = 'Compiled Blade Views';
                        $dir = storage_path('framework/views');
                        $result = array_merge($result, $this->clearDirectory($dir, '*.php'));
                        break;

                    case 'cache':
                        $result['label'] = 'Application Cache';
                        $dir = storage_path('framework/cache/data');
                        if (!is_dir($dir)) $dir = storage_path('framework/cache');
                        $result = array_merge($result, $this->clearDirectoryRecursive($dir));
                        // Also clear Configuration model cache
                        \App\Models\Configuration::clearCache();
                        break;

                    case 'sessions':
                        $result['label'] = 'Session Files';
                        $dir = storage_path('framework/sessions');
                        $result = array_merge($result, $this->clearDirectory($dir, '*'));
                        break;

                    case 'config':
                        $result['label'] = 'Config & Services Cache';
                        $configFiles = ['config.php', 'services.php', 'packages.php', 'routes-v7.php'];
                        $cleared = 0;
                        $freed = 0;
                        $fileList = [];
                        foreach ($configFiles as $f) {
                            $path = base_path("bootstrap/cache/{$f}");
                            if (file_exists($path)) {
                                $size = filesize($path);
                                $ok = @unlink($path);
                                $freed += $size;
                                $cleared++;
                                $fileList[] = ['path' => "bootstrap/cache/{$f}", 'size' => $size, 'ok' => $ok];
                            }
                        }
                        $result['cleared'] = $cleared;
                        $result['size_freed'] = $freed;
                        $result['files'] = $fileList;
                        break;

                    case 'routes':
                        $result['label'] = 'Route Cache';
                        $path = base_path('bootstrap/cache/routes-v7.php');
                        $fileList = [];
                        if (file_exists($path)) {
                            $size = filesize($path);
                            $ok = @unlink($path);
                            $result['size_freed'] = $size;
                            $result['cleared'] = 1;
                            $fileList[] = ['path' => 'bootstrap/cache/routes-v7.php', 'size' => $size, 'ok' => $ok];
                        }
                        $result['files'] = $fileList;
                        break;

                    case 'logs':
                        $result['label'] = 'Log Files';
                        $dir = storage_path('logs');
                        $result = array_merge($result, $this->clearDirectory($dir, '*.log'));
                        break;

                    case 'patch_backups':
                        $result['label'] = 'Patch Backups';
                        $dir = storage_path('app/patch_backups');
                        $result = array_merge($result, $this->clearDirectoryRecursive($dir, true));
                        break;

                    case 'opcache':
                        $result['label'] = 'PHP OPcache';
                        if (function_exists('opcache_get_status') && function_exists('opcache_reset')) {
                            $status = @opcache_get_status(false);
                            if (is_array($status) && !empty($status['opcache_enabled'])) {
                                $result['cleared'] = $status['opcache_statistics']['num_cached_scripts'] ?? 0;
                                $result['size_freed'] = $status['memory_usage']['used_memory'] ?? 0;
                                @opcache_reset();
                            } else {
                                $result['cleared'] = 0;
                                $result['size_freed'] = 0;
                            }
                        } else {
                            $result['cleared'] = 0;
                            $result['size_freed'] = 0;
                        }
                        break;

                    case 'db_cache':
                        $result['label'] = 'Database Cache Table';
                        try {
                            $count = DB::table('cache')->count();
                            DB::table('cache')->delete();
                            try { DB::table('cache_locks')->delete(); } catch (\Exception $e) {}
                            $result['cleared'] = $count;
                            $result['size_freed'] = 0;
                        } catch (\Exception $dbErr) {
                            $result['cleared'] = 0;
                            $result['size_freed'] = 0;
                            $result['status'] = 'err';
                            $result['error'] = $dbErr->getMessage();
                        }
                        break;
                }
            } catch (\Exception $e) {
                $result['status'] = 'err';
                $result['error'] = $e->getMessage();
            }

            $results[] = $result;
        }

        $totalCleared = array_sum(array_column($results, 'cleared'));
        $totalFreed = array_sum(array_column($results, 'size_freed'));
        $hasErrors = !empty(array_filter($results, fn($r) => $r['status'] === 'err'));

        return response()->json([
            'success' => !$hasErrors,
            'total_cleared' => $totalCleared,
            'total_freed' => $this->formatCacheSize($totalFreed),
            'total_freed_bytes' => $totalFreed,
            'results' => $results,
        ]);
    }

    protected function clearDirectory(string $dir, string $pattern): array
    {
        $cleared = 0;
        $freed = 0;
        $files = [];
        if (!is_dir($dir)) return ['cleared' => 0, 'size_freed' => 0, 'files' => []];

        foreach (glob("{$dir}/{$pattern}") as $file) {
            if (is_file($file) && basename($file) !== '.gitignore') {
                $size = filesize($file);
                $relativePath = str_replace(base_path() . '/', '', $file);
                $ok = @unlink($file);
                $freed += $size;
                $cleared++;
                $files[] = ['path' => $relativePath, 'size' => $size, 'ok' => $ok];
            }
        }
        return ['cleared' => $cleared, 'size_freed' => $freed, 'files' => $files];
    }

    protected function clearDirectoryRecursive(string $dir, bool $removeDir = false): array
    {
        $cleared = 0;
        $freed = 0;
        $files = [];
        $dirsRemoved = [];
        if (!is_dir($dir)) return ['cleared' => 0, 'size_freed' => 0, 'files' => []];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && $item->getFilename() !== '.gitignore') {
                $size = $item->getSize();
                $relativePath = str_replace(base_path() . '/', '', $item->getRealPath());
                $ok = @unlink($item->getRealPath());
                $freed += $size;
                $cleared++;
                $files[] = ['path' => $relativePath, 'size' => $size, 'ok' => $ok];
            } elseif ($item->isDir() && $removeDir) {
                $rmOk = @rmdir($item->getRealPath());
                if ($rmOk) {
                    $dirsRemoved[] = str_replace(base_path() . '/', '', $item->getRealPath()) . '/';
                }
            }
        }

        if ($removeDir && is_dir($dir)) {
            @rmdir($dir);
        }

        return ['cleared' => $cleared, 'size_freed' => $freed, 'files' => $files, 'dirs_removed' => $dirsRemoved];
    }

    /**
     * Validate a config value based on its type.
     * Returns error message on failure, null on success.
     */
    protected function validateConfigValue($row, $value): ?string
    {
        if ($value === null || $value === '') return null; // Allow empty values

        switch ($row->type) {
            case 'color':
                // Accept: #hex (3, 4, 6, 8 digits), rgb(), rgba(), CSS named colors
                $value = trim($value);
                if (preg_match('/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) return null;
                if (preg_match('/^rgba?\s*\([\d\s,.%]+\)$/i', $value)) return null;
                return 'Invalid color format (use #hex or rgba)';

            case 'number':
                if (!is_numeric($value)) return 'Must be a number';
                $num = (float) $value;
                // Sane ranges for common config types
                if ($num < 0) return 'Cannot be negative';
                if ($num > 9999) return 'Value too large (max 9999)';
                return null;

            case 'select':
                // Validate against allowed options
                $options = $row->getOptionsArray();
                if (!empty($options) && !array_key_exists($value, $options)) {
                    return 'Invalid option selected';
                }
                return null;

            case 'boolean':
                if (!in_array($value, ['0', '1', 'true', 'false', 'enabled', 'disabled'], true)) {
                    return 'Must be a boolean value';
                }
                return null;

            default:
                return null; // text, textarea, code — accept anything
        }
    }

    protected function formatCacheSize(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    protected function imapQuote(string $str): string
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $str) . '"';
    }
}
