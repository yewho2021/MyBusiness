<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPartner extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_company_partner';

    protected $fillable = [
        'company_id',
        'upline_id',
        'partner_type',
        'name',
        'email',
        'mobile_no',
        'password',
        'referral_code',
        'ic_number',
        'company_name',
        'registration_no',
        'tin',
        'sst_no',
        'email_verified_at',
        'mobile_verified_at',
        'document_verified_at',
        'country',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'document_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function upline(): BelongsTo
    {
        return $this->belongsTo(self::class, 'upline_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
