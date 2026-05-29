<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\AdminMenu;
use App\Models\AdminMenuGroup;
use App\Models\AdminLog;
use App\Models\Changelog;
use App\Models\BackupRun;
use App\Models\DatabaseConnection;
use App\Models\Version;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->attributes->get('admin');
        $tz = $admin->timezone ?? Configuration::get('default_timezone', config('app.timezone', 'UTC'));

        // ── Cached core stats (60s TTL) ──────────────
        $coreStats = Cache::remember('dashboard_core_stats', 60, function () {
            $savedConnections = 0;
            try { $savedConnections = DatabaseConnection::count(); } catch (\Exception $e) {}

            return [
                'totalAdmins'      => Admin::count(),
                'totalRoles'       => AdminRole::count(),
                'totalMenus'       => AdminMenu::count(),
                'totalChangelogs'  => Changelog::count(),
                'savedConnections' => $savedConnections,
            ];
        });

        // ── Cached database stats (60s TTL) ──────────
        $dbStats = Cache::remember('dashboard_db_stats', 60, function () {
            return $this->getDatabaseStats();
        });

        // ── Cached disk stats (5 min TTL) ────────────
        $diskStats = Cache::remember('dashboard_disk_stats', 300, function () {
            return $this->getDiskStats();
        });

        // ── Security overview (cached 60s) ───────────
        $securityStats = Cache::remember('dashboard_security', 60, function () {
            $activeSessions = AdminLog::where('status', 'active')->count();
            $failedLast24h = AdminLog::where('status', 'like', 'failed_%')
                ->where('login_at', '>=', now()->subDay())
                ->count();
            $lockedAccounts = Admin::whereNotNull('locked_at')->count();
            $twofaEnabled = Admin::where('twofa_enabled', 1)->count();
            $totalAdmins = Admin::count();
            $twofaPct = $totalAdmins > 0 ? round($twofaEnabled / $totalAdmins * 100) : 0;

            return compact('activeSessions', 'failedLast24h', 'lockedAccounts', 'twofaEnabled', 'twofaPct', 'totalAdmins');
        });

        // ── Patch/Version stats (cached 60s) ─────────
        $patchStats = Cache::remember('dashboard_patch_stats', 60, function () {
            $latestVersion = Version::latest();
            $totalPatches = Version::where('type', 'patch')->count();
            $totalRollbacks = Version::where('type', 'rollback')->count();
            $successRate = 0;
            $totalVersions = Version::count();
            if ($totalVersions > 0) {
                $successRate = round(Version::where('status', 'success')->count() / $totalVersions * 100);
            }

            return [
                'latestVersion'  => $latestVersion,
                'totalPatches'   => $totalPatches,
                'totalRollbacks' => $totalRollbacks,
                'totalVersions'  => $totalVersions,
                'successRate'    => $successRate,
            ];
        });

        // ── 7-day login activity trend (cached 5 min) ──
        $loginTrend = Cache::remember('dashboard_login_trend', 300, function () {
            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $label = now()->subDays($i)->format('D');
                $logins = AdminLog::whereDate('login_at', $date)
                    ->whereIn('status', ['active', 'success', 'expired'])
                    ->count();
                $failed = AdminLog::whereDate('login_at', $date)
                    ->where('status', 'like', 'failed_%')
                    ->count();
                $days[] = ['date' => $date, 'label' => $label, 'logins' => $logins, 'failed' => $failed];
            }
            $maxVal = max(1, max(array_column($days, 'logins')));
            return ['days' => $days, 'max' => $maxVal];
        });

        // ── Recent data (NOT cached — always fresh) ──
        $recentBackups = BackupRun::orderBy('created_at', 'desc')->limit(5)->get();
        $lastBackup = $recentBackups->first();
        $recentChangelogs = Changelog::orderBy('created_at', 'desc')->limit(5)->get();

        $serverInfo = [
            'php'      => phpversion(),
            'laravel'  => app()->version(),
            'server'   => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'timezone' => $tz,
        ];

        // ── Recent logins (last 10) ──
        $recentLogins = AdminLog::orderBy('login_at', 'desc')
            ->limit(10)
            ->get(['admin_name', 'admin_username', 'role_name', 'status', 'ip_address', 'ip_country', 'ip_city', 'ip_isp', 'browser', 'platform', 'device_type', 'login_at', 'logout_at', 'duration_seconds', 'logout_type', 'fail_reason']);

        // ── Recent activity (last 10) ──
        $recentActivities = [];
        try {
            $recentActivities = \Spatie\Activitylog\Models\Activity::orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['description', 'subject_type', 'event', 'causer_id', 'created_at']);
        } catch (\Exception $e) {}

        // ── Control Panel (cached per role, 5 min TTL) ──
        $roleId = $admin ? $admin->role_id : 0;
        $controlPanel = Cache::remember('dashboard_cp_' . $roleId, 300, function () use ($admin) {
            return $this->getControlPanelData($admin);
        });

        // ── Greeting based on time of day ──
        $hour = (int) now()->setTimezone($tz)->format('H');
        if ($hour < 12) $greeting = 'Good morning';
        elseif ($hour < 17) $greeting = 'Good afternoon';
        else $greeting = 'Good evening';

        return view('admin.pages.dashboard', array_merge(
            $coreStats,
            compact('admin', 'greeting', 'recentBackups', 'lastBackup', 'recentChangelogs',
                    'diskStats', 'recentLogins', 'recentActivities', 'controlPanel',
                    'securityStats', 'patchStats', 'loginTrend'),
            [
                'dbName'          => $dbStats['name'],
                'totalTables'     => $dbStats['tables'],
                'totalRows'       => $dbStats['rows'],
                'totalDbSize'     => $dbStats['size'],
                'phpVersion'      => $serverInfo['php'],
                'laravelVersion'  => $serverInfo['laravel'],
                'serverSoftware'  => $serverInfo['server'],
                'serverTimezone'  => $serverInfo['timezone'],
            ]
        ));
    }

    protected function getDatabaseStats(): array
    {
        $dbName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLE STATUS');
        $totalTables = count($tables);
        $totalRows = 0;
        $totalDbSize = 0;
        foreach ($tables as $t) {
            $totalRows += $t->Rows ?? 0;
            $totalDbSize += ($t->Data_length ?? 0) + ($t->Index_length ?? 0);
        }
        return ['name' => $dbName, 'tables' => $totalTables, 'rows' => $totalRows, 'size' => $totalDbSize];
    }

    protected function getDiskStats(): array
    {
        $path = base_path();
        $total = @disk_total_space($path);
        $free = @disk_free_space($path);
        if ($total === false || $free === false) {
            return ['total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0, 'storage_size' => 0];
        }
        $used = $total - $free;
        $percent = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        $storageSize = 0;
        $storagePath = storage_path();
        if (is_dir($storagePath)) {
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storagePath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iter as $file) { $storageSize += $file->getSize(); }
        }

        return [
            'total'        => $total,
            'free'         => $free,
            'used'         => $used,
            'percent'      => $percent,
            'storage_size' => $storageSize,
        ];
    }

    protected function getControlPanelData($admin): array
    {
        $groups = AdminMenuGroup::where('is_active', 1)->orderBy('sort_order')->get();
        $menus = AdminMenu::where('is_active', 1)
            ->whereNotNull('route_name')
            ->where('route_name', '!=', 'admin.dashboard')
            ->orderBy('group_id')->orderBy('sort_order')->get();

        $palette = [
            ['bg' => '#eff6ff', 'fg' => '#2563eb', 'border' => '#bfdbfe'],
            ['bg' => '#f5f3ff', 'fg' => '#7c3aed', 'border' => '#ddd6fe'],
            ['bg' => '#f0fdf4', 'fg' => '#16a34a', 'border' => '#bbf7d0'],
            ['bg' => '#fffbeb', 'fg' => '#d97706', 'border' => '#fde68a'],
            ['bg' => '#fef2f2', 'fg' => '#dc2626', 'border' => '#fecaca'],
            ['bg' => '#f0f9ff', 'fg' => '#0ea5e9', 'border' => '#bae6fd'],
        ];

        $panel = [];
        $colorIndex = 0;
        foreach ($groups as $group) {
            $groupMenus = $menus->where('group_id', $group->id)->values();
            if ($groupMenus->isEmpty()) continue;

            $items = [];
            foreach ($groupMenus as $menu) {
                if ($admin && !$admin->isAdministrator()) {
                    if (!$admin->hasMenuPermission($menu->id, 'can_view')) continue;
                }
                try { $url = route($menu->route_name); } catch (\Exception $e) { continue; }
                $items[] = ['title' => $menu->title, 'icon' => $menu->icon ?? 'fas fa-cube', 'url' => $url];
            }
            if (empty($items)) continue;

            $panel[] = ['group_title' => $group->title, 'color' => $palette[$colorIndex % count($palette)], 'items' => $items];
            $colorIndex++;
        }
        return $panel;
    }
}
