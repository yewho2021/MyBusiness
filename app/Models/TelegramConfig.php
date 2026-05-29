<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramConfig extends Model
{
    protected $table = 'tbl_telegram_config';

    protected $fillable = ['key_name', 'value'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::where('key_name', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::updateOrCreate(['key_name' => $key], ['value' => $value ?? '']);
    }
}
