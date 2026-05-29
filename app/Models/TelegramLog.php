<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramLog extends Model
{
    protected $table = 'tbl_telegram_log';
    public $timestamps = false;

    protected $fillable = [
        'type', 'target', 'chat_id', 'message', 'status', 'error', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function scopeSent($q) { return $q->where('status', 'sent'); }
    public function scopeFailed($q) { return $q->where('status', 'failed'); }
}
