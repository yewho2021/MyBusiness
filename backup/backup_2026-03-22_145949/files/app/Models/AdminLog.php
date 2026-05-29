<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
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

    // ── Relationships ──────────────────────────────────────────

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    // ── Scopes ─────────────────────────────────────────────────

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

    // ── Helpers ─────────────────────────────────────────────────

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
     * Parse user-agent string into browser, platform, device_type.
     */
    public static function parseUserAgent(?string $ua): array
    {
        if (!$ua) return ['browser' => null, 'platform' => null, 'device_type' => 'unknown'];

        $browser  = 'Unknown';
        $platform = 'Unknown';
        $device   = 'desktop';

        // ── Platform detection ──
        if (preg_match('/Windows NT 10/i', $ua))                    $platform = 'Windows 10/11';
        elseif (preg_match('/Windows NT 6\.3/i', $ua))              $platform = 'Windows 8.1';
        elseif (preg_match('/Windows NT 6\.2/i', $ua))              $platform = 'Windows 8';
        elseif (preg_match('/Windows NT 6\.1/i', $ua))              $platform = 'Windows 7';
        elseif (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m))       $platform = 'macOS ' . str_replace('_', '.', $m[1]);
        elseif (preg_match('/Android ([\d.]+)/i', $ua, $m))        $platform = 'Android ' . $m[1];
        elseif (preg_match('/iPhone OS ([\d_]+)/i', $ua, $m))      $platform = 'iOS ' . str_replace('_', '.', $m[1]);
        elseif (preg_match('/iPad/i', $ua))                         $platform = 'iPadOS';
        elseif (preg_match('/CrOS/i', $ua))                         $platform = 'Chrome OS';
        elseif (preg_match('/Linux/i', $ua))                        $platform = 'Linux';

        // ── Browser detection (order matters: check branded first) ──
        if (preg_match('/Edg\/([\d.]+)/i', $ua, $m))               $browser = 'Edge ' . intval($m[1]);
        elseif (preg_match('/OPR\/([\d.]+)/i', $ua, $m))           $browser = 'Opera ' . intval($m[1]);
        elseif (preg_match('/SamsungBrowser\/([\d.]+)/i', $ua, $m)) $browser = 'Samsung ' . intval($m[1]);
        elseif (preg_match('/UCBrowser\/([\d.]+)/i', $ua, $m))     $browser = 'UC Browser ' . intval($m[1]);
        elseif (preg_match('/Chrome\/([\d.]+)/i', $ua, $m))        $browser = 'Chrome ' . intval($m[1]);
        elseif (preg_match('/Firefox\/([\d.]+)/i', $ua, $m))       $browser = 'Firefox ' . intval($m[1]);
        elseif (preg_match('/Safari\/([\d.]+)/i', $ua) && preg_match('/Version\/([\d.]+)/i', $ua, $m))
            $browser = 'Safari ' . intval($m[1]);

        // ── Device type detection ──
        if (preg_match('/Mobile|Android.*Mobile|iPhone/i', $ua))           $device = 'mobile';
        elseif (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $ua))     $device = 'tablet';

        return ['browser' => $browser, 'platform' => $platform, 'device_type' => $device];
    }

    /**
     * Resolve IP geolocation via ip-api.com (free, no key, 45 req/min).
     * Fails silently — geo info is optional.
     */
    public static function resolveIpGeo(string $ip): array
    {
        $default = ['ip_country' => null, 'ip_city' => null, 'ip_isp' => null];

        // Skip private/local IPs
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return array_merge($default, ['ip_country' => 'Local', 'ip_city' => 'Local', 'ip_isp' => 'Loopback']);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return array_merge($default, ['ip_country' => 'Private', 'ip_city' => 'Private', 'ip_isp' => 'Private Network']);
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
            // Silent fail
        }

        return $default;
    }

    /**
     * Record a login attempt (success or failure) in one call.
     */
    public static function record(
        Request $request,
        string  $status,
        ?Admin  $admin = null,
        ?string $attemptedUsername = null,
        ?string $failReason = null,
        ?string $sessionId = null
    ): self {
        $ua     = $request->userAgent();
        $ip     = $request->ip();
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
     * Close an active session (manual logout, expiry, or kick).
     */
    public function closeSession(string $logoutType = 'manual'): void
    {
        $this->update([
            'status'           => $logoutType === 'expired' ? 'expired' : 'success',
            'logout_at'        => now(),
            'logout_type'      => $logoutType,
            'duration_seconds' => $this->login_at ? (int) abs(now()->diffInSeconds($this->login_at)) : null,
        ]);
    }
}
