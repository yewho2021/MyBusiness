<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAdmin extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_company_admin';

    protected $fillable = [
        'company_id',
        'role_id',
        'name',
        'email',
        'mobile_no',
        'password',
        'is_owner',
        'email_verified_at',
        'mobile_verified_at',
        'last_login_at',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(CompanyRole::class, 'role_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
