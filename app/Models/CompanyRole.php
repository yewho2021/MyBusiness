<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyRole extends Model
{
    protected $table = 'tbl_company_role';

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'permissions',
        'is_owner',
        'status',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_owner' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function admins(): HasMany
    {
        return $this->hasMany(CompanyAdmin::class, 'role_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
