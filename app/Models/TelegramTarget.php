<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramTarget extends Model
{
    protected $table = 'tbl_telegram_targets';

    protected $fillable = [
        'name', 'chat_id', 'type', 'notes', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function scopeDefault($q) { return $q->where('is_default', 1); }
}
