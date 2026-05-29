<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    use HasFactory;

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
