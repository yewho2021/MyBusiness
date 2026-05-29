<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueryHistory extends Model
{
    protected $table = 'tbl_query_history';

    protected $fillable = [
        'admin_id',
        'sql_query',
        'created_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the admin who ran this query.
     */
    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'admin_id');
    }
}
