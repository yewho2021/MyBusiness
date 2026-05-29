<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\InvalidatesMenuCache;

class AdminRole extends Model
{
    use HasFactory, LogsActivity, InvalidatesMenuCache;

    protected $table = 'tbl_admin_roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    // ── Activity Log Options ─────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'level', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('admin')
            ->setDescriptionForEvent(fn(string $eventName) => "Role was {$eventName}");
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'role_id');
    }

    public function menuAccess(): HasMany
    {
        return $this->hasMany(AdminRoleMenuAccess::class, 'role_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
