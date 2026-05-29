<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\InvalidatesMenuCache;

class AdminMenu extends Model
{
    use HasFactory, LogsActivity, InvalidatesMenuCache;

    protected $table = 'tbl_admin_menus';

    protected $fillable = [
        'group_id',
        'parent_id',
        'level',
        'title',
        'icon',
        'route_name',
        'url',
        'permission_key',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
    ];

    // ── Activity Log Options ─────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'icon', 'route_name', 'sort_order', 'is_active', 'group_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('admin')
            ->setDescriptionForEvent(fn(string $eventName) => "Menu item was {$eventName}");
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AdminMenuGroup::class, 'group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AdminMenu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AdminMenu::class, 'parent_id')
            ->where('is_active', 1)
            ->orderBy('sort_order');
    }

    public function roleAccess(): HasMany
    {
        return $this->hasMany(AdminRoleMenuAccess::class, 'menu_id');
    }

    public function getUrl(): string
    {
        if ($this->route_name && Route::has($this->route_name)) {
            return route($this->route_name);
        }
        return $this->url ?? '#';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
