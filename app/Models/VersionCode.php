<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersionCode extends Model
{
    protected $table = 'tbl_version_code';
    public $timestamps = false;

    protected $fillable = [
        'version_id', 'file_path', 'action',
        'content_before', 'content_after',
        'size_before', 'size_after',
        'hash_before', 'hash_after',
    ];

    // Do NOT cast content_before/content_after — they are raw binary (gzcompress output).
    // Use getContentBefore() / getContentAfter() methods instead.

    protected $casts = [
        'size_before' => 'integer',
        'size_after'  => 'integer',
    ];

    // ── Relationships ──────────────────────────────

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class, 'version_id');
    }

    // ── Content Access (with decompression) ────────

    /**
     * Get the file content BEFORE this patch was applied.
     * Returns NULL for 'create' actions (file didn't exist).
     */
    public function getContentBefore(): ?string
    {
        return self::decompress($this->content_before);
    }

    /**
     * Get the file content AFTER this patch was applied.
     */
    public function getContentAfter(): ?string
    {
        return self::decompress($this->content_after);
    }

    // ── Static Compression Helpers ─────────────────

    /**
     * Compress content for storage.
     * Uses gzcompress level 6 (good balance of speed vs ratio).
     */
    public static function compress(?string $content): ?string
    {
        if ($content === null) return null;
        return gzcompress($content, 6);
    }

    /**
     * Decompress stored content.
     * Falls back to raw content if decompression fails (backward compat).
     */
    public static function decompress(?string $data): ?string
    {
        if ($data === null) return null;
        $result = @gzuncompress($data);
        return $result !== false ? $result : $data;
    }

    // ── Factory Methods ────────────────────────────

    /**
     * Create a version_code entry from file content (handles compression + hashing).
     */
    public static function store(int $versionId, string $filePath, string $action, ?string $before, ?string $after): self
    {
        return self::create([
            'version_id'     => $versionId,
            'file_path'      => $filePath,
            'action'         => $action,
            'content_before' => self::compress($before),
            'content_after'  => self::compress($after),
            'size_before'    => $before !== null ? strlen($before) : null,
            'size_after'     => $after !== null ? strlen($after) : null,
            'hash_before'    => $before !== null ? md5($before) : null,
            'hash_after'     => $after !== null ? md5($after) : null,
        ]);
    }

    // ── Display Helpers ────────────────────────────

    public function getSizeBeforeHuman(): string
    {
        return $this->size_before !== null ? self::formatBytes($this->size_before) : '—';
    }

    public function getSizeAfterHuman(): string
    {
        return $this->size_after !== null ? self::formatBytes($this->size_after) : '—';
    }

    public function getActionLabel(): string
    {
        return match ($this->action) {
            'create'    => 'New',
            'overwrite' => 'Modified',
            'sql'       => 'SQL',
            default     => $this->action,
        };
    }

    protected static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
