<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueryBookmark extends Model
{
    protected $table = 'tbl_query_bookmarks';

    protected $fillable = [
        'title',
        'sql_query',
        'created_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
