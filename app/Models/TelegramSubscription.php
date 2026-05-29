<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramSubscription extends Model
{
    protected $table = 'tbl_telegram_subscriptions';

    protected $fillable = [
        'name', 'report_id', 'target_id', 'schedule_type', 'schedule_time',
        'schedule_day', 'timezone', 'params', 'enabled', 'created_by',
    ];

    protected $casts = [
        'params'       => 'array',
        'enabled'      => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(TelegramReport::class, 'report_id');
    }

    public function target()
    {
        return $this->belongsTo(TelegramTarget::class, 'target_id');
    }
}
