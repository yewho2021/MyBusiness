@extends('admin.layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
.dash-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px}
.dash-header h1{font-size:22px;font-weight:700;color:#1e293b;margin-bottom:4px}
.dash-header p{font-size:13px;color:#64748b}

.welcome-banner{background:#111;border-radius:12px;padding:24px 28px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.welcome-left h2{font-size:20px;font-weight:700;color:#fff;margin-bottom:6px}
.welcome-left p{font-size:13px;color:#9ca3af}
.welcome-left p strong{color:#d1d5db}
.welcome-right{display:flex;gap:10px;flex-wrap:wrap}
.welcome-badge{padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px}
.welcome-badge.role{background:rgba(255,255,255,.1);color:#e5e7eb}
.welcome-badge.time{background:rgba(255,255,255,.06);color:#9ca3af;font-weight:400}

.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
@media(max-width:1200px){.stats-row{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.stats-row{grid-template-columns:1fr}}
.stat-card{background:#fff;border-radius:12px;padding:20px 24px;border:1px solid #f1f5f9;transition:all .2s}
.stat-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.06);border-color:#e2e8f0}
.stat-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.stat-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px}
.stat-icon.blue{background:#eff6ff;color:#2563eb}
.stat-icon.green{background:#f0fdf4;color:#16a34a}
.stat-icon.amber{background:#fffbeb;color:#d97706}
.stat-icon.purple{background:#f5f3ff;color:#7c3aed}
.stat-badge{font-size:11px;font-weight:600;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:3px}
.stat-badge.up{background:#f0fdf4;color:#16a34a}
.stat-badge.neutral{background:#f8fafc;color:#64748b}
.stat-value{font-size:26px;font-weight:700;color:#1e293b;line-height:1.2;margin-bottom:4px}
.stat-label{font-size:13px;color:#64748b;font-weight:500}

.card-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
@media(max-width:900px){.card-grid{grid-template-columns:1fr}}
.card-grid.three{grid-template-columns:1fr 1fr 1fr}
@media(max-width:1100px){.card-grid.three{grid-template-columns:1fr 1fr}}
@media(max-width:700px){.card-grid.three{grid-template-columns:1fr}}

.card{background:#fff;border-radius:12px;border:1px solid #f1f5f9;overflow:hidden}
.card:hover{border-color:#e2e8f0}
.card-head{padding:18px 22px;display:flex;justify-content:space-between;align-items:center}
.card-title{font-size:15px;font-weight:600;color:#1e293b;display:flex;align-items:center;gap:8px}
.card-title i{color:#94a3b8;font-size:14px}
.card-subtitle{font-size:12px;color:#94a3b8;font-weight:400;margin-left:4px}
.card-action{font-size:12px;color:#2563eb;text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:4px}
.card-action:hover{color:#1d4ed8}
.card-body{padding:0 22px 18px}
.card-body.no-pad{padding:0}

.card-table{width:100%;border-collapse:collapse}
.card-table th{text-align:left;padding:10px 22px;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;background:#fafbfc;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9}
.card-table td{padding:12px 22px;font-size:13px;color:#374151;border-bottom:1px solid #f8fafc}
.card-table tbody tr:last-child td{border-bottom:none}
.card-table tbody tr:hover td{background:#fafbfc}

.badge{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600}
.badge.green{background:#f0fdf4;color:#16a34a}
.badge.red{background:#fef2f2;color:#dc2626}
.badge.blue{background:#eff6ff;color:#2563eb}
.badge.amber{background:#fffbeb;color:#d97706}
.badge.gray{background:#f3f4f6;color:#6b7280}
.badge.purple{background:#f5f3ff;color:#7c3aed}

.progress-bar{width:100%;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden}
.progress-fill{height:100%;border-radius:3px;transition:width .6s ease}
.progress-fill.blue{background:#2563eb}

.info-list{display:flex;flex-direction:column;gap:0}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f8fafc}
.info-row:last-child{border-bottom:none}
.info-label{font-size:13px;color:#64748b;display:flex;align-items:center;gap:8px}
.info-label i{width:16px;color:#94a3b8;text-align:center}
.info-value{font-size:13px;color:#1e293b;font-weight:600}

.quick-links{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.quick-link{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:8px;border:1px solid #f1f5f9;text-decoration:none;color:#374151;font-size:13px;font-weight:500;transition:all .15s}
.quick-link:hover{background:#f8fafc;border-color:#e2e8f0}
.quick-link i{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.quick-link .ql-text{display:flex;flex-direction:column}
.quick-link .ql-text span:first-child{font-weight:600;color:#1e293b}
.quick-link .ql-text span:last-child{font-size:11px;color:#94a3b8;font-weight:400}
</style>
@endpush

@section('content')
<div class="welcome-banner">
    <div class="welcome-left">
        <h2>Welcome back, {{ $admin->name ?? 'Admin' }}</h2>
        <p>Logged in as <strong>{{ $admin->role->name ?? 'User' }}</strong> &middot; Here's your system overview</p>
    </div>
    <div class="welcome-right">
        <span class="welcome-badge role"><i class="fas fa-shield-alt"></i> {{ $admin->role->name ?? 'User' }}</span>
        <span class="welcome-badge time"><i class="fas fa-clock"></i> {{ now()->format('D, d M Y') }}</span>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon blue"><i class="fas fa-table"></i></div>
            <span class="stat-badge neutral"><i class="fas fa-database"></i> {{ $dbName }}</span>
        </div>
        <div class="stat-value">{{ number_format($totalTables) }}</div>
        <div class="stat-label">Database Tables</div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon green"><i class="fas fa-hdd"></i></div>
            <span class="stat-badge up"><i class="fas fa-check-circle"></i> Active</span>
        </div>
        <div class="stat-value">{{ $totalDbSize >= 1048576 ? number_format($totalDbSize/1048576,1).' MB' : number_format($totalDbSize/1024,1).' KB' }}</div>
        <div class="stat-label">Database Size</div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon amber"><i class="fas fa-layer-group"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalRows) }}</div>
        <div class="stat-label">Total Rows</div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon purple"><i class="fas fa-plug"></i></div>
        </div>
        <div class="stat-value">{{ $savedConnections }}</div>
        <div class="stat-label">Saved Connections</div>
    </div>
</div>

<div class="card-grid">
    <div class="card">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-code-branch"></i> Recent Changes <span class="card-subtitle">{{ $totalChangelogs }} total</span></span>
            <a href="{{ route('admin.changelog.index') }}" class="card-action">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body no-pad">
            <table class="card-table">
                <thead><tr><th>Version</th><th>Title</th><th>Type</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($recentChangelogs as $log)
                    <tr>
                        <td><span class="badge blue">v{{ $log->version }}</span></td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $log->title }}</td>
                        <td><span class="badge {{ $log->app_type === 'office' ? 'purple' : 'green' }}">{{ $log->app_type }}</span></td>
                        <td style="color:#94a3b8;font-size:12px">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:24px">No changelogs yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-shield-alt"></i> Recent Backups</span>
            <a href="{{ route('admin.backup.history') }}" class="card-action">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body no-pad">
            <table class="card-table">
                <thead><tr><th>Backup</th><th>Status</th><th>Files</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($recentBackups as $bk)
                    <tr>
                        <td style="font-weight:500">{{ \Illuminate\Support\Str::limit($bk->folder_name ?? 'Manual', 28) }}</td>
                        <td>
                            @if($bk->status === 'completed')<span class="badge green"><i class="fas fa-check"></i> Done</span>
                            @elseif($bk->status === 'failed')<span class="badge red"><i class="fas fa-times"></i> Failed</span>
                            @elseif($bk->status === 'running')<span class="badge amber"><i class="fas fa-spinner fa-spin"></i> Running</span>
                            @else<span class="badge gray">{{ $bk->status }}</span>@endif
                        </td>
                        <td>{{ number_format($bk->total_files) }}</td>
                        <td style="color:#94a3b8;font-size:12px">{{ $bk->created_at?->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:24px">No backups yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card-grid three">
    <div class="card">
        <div class="card-head"><span class="card-title"><i class="fas fa-bolt"></i> Quick Actions</span></div>
        <div class="card-body">
            <div class="quick-links">
                <a href="{{ route('admin.database.connections.index') }}" class="quick-link">
                    <i style="background:#eff6ff;color:#2563eb" class="fas fa-database"></i>
                    <div class="ql-text"><span>Database</span><span>Manage connections</span></div>
                </a>
                <a href="{{ route('admin.backup.index') }}" class="quick-link">
                    <i style="background:#f0fdf4;color:#16a34a" class="fas fa-shield-alt"></i>
                    <div class="ql-text"><span>Backup</span><span>Run & manage</span></div>
                </a>
                <a href="{{ route('admin.filemanager.index') }}" class="quick-link">
                    <i style="background:#fffbeb;color:#d97706" class="fas fa-folder"></i>
                    <div class="ql-text"><span>File Manager</span><span>Browse files</span></div>
                </a>
                <a href="{{ route('admin.users.index') }}" class="quick-link">
                    <i style="background:#f5f3ff;color:#7c3aed" class="fas fa-users"></i>
                    <div class="ql-text"><span>Users</span><span>Manage admins</span></div>
                </a>
                <a href="{{ route('admin.menus.index') }}" class="quick-link">
                    <i style="background:#ecfeff;color:#0891b2" class="fas fa-sitemap"></i>
                    <div class="ql-text"><span>Menus</span><span>Navigation setup</span></div>
                </a>
                <a href="{{ route('admin.changelog.index') }}" class="quick-link">
                    <i style="background:#fff1f2;color:#e11d48" class="fas fa-history"></i>
                    <div class="ql-text"><span>Changelog</span><span>Version history</span></div>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><span class="card-title"><i class="fas fa-server"></i> Server Info</span></div>
        <div class="card-body">
            <div class="info-list">
                <div class="info-row"><span class="info-label"><i class="fab fa-php"></i> PHP Version</span><span class="info-value">{{ $phpVersion }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fab fa-laravel"></i> Laravel</span><span class="info-value">{{ $laravelVersion }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-globe"></i> Server</span><span class="info-value" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $serverSoftware }}">{{ \Illuminate\Support\Str::limit($serverSoftware, 28) }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-database"></i> Database</span><span class="info-value">{{ $dbName }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-network-wired"></i> DB Host</span><span class="info-value">{{ config('database.connections.mysql.host') }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-users"></i> Admins</span><span class="info-value">{{ $totalAdmins }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-user-tag"></i> Roles</span><span class="info-value">{{ $totalRoles }}</span></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><span class="card-title"><i class="fas fa-heartbeat"></i> System Health</span></div>
        <div class="card-body">
            <div class="info-list">
                <div class="info-row"><span class="info-label"><i class="fas fa-database"></i> Database</span><span class="badge green"><i class="fas fa-check-circle"></i> Connected</span></div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-shield-alt"></i> Last Backup</span>
                    @if($lastBackup)<span class="badge {{ $lastBackup->status === 'completed' ? 'green' : 'amber' }}">{{ $lastBackup->created_at?->diffForHumans() }}</span>
                    @else<span class="badge gray">Never</span>@endif
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-hdd"></i> DB Size</span>
                    <div style="flex:1;max-width:140px;margin-left:auto">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span style="font-size:11px;color:#64748b">{{ $totalDbSize >= 1048576 ? number_format($totalDbSize/1048576,1).' MB' : number_format($totalDbSize/1024,1).' KB' }}</span></div>
                        <div class="progress-bar"><div class="progress-fill blue" style="width:{{ min(100, $totalDbSize/1048576/100*100) }}%"></div></div>
                    </div>
                </div>
                <div class="info-row"><span class="info-label"><i class="fas fa-table"></i> Tables</span><span class="info-value">{{ $totalTables }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-plug"></i> Connections</span><span class="info-value">{{ $savedConnections }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-sitemap"></i> Menu Items</span><span class="info-value">{{ $totalMenus }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-code-branch"></i> Changelog</span><span class="info-value">{{ $totalChangelogs }} entries</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
