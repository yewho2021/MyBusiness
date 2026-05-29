<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_admin';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role_id',
        'is_active',
        'datetime_lastlogin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'datetime_creation' => 'datetime',
        'datetime_lastlogin' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

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

    public function hasMenuPermission(int $menuId, string $permission = 'can_view'): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        $access = AdminRoleMenuAccess::where('role_id', $this->role_id)
            ->where('menu_id', $menuId)
            ->first();

        return $access && $access->{$permission};
    }

    public function updateLastLogin(): void
    {
        $this->datetime_lastlogin = now();
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    
    public function getIsActiveAttribute($value)
    {
        return (bool) $value;
    }
}
