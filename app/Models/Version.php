<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Version extends Model
{
    protected $table = 'tbl_versions';
    public $timestamps = false;

    protected $fillable = [
        'version_code', 'version_label', 'type',
        'file_name', 'file_hash', 'description',
        'rollback_target_code', 'rollback_from_code', 'rollback_chain',
        'code_files', 'sql_files', 'files_created', 'files_overwritten',
        'files_restored', 'files_deleted',
        'sql_ok', 'sql_err', 'total_backup_bytes',
        'status', 'admin_id', 'admin_name',
        'applied_at', 'elapsed_ms', 'log',
    ];

    protected $casts = [
        'applied_at'     => 'datetime',
        'rollback_chain' => 'array',
        'log'            => 'array',
        'code_files'     => 'integer',
        'sql_files'      => 'integer',
        'elapsed_ms'     => 'integer',
        'total_backup_bytes' => 'integer',
    ];

    // ── Relationships ──────────────────────────────

    public function files(): HasMany
    {
        return $this->hasMany(VersionCode::class, 'version_id');
    }

    public function codeFiles(): HasMany
    {
        return $this->hasMany(VersionCode::class, 'version_id')->where('action', '!=', 'sql');
    }

    public function sqlFiles(): HasMany
    {
        return $this->hasMany(VersionCode::class, 'version_id')->where('action', 'sql');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    // ── Type Checks ────────────────────────────────

    public function isPatch(): bool
    {
        return $this->type === 'patch';
    }

    public function isRollback(): bool
    {
        return $this->type === 'rollback';
    }

    public function isLegacy(): bool
    {
        return $this->type === 'legacy';
    }

    /**
     * Can this version be used as a rollback target?
     * Requires: success status + has code backups stored in DB.
     */
    public function canRestore(): bool
    {
        if ($this->status !== 'success') return false;
        return $this->codeFiles()->exists();
    }

    // ── Display Helpers ────────────────────────────

    public function getDisplayCode(): string
    {
        return 'v' . $this->version_code;
    }

    public function getBackupSizeHuman(): string
    {
        $bytes = $this->total_backup_bytes ?? 0;
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public function getTotalFilesAttribute(): int
    {
        return $this->code_files + $this->sql_files;
    }

    // ── Static Helpers ─────────────────────────────

    /**
     * Get the latest (most recent) version.
     */
    public static function latest(): ?self
    {
        return static::orderBy('version_code', 'desc')->first();
    }

    /**
     * Generate the next version code (YmdHis).
     */
    public static function generateCode(): string
    {
        return date('YmdHis');
    }

    /**
     * Check if a patch with the same filename was applied before.
     */
    public static function findByFileName(string $fileName): ?self
    {
        return static::where('file_name', $fileName)->orderBy('version_code', 'desc')->first();
    }

    /**
     * Check if a patch with the same content hash was applied.
     */
    public static function findByHash(string $hash): ?self
    {
        return static::where('file_hash', $hash)->orderBy('version_code', 'desc')->first();
    }

    /**
     * Get all versions between two codes (exclusive of target, inclusive of from).
     * Returns in DESCENDING order (newest first) for rollback processing.
     */
    public static function getChainBetween(string $targetCode, string $fromCode)
    {
        return static::where('version_code', '>', $targetCode)
            ->where('version_code', '<=', $fromCode)
            ->where('status', 'success')
            ->orderBy('version_code', 'desc')
            ->get();
    }
}
