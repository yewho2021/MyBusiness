<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity, SoftDeletes;

    protected $table = 'tbl_admin';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'twofa_secret',
        'twofa_enabled',
        'role_id',
        'is_active',
        'timezone',
        'datetime_lastlogin',
        'password_changed_at',
        'failed_login_count',
        'locked_at',
        'lock_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'twofa_secret',
    ];

    protected $casts = [
        'datetime_creation' => 'datetime',
        'datetime_lastlogin' => 'datetime',
        'password_changed_at' => 'datetime',
        'locked_at' => 'datetime',
        'twofa_enabled' => 'boolean',
    ];

    // ── Activity Log Options ─────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'username', 'role_id', 'is_active', 'twofa_enabled'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('admin')
            ->setDescriptionForEvent(fn(string $eventName) => "Admin user was {$eventName}");
    }

    // ── 2FA Secret (encrypted at rest) ─────────────────────

    public function setTwofaSecretAttribute($value)
    {
        $this->attributes['twofa_secret'] = $value ? encrypt($value) : null;
    }

    public function getTwofaSecretAttribute($value)
    {
        if (!$value) return null;
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    // ── Timezone helpers (Item #25) ───────────────────────

    /**
     * Get this admin's timezone, falling back to portal default then server.
     */
    public function getTimezoneAttribute($value): string
    {
        if ($value && $value !== '') {
            return $value;
        }
        return Configuration::get('default_timezone', config('app.timezone', 'UTC'));
    }

    /**
     * Get current time in admin's timezone.
     */
    public function localNow(): \Carbon\Carbon
    {
        return now()->setTimezone($this->timezone);
    }

    // ── Relationships ──────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    // ── Role helpers ───────────────────────────────────────

    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole('administrator');
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole('supervisor');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function updateLastLogin(): void
    {
        $this->datetime_lastlogin = now();
        $this->save();
    }

    /**
     * Check if admin must change their password (first login or forced).
     */
    public function mustChangePassword(): bool
    {
        return is_null($this->password_changed_at);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function getIsActiveAttribute($value)
    {
        return (bool) $value;
    }

    // ── Account Lockout ─────────────────────────────────

    /**
     * Check if account is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * Record a failed login attempt. Lock account after threshold.
     */
    public function recordFailedLogin(): void
    {
        $maxAttempts = (int) Configuration::get('max_failed_logins', 10);
        $count = ($this->failed_login_count ?? 0) + 1;

        $data = ['failed_login_count' => $count];

        if ($maxAttempts > 0 && $count >= $maxAttempts) {
            $data['locked_at'] = now();
            $data['lock_reason'] = "Locked after {$count} failed login attempts";
            $data['is_active'] = 0;
        }

        $this->update($data);
    }

    /**
     * Reset failed login counter on successful login.
     */
    public function resetFailedLogins(): void
    {
        if ($this->failed_login_count > 0 || $this->locked_at) {
            $this->update([
                'failed_login_count' => 0,
                'locked_at' => null,
                'lock_reason' => null,
            ]);
        }
    }

    // ── Encrypted URL Tokens ────────────────────────────

    /**
     * Generate an encrypted URL token for this admin.
     * Used in routes instead of raw IDs (e.g. /users/{token}/edit).
     */
    public function getRouteToken(): string
    {
        return encrypt($this->id);
    }

    /**
     * Resolve an admin from an encrypted token.
     * Returns null if decryption fails or admin not found.
     */
    public static function findByToken(string $token): ?self
    {
        try {
            $id = decrypt($token);
        } catch (\Exception $e) {
            return null;
        }
        return static::with('role')->find($id);
    }

    /**
     * findByToken but throw 404 if not found.
     */
    public static function findByTokenOrFail(string $token): self
    {
        $admin = static::findByToken($token);
        if (!$admin) abort(404);
        return $admin;
    }

    // ── Per-User Permission Overrides ───────────────────

    /**
     * User-level menu permission overrides (on top of role permissions).
     */
    public function menuOverrides()
    {
        return $this->hasMany(AdminUserMenuAccess::class, 'admin_id');
    }

    /**
     * Check menu permission: user override first, then fall back to role.
     */
    public function hasMenuPermission(int $menuId, string $permission = 'can_view'): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        // Check user-level override first
        $userOverride = AdminUserMenuAccess::where('admin_id', $this->id)
            ->where('menu_id', $menuId)
            ->first();

        if ($userOverride) {
            return (bool) $userOverride->{$permission};
        }

        // Fall back to role-level permission
        $access = AdminRoleMenuAccess::where('role_id', $this->role_id)
            ->where('menu_id', $menuId)
            ->first();

        return $access && $access->{$permission};
    }
}
