<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CompanyEmailConfig extends Model
{
    protected $table = 'tbl_company_email_config';

    protected $fillable = [
        'company_id',
        'name',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_name',
        'from_email',
        'reply_to',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'port' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['company_id', 'name', 'host', 'port', 'encryption', 'from_email', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('admin')
            ->setDescriptionForEvent(fn(string $eventName) => "Email config was {$eventName}");
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(CompanyEmailTemplate::class, 'smtp_id');
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
