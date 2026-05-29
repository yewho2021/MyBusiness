# Admin Login Activity Log Module — Full Design Document

## 1. Overview

A complete session-based login activity tracking system for the admin portal. Every login attempt (success or fail) is recorded with full IP information, user-agent details, and session lifecycle (login → logout / expiry). The module includes a rich filterable interface for administrators to audit all login activity.

---

## 2. Database Table: `tbl_admin_log`

```sql
CREATE TABLE `tbl_admin_log` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `session_id`        VARCHAR(64)         NOT NULL COMMENT 'Unique session token (UUID)',
    `admin_id`          BIGINT UNSIGNED     NULL     DEFAULT NULL COMMENT 'FK → tbl_admin.id, NULL if user not found',
    `admin_name`        VARCHAR(100)        NULL     DEFAULT NULL COMMENT 'Snapshot of admin name at login time',
    `admin_username`    VARCHAR(50)         NULL     DEFAULT NULL COMMENT 'Username attempted (always stored)',
    `role_id`           BIGINT UNSIGNED     NULL     DEFAULT NULL COMMENT 'FK → tbl_admin_roles.id at login time',
    `role_name`         VARCHAR(50)         NULL     DEFAULT NULL COMMENT 'Snapshot of role name at login time',
    `status`            ENUM('success','failed_password','failed_not_found','failed_inactive','expired','active')
                                            NOT NULL DEFAULT 'active',
    `ip_address`        VARCHAR(45)         NOT NULL COMMENT 'IPv4 or IPv6',
    `ip_country`        VARCHAR(100)        NULL     DEFAULT NULL,
    `ip_city`           VARCHAR(100)        NULL     DEFAULT NULL,
    `ip_isp`            VARCHAR(255)        NULL     DEFAULT NULL,
    `user_agent`        TEXT                NULL     DEFAULT NULL COMMENT 'Raw user-agent string',
    `browser`           VARCHAR(100)        NULL     DEFAULT NULL COMMENT 'Parsed: Chrome 120, Firefox 121, etc.',
    `platform`          VARCHAR(100)        NULL     DEFAULT NULL COMMENT 'Parsed: Windows 11, macOS 14, Android 14, etc.',
    `device_type`       ENUM('desktop','mobile','tablet','unknown')
                                            NOT NULL DEFAULT 'unknown',
    `login_at`          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `logout_at`         TIMESTAMP           NULL     DEFAULT NULL,
    `duration_seconds`  INT UNSIGNED        NULL     DEFAULT NULL COMMENT 'Calculated on logout/expiry',
    `logout_type`       ENUM('manual','expired','kicked','system')
                                            NULL     DEFAULT NULL,
    `fail_reason`       VARCHAR(255)        NULL     DEFAULT NULL COMMENT 'Human-readable failure detail',
    `created_at`        TIMESTAMP           NULL     DEFAULT NULL,
    `updated_at`        TIMESTAMP           NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_session_id` (`session_id`),
    KEY `idx_admin_id` (`admin_id`),
    KEY `idx_status` (`status`),
    KEY `idx_login_at` (`login_at`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Why snapshot `admin_name` / `role_name`?
If an admin is renamed or role changed later, historical log entries still show the **exact** name and role at the time of that login. The FK columns (`admin_id`, `role_id`) remain for JOIN queries on current data when needed.

### Status Lifecycle

```
Login attempt
  ├── User not found          → status = 'failed_not_found'
  ├── Account inactive        → status = 'failed_inactive'  
  ├── Wrong password          → status = 'failed_password'
  └── Success                 → status = 'active'
                                  │
                                  ├── User clicks Logout     → status = 'success', logout_type = 'manual'
                                  ├── Cookie expires (7 days) → status = 'expired',  logout_type = 'expired'
                                  └── Admin kicked by admin   → status = 'success', logout_type = 'kicked'
```

---

## 3. Model: `AdminLog`

**File:** `app/Models/AdminLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AdminLog extends Model
{
    protected $table = 'tbl_admin_log';

    protected $fillable = [
        'session_id', 'admin_id', 'admin_name', 'admin_username',
        'role_id', 'role_name', 'status',
        'ip_address', 'ip_country', 'ip_city', 'ip_isp',
        'user_agent', 'browser', 'platform', 'device_type',
        'login_at', 'logout_at', 'duration_seconds', 'logout_type',
        'fail_reason',
    ];

    protected $casts = [
        'login_at'  => 'datetime',
        'logout_at' => 'datetime',
    ];

    // ── Relationships ──

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    // ── Scopes ──

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['success', 'active', 'expired']);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'like', 'failed_%');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeForRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        if ($from) $query->where('login_at', '>=', $from);
        if ($to)   $query->where('login_at', '<=', $to . ' 23:59:59');
        return $query;
    }

    public function scopeForIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    // ── Helpers ──

    public static function generateSessionId(): string
    {
        return (string) Str::uuid();
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_seconds) return null;

        $h = intdiv($this->duration_seconds, 3600);
        $m = intdiv($this->duration_seconds % 3600, 60);
        $s = $this->duration_seconds % 60;

        if ($h > 0) return sprintf('%dh %dm %ds', $h, $m, $s);
        if ($m > 0) return sprintf('%dm %ds', $m, $s);
        return sprintf('%ds', $s);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFailed(): bool
    {
        return str_starts_with($this->status, 'failed_');
    }

    /**
     * Parse user-agent into browser, platform, device_type.
     */
    public static function parseUserAgent(?string $ua): array
    {
        if (!$ua) return ['browser' => null, 'platform' => null, 'device_type' => 'unknown'];

        $browser  = 'Unknown';
        $platform = 'Unknown';
        $device   = 'desktop';

        // ── Platform ──
        if (preg_match('/Windows NT 10/i', $ua))          $platform = 'Windows 10/11';
        elseif (preg_match('/Windows NT 6\.3/i', $ua))     $platform = 'Windows 8.1';
        elseif (preg_match('/Windows NT 6\.1/i', $ua))     $platform = 'Windows 7';
        elseif (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m)) $platform = 'macOS ' . str_replace('_', '.', $m[1]);
        elseif (preg_match('/Android ([\d.]+)/i', $ua, $m))  $platform = 'Android ' . $m[1];
        elseif (preg_match('/iPhone OS ([\d_]+)/i', $ua, $m)) $platform = 'iOS ' . str_replace('_', '.', $m[1]);
        elseif (preg_match('/iPad/i', $ua))                   $platform = 'iPadOS';
        elseif (preg_match('/Linux/i', $ua))                  $platform = 'Linux';
        elseif (preg_match('/CrOS/i', $ua))                   $platform = 'Chrome OS';

        // ── Browser ──
        if (preg_match('/Edg\/([\d.]+)/i', $ua, $m))         $browser = 'Edge ' . intval($m[1]);
        elseif (preg_match('/OPR\/([\d.]+)/i', $ua, $m))     $browser = 'Opera ' . intval($m[1]);
        elseif (preg_match('/Chrome\/([\d.]+)/i', $ua, $m))  $browser = 'Chrome ' . intval($m[1]);
        elseif (preg_match('/Firefox\/([\d.]+)/i', $ua, $m)) $browser = 'Firefox ' . intval($m[1]);
        elseif (preg_match('/Safari\/([\d.]+)/i', $ua) && preg_match('/Version\/([\d.]+)/i', $ua, $m))
            $browser = 'Safari ' . intval($m[1]);

        // ── Device type ──
        if (preg_match('/Mobile|Android.*Mobile|iPhone/i', $ua))  $device = 'mobile';
        elseif (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $ua)) $device = 'tablet';

        return ['browser' => $browser, 'platform' => $platform, 'device_type' => $device];
    }

    /**
     * Resolve IP geo info via ip-api.com (free, no key needed, 45 req/min).
     * Returns array with country, city, isp. Safe to fail silently.
     */
    public static function resolveIpGeo(string $ip): array
    {
        $default = ['ip_country' => null, 'ip_city' => null, 'ip_isp' => null];

        // Skip private/local IPs
        if (in_array($ip, ['127.0.0.1', '::1']) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return array_merge($default, ['ip_country' => 'Local', 'ip_city' => 'Local']);
        }

        try {
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,isp", false, $ctx);
            if ($json) {
                $data = json_decode($json, true);
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'ip_country' => $data['country'] ?? null,
                        'ip_city'    => $data['city'] ?? null,
                        'ip_isp'     => $data['isp'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silent fail — geo info is optional
        }

        return $default;
    }

    /**
     * One-shot: create a full log entry from a request + admin context.
     */
    public static function record(
        Request $request,
        string  $status,
        ?Admin  $admin = null,
        ?string $attemptedUsername = null,
        ?string $failReason = null,
        ?string $sessionId = null
    ): self {
        $ua    = $request->userAgent();
        $ip    = $request->ip();
        $parsed = self::parseUserAgent($ua);
        $geo    = self::resolveIpGeo($ip);

        return self::create([
            'session_id'     => $sessionId ?? self::generateSessionId(),
            'admin_id'       => $admin?->id,
            'admin_name'     => $admin?->name,
            'admin_username' => $attemptedUsername ?? $admin?->username,
            'role_id'        => $admin?->role_id,
            'role_name'      => $admin?->role?->name,
            'status'         => $status,
            'ip_address'     => $ip,
            'ip_country'     => $geo['ip_country'],
            'ip_city'        => $geo['ip_city'],
            'ip_isp'         => $geo['ip_isp'],
            'user_agent'     => $ua,
            'browser'        => $parsed['browser'],
            'platform'       => $parsed['platform'],
            'device_type'    => $parsed['device_type'],
            'login_at'       => now(),
            'fail_reason'    => $failReason,
        ]);
    }

    /**
     * Close an active session (logout / expiry).
     */
    public function closeSession(string $logoutType = 'manual'): void
    {
        $this->update([
            'status'           => $logoutType === 'expired' ? 'expired' : 'success',
            'logout_at'        => now(),
            'logout_type'      => $logoutType,
            'duration_seconds' => $this->login_at ? now()->diffInSeconds($this->login_at) : null,
        ]);
    }
}
```

> **Note:** The `record()` method uses `Illuminate\Http\Request` — add `use Illuminate\Http\Request;` at the top. The model is self-contained: it parses UA, resolves geo, and creates the record in one call.

---

## 4. Modified LoginController

**File:** `app/Http/Controllers/Admin/Auth/LoginController.php`

Key changes marked with `// ★ NEW`:

```php
<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $adminId = $request->cookie('admin_id');
        if ($adminId) {
            $admin = Admin::find($adminId);
            if ($admin && $admin->is_active == 1) {
                return redirect()->route('admin.dashboard');
            }
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        $admin = Admin::with('role')                        // ★ eager load role
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        // ── Failed: user not found ──
        if (!$admin) {
            AdminLog::record($request, 'failed_not_found',  // ★ NEW
                null, $username, 'User not found');
            return back()->withErrors([
                'username' => 'User not found.',
            ])->withInput($request->only('username'));
        }

        // ── Failed: account inactive ──
        if ($admin->is_active != 1) {
            AdminLog::record($request, 'failed_inactive',   // ★ NEW
                $admin, $username, 'Account deactivated');
            return back()->withErrors([
                'username' => 'Account deactivated.',
            ])->withInput($request->only('username'));
        }

        // ── Failed: wrong password ──
        if (!Hash::check($password, $admin->password)) {
            AdminLog::record($request, 'failed_password',   // ★ NEW
                $admin, $username, 'Incorrect password');
            return back()->withErrors([
                'username' => 'Wrong password.',
            ])->withInput($request->only('username'));
        }

        // ── Success ──
        $admin->datetime_lastlogin = now();
        $admin->save();

        $sessionId = AdminLog::generateSessionId();         // ★ NEW
        AdminLog::record($request, 'active',                // ★ NEW
            $admin, $username, null, $sessionId);

        $cookie  = Cookie::make('admin_id', $admin->id, 10080, '/', null, false, true);
        $sCookie = Cookie::make('admin_session_id',         // ★ NEW
            $sessionId, 10080, '/', null, false, true);

        return redirect()->route('admin.dashboard')
            ->withCookie($cookie)
            ->withCookie($sCookie);                         // ★ NEW
    }

    public function logout(Request $request)
    {
        // ★ NEW — close the active session
        $sessionId = $request->cookie('admin_session_id');
        if ($sessionId) {
            $log = AdminLog::where('session_id', $sessionId)
                ->where('status', 'active')
                ->first();
            if ($log) {
                $log->closeSession('manual');
            }
        }

        $cookie  = Cookie::forget('admin_id');
        $sCookie = Cookie::forget('admin_session_id');      // ★ NEW

        return redirect()->route('admin.login')
            ->withCookie($cookie)
            ->withCookie($sCookie);                         // ★ NEW
    }
}
```

---

## 5. Modified AdminAuthenticate Middleware

When a cookie is missing or invalid but there's still an active session in the DB, mark it as expired.

**File:** `app/Http/Middleware/AdminAuthenticate.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\AdminLog;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId   = $request->cookie('admin_id');
        $sessionId = $request->cookie('admin_session_id');

        if (!$adminId) {
            // ★ If session cookie still present but admin cookie gone → mark expired
            $this->expireOrphanSession($sessionId);
            return redirect()->route('admin.login');
        }

        $admin = Admin::find($adminId);

        if (!$admin || $admin->is_active != 1) {
            $this->expireOrphanSession($sessionId);
            return redirect()->route('admin.login')
                ->withCookie(\Cookie::forget('admin_id'))
                ->withCookie(\Cookie::forget('admin_session_id'));
        }

        return $next($request);
    }

    private function expireOrphanSession(?string $sessionId): void
    {
        if (!$sessionId) return;

        $log = AdminLog::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if ($log) {
            $log->closeSession('expired');
        }
    }
}
```

---

## 6. Controller: `AdminLogController`

**File:** `app/Http/Controllers/Admin/AdminLogController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Models\AdminRole;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminLog::query()->orderBy('login_at', 'desc');

        // ── Filters ──

        // Status filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'failed') {
                $query->where('status', 'like', 'failed_%');
            } else {
                $query->where('status', $status);
            }
        }

        // Admin user filter
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // Role filter
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // IP filter
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('login_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('login_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Device type filter
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Search (username, name, IP)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('admin_username', 'like', "%{$s}%")
                  ->orWhere('admin_name', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhere('browser', 'like', "%{$s}%")
                  ->orWhere('ip_country', 'like', "%{$s}%")
                  ->orWhere('ip_city', 'like', "%{$s}%");
            });
        }

        $logs = $query->paginate(25)->appends($request->query());

        // For filter dropdowns
        $admins = Admin::select('id', 'name', 'username')->orderBy('name')->get();
        $roles  = AdminRole::select('id', 'name')->orderBy('name')->get();

        // ── Summary stats (based on current filters) ──
        $statsQuery = AdminLog::query();

        // Apply same filters for stats
        if ($request->filled('date_from')) $statsQuery->where('login_at', '>=', $request->date_from . ' 00:00:00');
        if ($request->filled('date_to'))   $statsQuery->where('login_at', '<=', $request->date_to . ' 23:59:59');
        if ($request->filled('admin_id'))  $statsQuery->where('admin_id', $request->admin_id);
        if ($request->filled('role_id'))   $statsQuery->where('role_id', $request->role_id);

        $stats = [
            'total'          => (clone $statsQuery)->count(),
            'success'        => (clone $statsQuery)->whereIn('status', ['success', 'active', 'expired'])->count(),
            'failed'         => (clone $statsQuery)->where('status', 'like', 'failed_%')->count(),
            'active_now'     => AdminLog::where('status', 'active')->count(),
            'unique_ips'     => (clone $statsQuery)->distinct('ip_address')->count('ip_address'),
        ];

        return view('admin.pages.admin-log.index', compact('logs', 'admins', 'roles', 'stats'));
    }

    /**
     * AJAX endpoint: show session detail in modal.
     */
    public function show(Request $request, $id)
    {
        $log = AdminLog::findOrFail($id);

        if ($request->ajax()) {
            return response()->json($log);
        }

        return redirect()->route('admin.admin-log.index');
    }

    /**
     * Force-close an active session (kick user).
     */
    public function kick(Request $request, $id)
    {
        $log = AdminLog::where('id', $id)->where('status', 'active')->firstOrFail();
        $log->closeSession('kicked');

        return redirect()->route('admin.admin-log.index')
            ->with('success', "Session for {$log->admin_name} has been terminated.");
    }

    /**
     * Export filtered logs as CSV.
     */
    public function export(Request $request)
    {
        $query = AdminLog::query()->orderBy('login_at', 'desc');

        // Apply same filters as index
        if ($request->filled('status')) {
            $status = $request->status;
            $status === 'failed'
                ? $query->where('status', 'like', 'failed_%')
                : $query->where('status', $status);
        }
        if ($request->filled('admin_id'))   $query->where('admin_id', $request->admin_id);
        if ($request->filled('role_id'))    $query->where('role_id', $request->role_id);
        if ($request->filled('ip_address')) $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        if ($request->filled('date_from'))  $query->where('login_at', '>=', $request->date_from . ' 00:00:00');
        if ($request->filled('date_to'))    $query->where('login_at', '<=', $request->date_to . ' 23:59:59');
        if ($request->filled('device_type')) $query->where('device_type', $request->device_type);

        $logs = $query->limit(5000)->get();

        $filename = 'admin_login_log_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Session ID', 'Admin', 'Username', 'Role', 'Status',
                'IP', 'Country', 'City', 'ISP',
                'Browser', 'Platform', 'Device',
                'Login At', 'Logout At', 'Duration', 'Logout Type', 'Fail Reason',
            ]);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id, $log->session_id,
                    $log->admin_name, $log->admin_username, $log->role_name,
                    $log->status, $log->ip_address, $log->ip_country,
                    $log->ip_city, $log->ip_isp, $log->browser,
                    $log->platform, $log->device_type,
                    $log->login_at?->format('Y-m-d H:i:s'),
                    $log->logout_at?->format('Y-m-d H:i:s'),
                    $log->formatted_duration, $log->logout_type,
                    $log->fail_reason,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Purge old logs (admin action).
     */
    public function purge(Request $request)
    {
        $request->validate(['days' => 'required|integer|min:30']);

        $cutoff = now()->subDays($request->days);
        $deleted = AdminLog::where('login_at', '<', $cutoff)
            ->where('status', '!=', 'active')
            ->delete();

        return redirect()->route('admin.admin-log.index')
            ->with('success', "Purged {$deleted} log entries older than {$request->days} days.");
    }
}
```

---

## 7. Routes

**Add to:** `routes/admin.php` — inside the authenticated middleware group:

```php
use App\Http\Controllers\Admin\AdminLogController;

// Admin Login Activity Log
Route::get('admin-log', [AdminLogController::class, 'index'])->name('admin.admin-log.index');
Route::get('admin-log/{id}', [AdminLogController::class, 'show'])->name('admin.admin-log.show');
Route::post('admin-log/{id}/kick', [AdminLogController::class, 'kick'])->name('admin.admin-log.kick');
Route::get('admin-log-export', [AdminLogController::class, 'export'])->name('admin.admin-log.export');
Route::post('admin-log-purge', [AdminLogController::class, 'purge'])->name('admin.admin-log.purge');
```

---

## 8. SQL Patch File

**File:** `database/patches/2026_03_22_admin_log.sql`

```sql
-- =============================================
-- Admin Login Activity Log
-- Created: 2026-03-22
-- =============================================

CREATE TABLE IF NOT EXISTS `tbl_admin_log` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `session_id`        VARCHAR(64)         NOT NULL,
    `admin_id`          BIGINT UNSIGNED     NULL     DEFAULT NULL,
    `admin_name`        VARCHAR(100)        NULL     DEFAULT NULL,
    `admin_username`    VARCHAR(50)         NULL     DEFAULT NULL,
    `role_id`           BIGINT UNSIGNED     NULL     DEFAULT NULL,
    `role_name`         VARCHAR(50)         NULL     DEFAULT NULL,
    `status`            ENUM('success','failed_password','failed_not_found','failed_inactive','expired','active')
                                            NOT NULL DEFAULT 'active',
    `ip_address`        VARCHAR(45)         NOT NULL,
    `ip_country`        VARCHAR(100)        NULL     DEFAULT NULL,
    `ip_city`           VARCHAR(100)        NULL     DEFAULT NULL,
    `ip_isp`            VARCHAR(255)        NULL     DEFAULT NULL,
    `user_agent`        TEXT                NULL     DEFAULT NULL,
    `browser`           VARCHAR(100)        NULL     DEFAULT NULL,
    `platform`          VARCHAR(100)        NULL     DEFAULT NULL,
    `device_type`       ENUM('desktop','mobile','tablet','unknown')
                                            NOT NULL DEFAULT 'unknown',
    `login_at`          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `logout_at`         TIMESTAMP           NULL     DEFAULT NULL,
    `duration_seconds`  INT UNSIGNED        NULL     DEFAULT NULL,
    `logout_type`       ENUM('manual','expired','kicked','system')
                                            NULL     DEFAULT NULL,
    `fail_reason`       VARCHAR(255)        NULL     DEFAULT NULL,
    `created_at`        TIMESTAMP           NULL     DEFAULT NULL,
    `updated_at`        TIMESTAMP           NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_session_id` (`session_id`),
    KEY `idx_admin_id` (`admin_id`),
    KEY `idx_status` (`status`),
    KEY `idx_login_at` (`login_at`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add menu entry (adjust group_id / sort_order to fit your menu structure)
-- Assuming group_id=1 is your main navigation group
INSERT INTO `tbl_admin_menus` (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES (1, NULL, 1, 'Login Log', 'fas fa-user-shield', 'admin.admin-log.index', 'admin_log', 50, 1, NOW(), NOW());
```

---

## 9. Blade View: `admin-log/index.blade.php`

**File:** `resources/views/admin/pages/admin-log/index.blade.php`

The view includes:

1. **Stats bar** — 5 summary cards (Total, Successful, Failed, Active Now, Unique IPs)
2. **Filter panel** — collapsible, with: date range, admin user, role, status, device type, IP search, text search
3. **Data table** — all columns with status badges, click-to-expand detail row
4. **Detail modal** — AJAX-loaded full session info (IP geo, UA, timestamps, duration)
5. **Actions** — Kick active session, Export CSV, Purge old logs
6. **Pagination** — preserving all filter parameters

### UI Patterns (matching existing codebase)

| Pattern | Source | Used |
|---------|--------|------|
| Page header with action buttons | `users/index.blade.php` | ✓ |
| Card + data-table layout | `users/index.blade.php` | ✓ |
| Status badges (colored pills) | `users/index.blade.php` | ✓ |
| Role badges with role-specific colors | `users/index.blade.php` | ✓ |
| Expandable detail rows | `changelog/index.blade.php` | ✓ |
| Filter pills/buttons | `changelog/index.blade.php` | ✓ |
| Modal overlay pattern | `users/index.blade.php` | ✓ |
| Inline form actions (POST with CSRF) | `users/index.blade.php` | ✓ |
| Color scheme: `#dc2626` primary, `#111` sidebar, Inter font | `layouts/app.blade.php` | ✓ |

---

## 10. Menu / Permission Integration

This module integrates with the existing dynamic menu system:

1. The SQL patch inserts a menu entry into `tbl_admin_menus` with `route_name = 'admin.admin-log.index'`
2. Administrators get automatic access (the `CheckAdminMenuAccess` middleware grants full access to admins with `administrator` role slug)
3. For other roles, grant access via the Permissions page (`admin.permissions.index`) by toggling `can_view` on the "Login Log" menu item

---

## 11. Changelog Entry

```sql
INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '1.8.0',
    'Admin Login Activity Log',
    'Complete login session tracking module:\n- Records every login attempt (success + all failure types)\n- Full IP geolocation (country, city, ISP) via ip-api.com\n- User-agent parsing (browser, platform, device type)\n- Session lifecycle tracking (login → logout/expiry)\n- Session duration calculation\n- Rich filterable interface (date range, user, role, status, IP, device)\n- Active session monitoring with force-kick capability\n- CSV export with all filter combinations\n- Log purge for data retention management\n- Snapshot of admin name + role at login time for historical accuracy',
    '{"table": "tbl_admin_log", "new_files": ["app/Models/AdminLog.php", "app/Http/Controllers/Admin/AdminLogController.php", "resources/views/admin/pages/admin-log/index.blade.php"], "modified_files": ["app/Http/Controllers/Admin/Auth/LoginController.php", "app/Http/Middleware/AdminAuthenticate.php", "routes/admin.php"], "cookies_added": ["admin_session_id"], "api_used": "ip-api.com (free, 45 req/min)"}',
    NOW()
);
```

---

## 12. File Summary

| File | Action | Description |
|------|--------|-------------|
| `database/patches/2026_03_22_admin_log.sql` | **NEW** | SQL to create table + menu entry |
| `app/Models/AdminLog.php` | **NEW** | Model with UA parser, geo resolver, scopes, helpers |
| `app/Http/Controllers/Admin/AdminLogController.php` | **NEW** | Index (filtered), show, kick, export, purge |
| `resources/views/admin/pages/admin-log/index.blade.php` | **NEW** | Full UI with stats, filters, table, modals |
| `app/Http/Controllers/Admin/Auth/LoginController.php` | **MODIFY** | Record login/logout, set session cookie |
| `app/Http/Middleware/AdminAuthenticate.php` | **MODIFY** | Expire orphan sessions on auth failure |
| `routes/admin.php` | **MODIFY** | Add 5 new routes |

---

## 13. Deployment Steps

1. Run `2026_03_22_admin_log.sql` on the production database
2. Upload all new files
3. Replace the 3 modified files
4. Verify the menu item appears in the sidebar
5. Assign permissions to non-admin roles via the Permissions page if needed
6. Test: login → check log appears → logout → check duration calculated → try wrong password → check failed entry
