<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupJob extends Model
{
    protected $table = 'tbl_backup_jobs';

    protected $fillable = [
        'name',
        'frequency',
        'cron_expression',
        'destination_path',
        'include_paths',
        'exclude_paths',
        'exclude_extensions',
        'include_database',
        'retention_count',
        'is_active',
        'last_run_at',
        'next_run_at',
        'created_by',
    ];

    protected $casts = [
        'include_paths' => 'array',
        'exclude_paths' => 'array',
        'exclude_extensions' => 'array',
        'include_database' => 'boolean',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(BackupRun::class, 'job_id');
    }

    public function latestRun()
    {
        return $this->hasOne(BackupRun::class, 'job_id')->latestOfMany();
    }

    /**
     * Resolve the backup destination path.
     * Supports absolute paths (/home/...) or relative to base_path().
     * Falls back to base_path('backup') if not set.
     */
    public function getDestinationPath(): string
    {
        $path = $this->destination_path;

        if (empty($path)) {
            return base_path('backup');
        }

        // Absolute path
        if (str_starts_with($path, '/')) {
            return rtrim($path, '/');
        }

        // Relative to base_path
        return base_path(rtrim($path, '/'));
    }

    public function getCronExpressionAttribute($value)
    {
        if ($value) return $value;

        return match ($this->frequency) {
            'daily' => '0 2 * * *',
            'weekly' => '0 2 * * 0',
            'monthly' => '0 2 1 * *',
            default => '0 2 * * *',
        };
    }
}
