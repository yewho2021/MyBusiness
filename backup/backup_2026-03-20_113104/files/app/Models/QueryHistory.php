<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueryHistory extends Model
{
    protected $table = 'tbl_query_history';

    protected $fillable = [
        'sql_query',
        'created_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
