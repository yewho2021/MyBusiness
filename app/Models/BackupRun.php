<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupRun extends Model
{
    protected $table = 'tbl_backup_runs';

    protected $fillable = [
        'job_id',
        'folder_name',
        'destination_path',
        'zip_path',
        'status',
        'total_files',
        'processed_files',
        'total_size',
        'zip_size',
        'include_paths',
        'exclude_paths',
        'exclude_extensions',
        'include_database',
        'error_message',
        'started_at',
        'completed_at',
        'description',
    ];

    protected $casts = [
        'include_paths' => 'array',
        'exclude_paths' => 'array',
        'exclude_extensions' => 'array',
        'include_database' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_RESTORING = 'restoring';
    const STATUS_RESTORED = 'restored';

    public function job(): BelongsTo
    {
        return $this->belongsTo(BackupJob::class, 'job_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(BackupLog::class, 'run_id');
    }

    public function getProgressAttribute(): int
    {
        if ($this->total_files <= 0)
            return 0;
        return min(100, (int) round(($this->processed_files / $this->total_files) * 100));
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->total_size ?? 0;
        if ($bytes >= 1073741824)
            return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)
            return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)
            return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getDurationAttribute(): string
    {
        if (!$this->started_at || !$this->completed_at)
            return '--';
        $seconds = abs($this->started_at->diffInSeconds($this->completed_at));
        if ($seconds < 60)
            return $seconds . 's';
        if ($seconds < 3600)
            return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
        return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
    }

    public function getBackupPath(): string
    {
        $basePath = $this->destination_path;

        if (empty($basePath)) {
            // Fall back to job's destination, then default
            $basePath = $this->job?->getDestinationPath() ?? base_path('backup');
        } elseif (!str_starts_with($basePath, '/')) {
            $basePath = base_path($basePath);
        }

        return rtrim($basePath, '/') . '/' . $this->folder_name;
    }

    /**
     * Get the full path to the ZIP file.
     */
    public function getZipPath(): ?string
    {
        if ($this->zip_path) {
            return str_starts_with($this->zip_path, '/')
                ? $this->zip_path
                : base_path($this->zip_path);
        }

        // Try conventional location
        $zipFile = $this->getBackupPath() . '.zip';
        return file_exists($zipFile) ? $zipFile : null;
    }

    /**
     * Check if this backup has a downloadable ZIP.
     */
    public function hasZip(): bool
    {
        $path = $this->getZipPath();
        return $path && file_exists($path);
    }

    /**
     * Get formatted ZIP size.
     */
    public function getFormattedZipSizeAttribute(): string
    {
        $bytes = $this->zip_size ?? 0;
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Is this a ZIP-based backup or legacy folder backup?
     */
    public function isZipBackup(): bool
    {
        return !empty($this->zip_path) || $this->hasZip();
    }
}
