<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportHistory extends Model
{
    protected $table = 'tbl_export_history';

    protected $fillable = [
        'source',
        'format',
        'file_path',
        'file_name',
        'file_size',
        'filters',
        'row_count',
        'admin_id',
    ];

    protected $casts = [
        'filters' => 'array',
        'file_size' => 'integer',
        'row_count' => 'integer',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'admin_users'   => 'Admin Users',
            'login_log'     => 'Login Log',
            'activity_log'  => 'Activity Log',
            'configuration' => 'Configuration',
            'backup_history'=> 'Backup History',
            'changelog'     => 'Changelog',
            'custom_query'  => 'Custom SQL Query',
            default         => ucfirst(str_replace('_', ' ', $this->source)),
        };
    }
}
