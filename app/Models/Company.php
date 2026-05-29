<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Company extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'tbl_company';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'code',
        'company_name',
        'name',
        'email',
        'mobile_no',
        'password',
        'company_info',
        'logo_path',
        'timezone',
        'setup_step',
        'agreement_id',
        'agreement_accepted_at',
        'email_verified_at',
        'mobile_verified_at',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'company_info' => 'array',
        'setup_step' => 'integer',
        'agreement_accepted_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['company_name', 'name', 'email', 'status', 'setup_step'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('admin')
            ->setDescriptionForEvent(fn(string $eventName) => "Company was {$eventName}");
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(CompanyAgreement::class, 'agreement_id');
    }

    public function admins(): HasMany
    {
        return $this->hasMany(CompanyAdmin::class, 'company_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(CompanyRole::class, 'company_id');
    }

    public function partners(): HasMany
    {
        return $this->hasMany(CompanyPartner::class, 'company_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(CompanyProduct::class, 'company_id');
    }

    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(
            RefIndustrySubcategory::class,
            'tbl_company_industry',
            'company_id',
            'subcategory_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSetupComplete(): bool
    {
        return $this->setup_step >= 3;
    }
}
