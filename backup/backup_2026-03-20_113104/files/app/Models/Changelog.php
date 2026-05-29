<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Changelog extends Model
{
    protected $table = 'tbl_changelog';

    protected $fillable = [
        'app_type',
        'version',
        'title',
        'details',
        'technical_info',
        'created_at',
    ];

    protected $casts = [
        'technical_info' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false; // We handle created_at manually for GMT+8 or simpler

    const APP_OFFICE = 'office';
    const APP_APPS = 'apps';
}
