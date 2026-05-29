<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CompanyEmailTemplate extends Model
{
    protected $table = 'tbl_company_email_template';

    protected $fillable = [
        'company_id',
        'smtp_id',
        'slug',
        'name',
        'subject',
        'content',
        'email_to',
        'email_cc',
        'email_bcc',
        'variables',
        'status',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['company_id', 'slug', 'name', 'subject', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('admin')
            ->setDescriptionForEvent(fn(string $eventName) => "Email template was {$eventName}");
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function smtp(): BelongsTo
    {
        return $this->belongsTo(CompanyEmailConfig::class, 'smtp_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('company_id');
    }

    public function isGlobal(): bool
    {
        return $this->company_id === null;
    }
}
