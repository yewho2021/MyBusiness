<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    protected $table = 'tbl_backup_logs';

    public $timestamps = false;

    protected $fillable = [
        'run_id',
        'level',
        'message',
        'file_path',
        'file_size',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    const LEVEL_INFO = 'info';
    const LEVEL_SUCCESS = 'success';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    public function run(): BelongsTo
    {
        return $this->belongsTo(BackupRun::class, 'run_id');
    }
}
