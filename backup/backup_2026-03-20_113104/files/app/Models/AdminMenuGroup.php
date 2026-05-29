<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminMenuGroup extends Model
{
    use HasFactory;

    protected $table = 'tbl_admin_menu_groups';

    protected $fillable = [
        'title',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function menus(): HasMany
    {
        return $this->hasMany(AdminMenu::class, 'group_id');
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
