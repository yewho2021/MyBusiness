<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseConnection extends Model
{
    protected $table = 'tbl_database';

    protected $fillable = [
        'name',
        'dbhost',
        'dbport',
        'dbname',
        'dbusername',
        'dbpassword',
        'description',
        'is_active',
        'last_connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
    ];

    /**
     * Password is stored encrypted.
     */
    public function setDbpasswordAttribute($value)
    {
        $this->attributes['dbpassword'] = encrypt($value);
    }

    public function getDbpasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value; // fallback for plain-text during migration
        }
    }
}
