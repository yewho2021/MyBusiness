@extends('admin.layouts.app')
@section('title', 'System Status')

@push('styles')
<style>
.ss-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:16px; margin-bottom:16px; }
.ss-card { background:var(--card-bg,#fff); border:1px solid var(--card-border,#e2e8f0); border-radius:var(--card-radius,12px); padding:20px; }
.ss-card-title { font-size:14px; font-weight:700; color:var(--text-heading); margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.ss-card-title i { font-size:13px; opacity:.7; }
.ss-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid var(--border-light,#f1f5f9); font-size:13px; }
.ss-row:last-child { border-bottom:none; }
.ss-label { color:var(--text-secondary,#475569); }
.ss-value { font-weight:600; color:var(--text-primary,#1e293b); font-family:var(--font-mono); font-size:12px; }
.ss-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:6px; font-size:11px; font-weight:600; }
.ss-ok { background:var(--c-success-light,#f0fdf4); color:var(--c-success,#16a34a); }
.ss-warn { background:var(--c-warning-light,#fffbeb); color:var(--c-warning,#d97706); }
.ss-err { background:var(--c-danger-light,#fef2f2); color:var(--c-danger,#dc2626); }
.ss-info { background:var(--c-info-light,#f0f9ff); color:var(--c-info,#0ea5e9); }
.ss-ext-grid { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
.ss-ext { padding:3px 8px; border-radius:5px; font-size:11px; font-weight:600; }
.ss-table { width:100%; border-collapse:collapse; font-size:12px; margin-top:8px; }
.ss-table th { text-align:left; padding:8px 10px; background:var(--table-header-bg,#f8fafc); font-weight:600; color:var(--text-secondary); border-bottom:2px solid var(--border-color,#e2e8f0); }
.ss-table td { padding:7px 10px; border-bottom:1px solid var(--border-light,#f1f5f9); color:var(--text-body); }
.ss-table .mono { font-family:var(--font-mono); font-size:11px; }
.ss-bar { height:8px; border-radius:4px; background:var(--border-light,#f1f5f9); overflow:hidden; margin-top:8px; }
.ss-bar-fill { height:100%; border-radius:4px; transition:width .3s; }
.ss-bar-healthy { background:var(--c-success,#16a34a); }
.ss-bar-warning { background:var(--c-warning,#d97706); }
.ss-bar-critical { background:var(--c-danger,#dc2626); }
.ss-error-line { font-family:var(--font-mono); font-size:11px; padding:6px 10px; background:var(--c-danger-light,#fef2f2); border-left:3px solid var(--c-danger,#dc2626); border-radius:4px; margin-bottom:6px; color:var(--text-body); word-break:break-all; }
.ss-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.ss-refresh { padding:8px 16px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:var(--btn-radius,8px); font-size:13px; cursor:pointer; color:var(--text-secondary); display:flex; align-items:center; gap:6px; }
.ss-refresh:hover { background:var(--hover-bg); border-color:var(--hover-border); }
</style>
@endpush

@section('content')
<div class="ss-header">
    <div>
        <h2 style="font-size:var(--fs-h2);font-weight:700;color:var(--text-heading);">System Status</h2>
        <p style="font-size:var(--fs-sm);color:var(--text-muted);margin-top:4px;">Server diagnostics and health overview</p>
    </div>
    <button class="ss-refresh" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Refresh</button>
</div>

{{-- Row 1: PHP + MySQL + Disk --}}
<div class="ss-grid">
    {{-- PHP Info --}}
    <div class="ss-card">
        <div class="ss-card-title"><i class="fab fa-php" style="color:#777BB4;font-size:18px;"></i> PHP</div>
        <div class="ss-row"><span class="ss-label">Version</span><span class="ss-value">{{ $php['version'] }}</span></div>
        <div class="ss-row"><span class="ss-label">SAPI</span><span class="ss-value">{{ $php['sapi'] }}</span></div>
        <div class="ss-row"><span class="ss-label">Memory Limit</span><span class="ss-value">{{ $php['memory_limit'] }}</span></div>
        <div class="ss-row"><span class="ss-label">Max Execution</span><span class="ss-value">{{ $php['max_execution'] }}s</span></div>
        <div class="ss-row"><span class="ss-label">Upload Max</span><span class="ss-value">{{ $php['upload_max'] }}</span></div>
        <div class="ss-row"><span class="ss-label">Post Max</span><span class="ss-value">{{ $php['post_max'] }}</span></div>
        <div class="ss-row"><span class="ss-label">Timezone</span><span class="ss-value">{{ $php['timezone'] }}</span></div>
        <div style="margin-top:10px;font-size:12px;font-weight:600;color:var(--text-secondary);">Extensions</div>
        <div class="ss-ext-grid">
            @foreach($php['extensions'] as $ext => $loaded)
                <span class="ss-ext {{ $loaded ? 'ss-ok' : 'ss-err' }}">
                    <i class="fas {{ $loaded ? 'fa-check' : 'fa-times' }}" style="font-size:9px;"></i> {{ $ext }}
                </span>
            @endforeach
        </div>
    </div>

    {{-- MySQL --}}
    <div class="ss-card">
        <div class="ss-card-title"><i class="fas fa-database" style="color:#00758f;"></i> MySQL</div>
        @if($mysql['connected'] ?? false)
            <div class="ss-row"><span class="ss-label">Version</span><span class="ss-value">{{ $mysql['version'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Database</span><span class="ss-value">{{ $mysql['database'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Host</span><span class="ss-value">{{ $mysql['host'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Charset</span><span class="ss-value">{{ $mysql['charset'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Collation</span><span class="ss-value">{{ $mysql['collation'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Max Connections</span><span class="ss-value">{{ $mysql['max_connections'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Buffer Pool</span><span class="ss-value">{{ $mysql['buffer_pool'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Uptime</span><span class="ss-value">{{ $mysql['uptime'] }}</span></div>
        @else
            <div class="ss-badge ss-err"><i class="fas fa-times-circle"></i> Connection failed: {{ $mysql['error'] ?? 'Unknown' }}</div>
        @endif
    </div>

    {{-- Disk --}}
    <div class="ss-card">
        <div class="ss-card-title"><i class="fas fa-hdd" style="color:var(--text-muted);"></i> Disk Usage</div>
        @if($disk['available'] ?? false)
            <div class="ss-row"><span class="ss-label">Total</span><span class="ss-value">{{ $disk['total'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Used</span><span class="ss-value">{{ $disk['used'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Free</span><span class="ss-value">{{ $disk['free'] }}</span></div>
            <div class="ss-row">
                <span class="ss-label">Usage</span>
                <span class="ss-badge {{ $disk['status'] === 'critical' ? 'ss-err' : ($disk['status'] === 'warning' ? 'ss-warn' : 'ss-ok') }}">
                    {{ $disk['percent'] }}%
                </span>
            </div>
            <div class="ss-bar">
                <div class="ss-bar-fill ss-bar-{{ $disk['status'] }}" style="width:{{ $disk['percent'] }}%"></div>
            </div>
        @else
            <span class="ss-badge ss-warn">Disk info unavailable</span>
        @endif
    </div>
</div>

{{-- Row 2: Laravel + OPcache + Sessions/Backup --}}
<div class="ss-grid">
    {{-- Laravel --}}
    <div class="ss-card">
        <div class="ss-card-title"><i class="fab fa-laravel" style="color:#FF2D20;"></i> Laravel</div>
        <div class="ss-row"><span class="ss-label">Version</span><span class="ss-value">{{ $laravel['version'] }}</span></div>
        <div class="ss-row"><span class="ss-label">Environment</span>
            <span class="ss-badge {{ $laravel['environment'] === 'production' ? 'ss-ok' : 'ss-warn' }}">{{ $laravel['environment'] }}</span>
        </div>
        <div class="ss-row"><span class="ss-label">Debug Mode</span>
            <span class="ss-badge {{ $laravel['debug'] ? 'ss-warn' : 'ss-ok' }}">{{ $laravel['debug'] ? 'ON' : 'OFF' }}</span>
        </div>
        <div class="ss-row"><span class="ss-label">Config Cached</span>
            <span class="ss-badge {{ $laravel['config_cached'] ? 'ss-ok' : 'ss-info' }}">{{ $laravel['config_cached'] ? 'Yes' : 'No' }}</span>
        </div>
        <div class="ss-row"><span class="ss-label">Routes Cached</span>
            <span class="ss-badge {{ $laravel['routes_cached'] ? 'ss-ok' : 'ss-info' }}">{{ $laravel['routes_cached'] ? 'Yes' : 'No' }}</span>
        </div>
        <div class="ss-row"><span class="ss-label">Compiled Views</span><span class="ss-value">{{ $laravel['views_compiled'] }}</span></div>
        <div class="ss-row"><span class="ss-label">Session Driver</span><span class="ss-value">{{ $laravel['session_driver'] }}</span></div>
        <div class="ss-row"><span class="ss-label">Cache Driver</span><span class="ss-value">{{ $laravel['cache_driver'] }}</span></div>
    </div>

    {{-- OPcache --}}
    <div class="ss-card">
        <div class="ss-card-title"><i class="fas fa-bolt" style="color:var(--c-warning);"></i> OPcache</div>
        @if($opcache['enabled'] ?? false)
            <div class="ss-row"><span class="ss-label">Memory Used</span><span class="ss-value">{{ $opcache['memory_used'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Memory Free</span><span class="ss-value">{{ $opcache['memory_free'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Wasted</span><span class="ss-value">{{ $opcache['memory_wasted'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Cached Scripts</span><span class="ss-value">{{ $opcache['cached_scripts'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Hit Rate</span>
                <span class="ss-badge ss-ok">{{ $opcache['hit_rate'] }}</span>
            </div>
            <div class="ss-row"><span class="ss-label">OOM Restarts</span><span class="ss-value">{{ $opcache['restarts'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Reset Available</span>
                <span class="ss-badge {{ $opcache['can_reset'] ? 'ss-ok' : 'ss-warn' }}">{{ $opcache['can_reset'] ? 'Yes' : 'No' }}</span>
            </div>
        @else
            <span class="ss-badge ss-warn"><i class="fas fa-info-circle"></i> {{ $opcache['reason'] ?? 'Disabled' }}</span>
        @endif
    </div>

    {{-- Sessions & Backup --}}
    <div class="ss-card">
        <div class="ss-card-title"><i class="fas fa-users" style="color:var(--c-success);"></i> Sessions & Backup</div>
        @if(!isset($sessions['error']))
            <div class="ss-row"><span class="ss-label">Active Sessions</span>
                <span class="ss-badge ss-info">{{ $sessions['active_sessions'] }}</span>
            </div>
            <div class="ss-row"><span class="ss-label">Logins Today</span><span class="ss-value">{{ $sessions['today_logins'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Failed Today</span>
                <span class="ss-value {{ $sessions['failed_today'] > 0 ? 'color:var(--c-danger)' : '' }}">{{ $sessions['failed_today'] }}</span>
            </div>
            <div class="ss-row"><span class="ss-label">Total Admins</span><span class="ss-value">{{ $sessions['active_admins'] }}/{{ $sessions['total_admins'] }}</span></div>
        @endif
        <div style="border-top:1px solid var(--border-light);margin:10px 0;"></div>
        @if(!isset($backup['error']))
            <div class="ss-row"><span class="ss-label">Last Backup</span>
                <span class="ss-badge {{ $backup['status'] === 'healthy' ? 'ss-ok' : ($backup['status'] === 'stale' ? 'ss-warn' : 'ss-err') }}">
                    {{ $backup['last_ago'] }}
                </span>
            </div>
            <div class="ss-row"><span class="ss-label">Last Size</span><span class="ss-value">{{ $backup['last_size'] }}</span></div>
            <div class="ss-row"><span class="ss-label">Total Backups</span><span class="ss-value">{{ $backup['total_backups'] }} ({{ $backup['total_size'] }})</span></div>
            <div class="ss-row"><span class="ss-label">Log Entries</span><span class="ss-value">{{ $backup['log_entries'] }}</span></div>
        @endif
    </div>
</div>

{{-- Row 3: Table Sizes + Storage Breakdown --}}
<div class="ss-grid">
    {{-- Table Sizes --}}
    <div class="ss-card" style="grid-column:span 2;">
        <div class="ss-card-title"><i class="fas fa-table" style="color:var(--c-purple);"></i> Database Tables ({{ $tables['count'] ?? 0 }} tables · {{ $tables['total_size'] ?? '?' }})</div>
        @if(!isset($tables['error']))
            <div style="overflow-x:auto;">
            <table class="ss-table">
                <thead><tr><th>Table</th><th style="text-align:right;">Rows</th><th style="text-align:right;">Size</th><th>Engine</th><th style="width:120px;">Proportion</th></tr></thead>
                <tbody>
                    @php $maxSize = max(1, collect($tables['tables'])->max('size')); @endphp
                    @foreach($tables['tables'] as $t)
                    <tr>
                        <td class="mono">{{ $t['name'] }}</td>
                        <td style="text-align:right;">{{ number_format($t['rows']) }}</td>
                        <td style="text-align:right;" class="mono">{{ $t['size_h'] }}</td>
                        <td>{{ $t['engine'] }}</td>
                        <td>
                            <div class="ss-bar" style="margin:0;">
                                <div class="ss-bar-fill ss-bar-healthy" style="width:{{ round($t['size'] / $maxSize * 100) }}%;opacity:.6;"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

{{-- Row 4: Storage Breakdown + Recent Errors --}}
<div class="ss-grid">
    {{-- Storage Breakdown --}}
    <div class="ss-card">
        <div class="ss-card-title"><i class="fas fa-folder-open" style="color:var(--c-warning);"></i> Storage Breakdown</div>
        <table class="ss-table">
            <thead><tr><th>Directory</th><th style="text-align:right;">Files</th><th style="text-align:right;">Size</th></tr></thead>
            <tbody>
                @foreach($storage as $s)
                <tr>
                    <td>{{ $s['label'] }}<div style="font-size:10px;color:var(--text-faint);font-family:var(--font-mono);">{{ $s['path'] }}</div></td>
                    <td style="text-align:right;">{{ $s['files'] > 0 ? number_format($s['files']) : '—' }}</td>
                    <td style="text-align:right;" class="mono">{{ $s['size_h'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Recent Errors --}}
    <div class="ss-card">
        <div class="ss-card-title"><i class="fas fa-exclamation-circle" style="color:var(--c-danger);"></i> Recent Errors</div>
        @if($errors['available'] ?? false)
            <div class="ss-row" style="margin-bottom:8px;">
                <span class="ss-label">Log File</span>
                <span class="ss-value">{{ $errors['file'] }} ({{ $errors['size'] }})</span>
            </div>
            @if(count($errors['recent']) > 0)
                @foreach($errors['recent'] as $line)
                    <div class="ss-error-line">{{ $line }}</div>
                @endforeach
            @else
                <div style="text-align:center;padding:20px;color:var(--text-faint);font-size:13px;">
                    <i class="fas fa-check-circle" style="color:var(--c-success);font-size:24px;display:block;margin-bottom:8px;"></i>
                    No recent errors
                </div>
            @endif
        @else
            <span class="ss-badge ss-info">No log file found</span>
        @endif
    </div>
</div>
@endsection
