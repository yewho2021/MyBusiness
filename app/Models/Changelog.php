<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Changelog extends Model
{
    protected $table = 'tbl_changelog';

    protected $fillable = [
        'version_id',
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

    public $timestamps = false;

    const APP_OFFICE = 'office';
    const APP_APPS = 'apps';

    /**
     * Link to tbl_versions (nullable — legacy entries have no version).
     */
    public function patchVersion(): BelongsTo
    {
        return $this->belongsTo(Version::class, 'version_id');
    }

    /**
     * Check if this changelog entry has a linked patch version with file backups.
     */
    public function hasVersionFiles(): bool
    {
        return $this->version_id && $this->patchVersion && $this->patchVersion->files()->exists();
    }
}
