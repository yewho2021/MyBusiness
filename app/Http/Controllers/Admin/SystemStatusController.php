<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Models\BackupRun;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SystemStatusController extends Controller implements HasMiddleware
{
    /**
     * Restrict to Administrator role only.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                $admin = $request->attributes->get('admin');
                if (!$admin || !$admin->isAdministrator()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Administrator access required.'], 403);
                    }
                    return redirect()->route('admin.dashboard')->with('error', 'Administrator access required.');
                }
                return $next($request);
            }),
        ];
    }

    public function index()
    {
        $data = [
            'php'       => $this->getPhpInfo(),
            'mysql'     => $this->getMysqlInfo(),
            'disk'      => $this->getDiskInfo(),
            'laravel'   => $this->getLaravelInfo(),
            'opcache'   => $this->getOpcacheInfo(),
            'sessions'  => $this->getSessionInfo(),
            'backup'    => $this->getBackupInfo(),
            'tables'    => $this->getTableSizes(),
            'storage'   => $this->getStorageBreakdown(),
            'errors'    => $this->getRecentErrors(),
        ];

        return view('admin.pages.system.status', $data);
    }

    /**
     * AJAX: Refresh a specific section.
     */
    public function refresh(Request $request)
    {
        $section = $request->input('section', 'all');

        $methods = [
            'php'      => 'getPhpInfo',
            'mysql'    => 'getMysqlInfo',
            'disk'     => 'getDiskInfo',
            'laravel'  => 'getLaravelInfo',
            'opcache'  => 'getOpcacheInfo',
            'sessions' => 'getSessionInfo',
            'backup'   => 'getBackupInfo',
            'tables'   => 'getTableSizes',
            'storage'  => 'getStorageBreakdown',
            'errors'   => 'getRecentErrors',
        ];

        if ($section === 'all') {
            $data = [];
            foreach ($methods as $key => $method) {
                $data[$key] = $this->$method();
            }
            return response()->json($data);
        }

        if (!isset($methods[$section])) {
            return response()->json(['error' => 'Invalid section'], 400);
        }

        return response()->json([$section => $this->{$methods[$section]}()]);
    }

    // ── Data Collectors ──────────────────────────────

    protected function getPhpInfo(): array
    {
        $requiredExtensions = ['pdo_mysql', 'mbstring', 'openssl', 'curl', 'gd', 'zip', 'xml', 'json', 'fileinfo'];
        $extensions = [];
        foreach ($requiredExtensions as $ext) {
            $extensions[$ext] = extension_loaded($ext);
        }

        return [
            'version'           => PHP_VERSION,
            'sapi'              => PHP_SAPI,
            'memory_limit'      => ini_get('memory_limit'),
            'max_execution'     => ini_get('max_execution_time'),
            'upload_max'        => ini_get('upload_max_filesize'),
            'post_max'          => ini_get('post_max_size'),
            'max_input_vars'    => ini_get('max_input_vars'),
            'display_errors'    => ini_get('display_errors'),
            'timezone'          => date_default_timezone_get(),
            'extensions'        => $extensions,
            'zend_version'      => zend_version(),
        ];
    }

    protected function getMysqlInfo(): array
    {
        try {
            $version = DB::select('SELECT VERSION() as v')[0]->v ?? 'Unknown';
            $charset = DB::select("SHOW VARIABLES LIKE 'character_set_database'");
            $collation = DB::select("SHOW VARIABLES LIKE 'collation_database'");
            $maxConn = DB::select("SHOW VARIABLES LIKE 'max_connections'");
            $bufferPool = DB::select("SHOW VARIABLES LIKE 'innodb_buffer_pool_size'");
            $uptime = DB::select("SHOW STATUS LIKE 'Uptime'");

            $uptimeSec = (int) ($uptime[0]->Value ?? 0);
            $days = intdiv($uptimeSec, 86400);
            $hours = intdiv($uptimeSec % 86400, 3600);

            return [
                'version'      => $version,
                'database'     => config('database.connections.mysql.database'),
                'host'         => config('database.connections.mysql.host'),
                'charset'      => $charset[0]->Value ?? 'Unknown',
                'collation'    => $collation[0]->Value ?? 'Unknown',
                'max_connections' => $maxConn[0]->Value ?? 'Unknown',
                'buffer_pool'  => $this->formatBytes((int) ($bufferPool[0]->Value ?? 0)),
                'uptime'       => "{$days}d {$hours}h",
                'connected'    => true,
            ];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getDiskInfo(): array
    {
        $path = base_path();
        $total = @disk_total_space($path);
        $free = @disk_free_space($path);

        if ($total === false) {
            return ['available' => false];
        }

        $used = $total - $free;
        $percent = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        return [
            'available' => true,
            'total'     => $this->formatBytes($total),
            'used'      => $this->formatBytes($used),
            'free'      => $this->formatBytes($free),
            'percent'   => $percent,
            'status'    => $percent > 90 ? 'critical' : ($percent > 75 ? 'warning' : 'healthy'),
        ];
    }

    protected function getLaravelInfo(): array
    {
        $configCached = file_exists(base_path('bootstrap/cache/config.php'));
        $routesCached = file_exists(base_path('bootstrap/cache/routes-v7.php'));
        $viewsCompiled = count(glob(storage_path('framework/views/*.php')));

        return [
            'version'        => app()->version(),
            'environment'    => app()->environment(),
            'debug'          => config('app.debug'),
            'url'            => config('app.url'),
            'timezone'       => config('app.timezone'),
            'config_cached'  => $configCached,
            'routes_cached'  => $routesCached,
            'views_compiled' => $viewsCompiled,
            'session_driver' => config('session.driver'),
            'cache_driver'   => config('cache.default'),
            'queue_driver'   => config('queue.default'),
        ];
    }

    protected function getOpcacheInfo(): array
    {
        if (!function_exists('opcache_get_status')) {
            return ['enabled' => false, 'reason' => 'OPcache extension not loaded'];
        }

        $status = @opcache_get_status(false);
        if (!$status) {
            return ['enabled' => false, 'reason' => 'OPcache disabled or restricted'];
        }

        $mem = $status['memory_usage'] ?? [];
        $stats = $status['opcache_statistics'] ?? [];

        return [
            'enabled'       => true,
            'memory_used'   => $this->formatBytes($mem['used_memory'] ?? 0),
            'memory_free'   => $this->formatBytes($mem['free_memory'] ?? 0),
            'memory_wasted' => isset($mem['wasted_percentage']) ? round($mem['wasted_percentage'], 1) . '%' : '0%',
            'cached_scripts'=> $stats['num_cached_scripts'] ?? 0,
            'hit_rate'      => isset($stats['opcache_hit_rate']) ? round($stats['opcache_hit_rate'], 1) . '%' : 'N/A',
            'restarts'      => $stats['oom_restarts'] ?? 0,
            'can_reset'     => function_exists('opcache_reset'),
        ];
    }

    protected function getSessionInfo(): array
    {
        try {
            $activeSessions = AdminLog::where('status', 'active')->count();
            $todayLogins = AdminLog::whereDate('login_at', today())->count();
            $failedToday = AdminLog::whereDate('login_at', today())
                ->where('status', 'like', 'failed_%')->count();
            $totalAdmins = Admin::count();
            $activeAdmins = Admin::where('is_active', 1)->count();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return [
            'active_sessions' => $activeSessions,
            'today_logins'    => $todayLogins,
            'failed_today'    => $failedToday,
            'total_admins'    => $totalAdmins,
            'active_admins'   => $activeAdmins,
        ];
    }

    protected function getBackupInfo(): array
    {
        try {
            $lastBackup = BackupRun::where('status', 'completed')
                ->orderBy('completed_at', 'desc')->first();

            $totalBackups = BackupRun::where('status', 'completed')->count();
            $totalSize = BackupRun::where('status', 'completed')->sum('total_size');
            $logCount = DB::table('tbl_backup_logs')->count();

            return [
                'last_backup'    => $lastBackup?->completed_at?->format('d M Y H:i') ?? 'Never',
                'last_ago'       => $lastBackup?->completed_at?->diffForHumans() ?? 'N/A',
                'last_size'      => $lastBackup ? $this->formatBytes($lastBackup->total_size) : 'N/A',
                'total_backups'  => $totalBackups,
                'total_size'     => $this->formatBytes($totalSize),
                'log_entries'    => number_format($logCount),
                'status'         => !$lastBackup ? 'never' :
                    ($lastBackup->completed_at->diffInDays(now()) > 7 ? 'stale' : 'healthy'),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function getTableSizes(): array
    {
        try {
            $tables = DB::select('SHOW TABLE STATUS');
            $result = [];
            $totalSize = 0;

            foreach ($tables as $t) {
                $size = ($t->Data_length ?? 0) + ($t->Index_length ?? 0);
                $totalSize += $size;
                $result[] = [
                    'name'   => $t->Name,
                    'rows'   => (int) ($t->Rows ?? 0),
                    'size'   => $size,
                    'size_h' => $this->formatBytes($size),
                    'engine' => $t->Engine ?? '?',
                ];
            }

            // Sort by size descending
            usort($result, fn($a, $b) => $b['size'] <=> $a['size']);

            return [
                'tables'     => $result,
                'total_size' => $this->formatBytes($totalSize),
                'count'      => count($result),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function getStorageBreakdown(): array
    {
        $dirs = [
            'storage/framework/views'    => 'Compiled Views',
            'storage/framework/cache'    => 'Application Cache',
            'storage/framework/sessions' => 'Sessions',
            'storage/logs'               => 'Logs',
            'storage/app/patch_backups'  => 'Patch Backups',
            'storage/app/exports'        => 'Exports',
            'storage/debugbar'           => 'Debug Bar',
            'backup'                     => 'Backups',
        ];

        $result = [];
        foreach ($dirs as $dir => $label) {
            $fullPath = base_path($dir);
            if (!is_dir($fullPath)) {
                $result[] = ['label' => $label, 'path' => $dir, 'size' => 0, 'size_h' => '—', 'files' => 0];
                continue;
            }

            $size = 0;
            $count = 0;
            try {
                $iter = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($fullPath, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($iter as $file) {
                    $size += $file->getSize();
                    $count++;
                }
            } catch (\Exception $e) {}

            $result[] = [
                'label' => $label,
                'path'  => $dir,
                'size'  => $size,
                'size_h'=> $this->formatBytes($size),
                'files' => $count,
            ];
        }

        // Sort by size descending
        usort($result, fn($a, $b) => $b['size'] <=> $a['size']);

        return $result;
    }

    protected function getRecentErrors(): array
    {
        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            // Try error_log in base path (cPanel)
            $logFile = base_path('error_log');
        }

        if (!file_exists($logFile)) {
            return ['available' => false];
        }

        $size = filesize($logFile);
        $lines = [];

        try {
            // Read last 2KB for recent entries
            $handle = fopen($logFile, 'r');
            if ($handle) {
                $readSize = min($size, 2048);
                fseek($handle, -$readSize, SEEK_END);
                $content = fread($handle, $readSize);
                fclose($handle);

                $rawLines = explode("\n", $content);
                // Filter to error/warning lines
                foreach (array_reverse($rawLines) as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    if (preg_match('/\.(ERROR|WARNING|CRITICAL)/i', $line) || preg_match('/^\[.*\]\s*(PHP\s+)?(Fatal|Warning|Error)/i', $line)) {
                        $lines[] = mb_substr($line, 0, 200);
                        if (count($lines) >= 5) break;
                    }
                }
            }
        } catch (\Exception $e) {}

        return [
            'available' => true,
            'file'      => basename($logFile),
            'size'      => $this->formatBytes($size),
            'recent'    => $lines,
        ];
    }

    // ── Helpers ──────────────────────────────────────

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
