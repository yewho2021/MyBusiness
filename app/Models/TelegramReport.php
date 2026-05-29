<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramReport extends Model
{
    protected $table = 'tbl_telegram_reports';

    protected $fillable = [
        'name', 'slug', 'report_type', 'description', 'category', 'icon',
        'default_params', 'param_schema', 'query', 'template', 'computed_fields',
        'php_code',
        'is_system', 'enabled', 'sort_order',
        'target_id', 'schedule_type', 'schedule_time', 'schedule_day',
        'timezone', 'params',
        'last_sent_at', 'last_status', 'last_error',
        'send_count', 'fail_count', 'consecutive_fails',
    ];

    protected $casts = [
        'default_params'   => 'array',
        'param_schema'     => 'array',
        'computed_fields'  => 'array',
        'params'           => 'array',
        'is_system'        => 'boolean',
        'enabled'          => 'boolean',
        'last_sent_at'     => 'datetime',
    ];

    public function target()
    {
        return $this->belongsTo(TelegramTarget::class, 'target_id');
    }
}
