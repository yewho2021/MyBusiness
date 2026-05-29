@extends('admin.layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
/* ── Welcome Banner ─────────────────────── */
.welcome-banner{background:#111;border-radius:var(--card-radius,12px);padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;overflow:hidden}
.welcome-banner::after{content:'';position:absolute;right:-40px;top:-40px;width:200px;height:200px;background:radial-gradient(circle,var(--c-secondary,#2563eb) 0%,transparent 70%);opacity:.12;pointer-events:none}
.welcome-left h2{font-size:24px;font-weight:700;color:#fff;margin-bottom:6px}
.welcome-left p{font-size:15px;color:var(--text-faint)}
.welcome-left p strong{color:var(--input-border)}
.welcome-right{display:flex;gap:10px;flex-wrap:wrap;z-index:1}
.welcome-badge{padding:6px 14px;border-radius:8px;font-size:14px;font-weight:600;display:inline-flex;align-items:center;gap:6px}
.welcome-badge.role{background:rgba(255,255,255,.1);color:var(--border-color)}
.welcome-badge.time{background:rgba(255,255,255,.06);color:var(--text-faint);font-weight:400}
.welcome-badge.session{background:rgba(34,197,94,.15);color:#4ade80}

/* ── Stat Cards ─────────────────────────── */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
@media(max-width:1200px){.stats-row{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.stats-row{grid-template-columns:1fr}}
.stat-card{background:var(--card-bg,#fff);border-radius:var(--card-radius,12px);padding:20px 24px;border:1px solid var(--border-light);transition:all .2s;position:relative;overflow:hidden}
.stat-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.06);border-color:var(--border-color)}
.stat-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.stat-icon{width:42px;height:42px;border-radius:var(--card-radius,10px);display:flex;align-items:center;justify-content:center;font-size:20px}
.stat-icon.blue{background:var(--c-secondary-light);color:var(--c-secondary)}
.stat-icon.green{background:var(--c-success-light);color:var(--c-success)}
.stat-icon.amber{background:var(--c-warning-light);color:var(--c-warning)}
.stat-icon.purple{background:var(--c-purple-light,#f5f3ff);color:var(--c-purple,#7c3aed)}
.stat-icon.red{background:var(--c-danger-light);color:var(--c-danger)}
.stat-icon.sky{background:var(--c-info-light);color:var(--c-info)}
.stat-badge{font-size:13px;font-weight:600;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:3px}
.stat-badge.up{background:var(--c-success-light);color:var(--c-success)}
.stat-badge.neutral{background:var(--table-header-bg);color:var(--text-muted)}
.stat-badge.warn{background:var(--c-warning-light);color:var(--c-warning)}
.stat-badge.danger{background:var(--c-danger-light);color:var(--c-danger)}
.stat-value{font-size:28px;font-weight:700;color:var(--text-heading);line-height:1.2;margin-bottom:4px}
.stat-label{font-size:15px;color:var(--text-muted);font-weight:500}

/* Animated counter */
.counter{display:inline-block}

/* ── Card grid ──────────────────────────── */
.card-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
@media(max-width:900px){.card-grid{grid-template-columns:1fr}}
.card-grid.three{grid-template-columns:1fr 1fr 1fr}
@media(max-width:1100px){.card-grid.three{grid-template-columns:1fr 1fr}}
@media(max-width:700px){.card-grid.three{grid-template-columns:1fr}}

.card{background:var(--card-bg,#fff);border-radius:var(--card-radius,12px);border:1px solid var(--border-light);overflow:hidden}
.card:hover{border-color:var(--border-color)}
.card-head{padding:18px 22px;display:flex;justify-content:space-between;align-items:center}
.card-title{font-size:17px;font-weight:600;color:var(--text-heading);display:flex;align-items:center;gap:8px}
.card-title i{color:var(--text-faint);font-size:16px}
.card-subtitle{font-size:14px;color:var(--text-faint);font-weight:400;margin-left:4px}
.card-action{font-size:14px;color:var(--c-secondary);text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:4px}
.card-action:hover{color:var(--c-secondary)}
.card-body{padding:0 22px 18px}
.card-body.no-pad{padding:0}

.card-table{width:100%;border-collapse:collapse}
.card-table th{text-align:left;padding:10px 22px;font-size:13px;font-weight:600;color:var(--text-faint);text-transform:uppercase;letter-spacing:.4px;background:var(--hover-bg);border-top:1px solid var(--border-light);border-bottom:1px solid var(--border-light)}
.card-table td{padding:12px 22px;font-size:15px;color:var(--text-body);border-bottom:1px solid var(--table-header-bg)}
.card-table tbody tr:last-child td{border-bottom:none}
.card-table tbody tr:hover td{background:var(--hover-bg)}

.badge{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;font-size:13px;font-weight:600}
.badge.green{background:var(--c-success-light);color:var(--c-success)}
.badge.red{background:var(--c-danger-light);color:var(--c-danger)}
.badge.blue{background:var(--c-secondary-light);color:var(--c-secondary)}
.badge.amber{background:var(--c-warning-light);color:var(--c-warning)}
.badge.gray{background:var(--hover-bg);color:var(--text-muted)}
.badge.purple{background:var(--c-purple-light,#f5f3ff);color:var(--c-purple,#7c3aed)}

.progress-bar{width:100%;height:6px;background:var(--border-light);border-radius:3px;overflow:hidden}
.progress-fill{height:100%;border-radius:3px;transition:width .6s ease}

.info-list{display:flex;flex-direction:column;gap:0}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--table-header-bg)}
.info-row:last-child{border-bottom:none}
.info-label{font-size:15px;color:var(--text-muted);display:flex;align-items:center;gap:8px}
.info-label i{width:16px;color:var(--text-faint);text-align:center}
.info-value{font-size:15px;color:var(--text-heading);font-weight:600}

/* ── Login sparkline ────────────────────── */
.spark-chart{display:flex;align-items:flex-end;gap:6px;height:60px;padding:8px 0}
.spark-bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px}
.spark-bar{width:100%;border-radius:4px 4px 0 0;min-height:3px;transition:height .4s ease;position:relative}
.spark-bar.ok{background:var(--c-secondary)}
.spark-bar.fail{background:var(--c-danger);opacity:.6;margin-top:2px;border-radius:0 0 4px 4px}
.spark-label{font-size:12px;color:var(--text-faint);font-weight:500}
.spark-count{font-size:12px;color:var(--text-muted);font-weight:600}

/* ── Quick links ────────────────────────── */
.quick-links{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.quick-link{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:8px;border:1px solid var(--border-light);text-decoration:none;color:var(--text-body);font-size:15px;font-weight:500;transition:all .15s}
.quick-link:hover{background:var(--table-header-bg);border-color:var(--border-color)}
.quick-link i{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.quick-link .ql-text{display:flex;flex-direction:column}
.quick-link .ql-text span:first-child{font-weight:600;color:var(--text-heading)}
.quick-link .ql-text span:last-child{font-size:13px;color:var(--text-faint);font-weight:400}

/* ── Control Panel ──────────────────────── */
.cp-section{margin-bottom:24px}
.cp-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;cursor:pointer;user-select:none}
.cp-header h3{font-size:18px;font-weight:700;color:var(--text-heading);display:flex;align-items:center;gap:10px}
.cp-header h3 i{color:var(--text-faint);font-size:16px}
.cp-toggle{font-size:14px;color:var(--text-faint);display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:6px;border:1px solid var(--border-color);background:#fff}
.cp-toggle:hover{background:var(--table-header-bg)}
.cp-body{transition:all .3s ease}
.cp-body.collapsed{display:none}
.cp-group{margin-bottom:18px}
.cp-group-title{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-faint);margin-bottom:10px;padding-left:4px;display:flex;align-items:center;gap:8px}
.cp-group-title::after{content:'';flex:1;height:1px;background:var(--border-light)}
.cp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px}
@media(max-width:640px){.cp-grid{grid-template-columns:repeat(2,1fr)}}
.cp-item{display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 10px;border-radius:var(--card-radius,10px);border:1px solid var(--border-light);background:var(--card-bg,#fff);text-decoration:none;color:var(--text-body);transition:all .15s;text-align:center}
.cp-item:hover{border-color:var(--border-color);box-shadow:0 2px 8px rgba(0,0,0,.04);transform:translateY(-1px)}
.cp-item-icon{width:40px;height:40px;border-radius:var(--card-radius,10px);display:flex;align-items:center;justify-content:center;font-size:18px}
.cp-item-label{font-size:14px;font-weight:600;line-height:1.3}

/* ── Stagger animation ──────────────────── */
.fade-in{opacity:0;transform:translateY(12px);animation:fadeUp .4s ease forwards}
@keyframes fadeUp{to{opacity:1;transform:translateY(0)}}

/* ── Expandable login rows ─────────────── */
.login-row{transition:background .15s}
.login-row:hover td{background:var(--hover-bg)}
.login-row td{vertical-align:middle}
.login-detail td{padding:0 !important}
</style>
@endpush

@section('content')
{{-- ── Welcome Banner ──────────────────── --}}
<div class="welcome-banner fade-in" style="animation-delay:.05s">
    <div class="welcome-left">
        <h2>{{ $greeting }}, {{ $admin->name ?? 'Admin' }}</h2>
        <p>Logged in as <strong>{{ $admin->role->name ?? 'User' }}</strong> &middot; {{ \App\Models\Configuration::get('portal_name', 'Admin Portal') }}</p>
    </div>
    <div class="welcome-right">
        <span class="welcome-badge session"><i class="fas fa-circle" style="font-size:9px"></i> {{ $securityStats['activeSessions'] }} active {{ Str::plural('session', $securityStats['activeSessions']) }}</span>
        <span class="welcome-badge role"><i class="fas fa-shield-alt"></i> {{ $admin->role->name ?? 'User' }}</span>
        <span class="welcome-badge time"><i class="fas fa-clock"></i> {{ now()->setTimezone($serverTimezone)->format('D, d M Y H:i') }}</span>
    </div>
</div>

{{-- ── Row 1: Primary Stats ────────────── --}}
<div class="stats-row">
    <div class="stat-card fade-in" style="animation-delay:.1s">
        <div class="stat-top">
            <div class="stat-icon blue"><i class="fas fa-table"></i></div>
            <span class="stat-badge neutral"><i class="fas fa-database"></i> {{ $dbName }}</span>
        </div>
        <div class="stat-value"><span class="counter" data-target="{{ $totalTables }}">0</span></div>
        <div class="stat-label">Database Tables</div>
    </div>
    <div class="stat-card fade-in" style="animation-delay:.15s">
        <div class="stat-top">
            <div class="stat-icon green"><i class="fas fa-hdd"></i></div>
            <span class="stat-badge up"><i class="fas fa-check-circle"></i> Active</span>
        </div>
        <div class="stat-value">{{ $totalDbSize >= 1048576 ? number_format($totalDbSize/1048576,1).' MB' : number_format($totalDbSize/1024,1).' KB' }}</div>
        <div class="stat-label">Database Size</div>
    </div>
    <div class="stat-card fade-in" style="animation-delay:.2s">
        <div class="stat-top">
            <div class="stat-icon amber"><i class="fas fa-layer-group"></i></div>
        </div>
        <div class="stat-value"><span class="counter" data-target="{{ $totalRows }}">0</span></div>
        <div class="stat-label">Total Rows</div>
    </div>
    <div class="stat-card fade-in" style="animation-delay:.25s">
        <div class="stat-top">
            <div class="stat-icon purple"><i class="fas fa-code-branch"></i></div>
            <span class="stat-badge {{ $patchStats['successRate'] >= 90 ? 'up' : 'warn' }}">{{ $patchStats['successRate'] }}% success</span>
        </div>
        <div class="stat-value"><span class="counter" data-target="{{ $patchStats['totalPatches'] }}">0</span></div>
        <div class="stat-label">Patches Applied</div>
    </div>
</div>

{{-- ── Row 2: Security + Login Trend ───── --}}
<div class="card-grid fade-in" style="animation-delay:.3s">
    <div class="card">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-shield-alt"></i> Security Overview</span>
        </div>
        <div class="card-body">
            <div class="stats-row" style="grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:0">
                <div style="text-align:center;padding:14px 8px;background:var(--hover-bg);border-radius:var(--card-radius,10px)">
                    <div style="font-size:24px;font-weight:700;color:var(--c-success)">{{ $securityStats['activeSessions'] }}</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:2px">Active Now</div>
                </div>
                <div style="text-align:center;padding:14px 8px;background:var(--hover-bg);border-radius:var(--card-radius,10px)">
                    <div style="font-size:24px;font-weight:700;color:{{ $securityStats['failedLast24h'] > 5 ? 'var(--c-danger)' : 'var(--text-heading)' }}">{{ $securityStats['failedLast24h'] }}</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:2px">Failed (24h)</div>
                </div>
                <div style="text-align:center;padding:14px 8px;background:var(--hover-bg);border-radius:var(--card-radius,10px)">
                    <div style="font-size:24px;font-weight:700;color:{{ $securityStats['lockedAccounts'] > 0 ? 'var(--c-danger)' : 'var(--text-heading)' }}">{{ $securityStats['lockedAccounts'] }}</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:2px">Locked</div>
                </div>
                <div style="text-align:center;padding:14px 8px;background:var(--hover-bg);border-radius:var(--card-radius,10px)">
                    <div style="font-size:24px;font-weight:700;color:{{ $securityStats['twofaPct'] >= 100 ? 'var(--c-success)' : 'var(--c-warning)' }}">{{ $securityStats['twofaPct'] }}%</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:2px">2FA Enabled</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-chart-bar"></i> Login Activity <span class="card-subtitle">7 days</span></span>
        </div>
        <div class="card-body">
            <div class="spark-chart">
                @foreach($loginTrend['days'] as $day)
                <div class="spark-bar-wrap">
                    <div class="spark-count">{{ $day['logins'] ?: '' }}</div>
                    <div class="spark-bar ok" style="height:{{ $loginTrend['max'] > 0 ? max(6, $day['logins'] / $loginTrend['max'] * 100) : 6 }}%"></div>
                    @if($day['failed'] > 0)
                    <div class="spark-bar fail" style="height:{{ max(4, $day['failed'] / max(1,$loginTrend['max']) * 40) }}%"></div>
                    @endif
                    <div class="spark-label">{{ $day['label'] }}</div>
                </div>
                @endforeach
            </div>
            <div style="display:flex;gap:16px;justify-content:center;margin-top:8px">
                <span style="font-size:13px;color:var(--text-faint);display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;background:var(--c-secondary);border-radius:2px;display:inline-block"></span> Logins</span>
                <span style="font-size:13px;color:var(--text-faint);display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;background:var(--c-danger);opacity:.6;border-radius:2px;display:inline-block"></span> Failed</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Control Panel ───────────────────── --}}
@if(!empty($controlPanel))
<div class="cp-section fade-in" style="animation-delay:.35s">
    <div class="cp-header" onclick="document.getElementById('cpBody').classList.toggle('collapsed');this.querySelector('.cp-arrow').classList.toggle('fa-chevron-down');this.querySelector('.cp-arrow').classList.toggle('fa-chevron-up')">
        <h3><i class="fas fa-th"></i> Control Panel</h3>
        <span class="cp-toggle"><span>Toggle</span> <i class="fas fa-chevron-up cp-arrow"></i></span>
    </div>
    <div id="cpBody" class="cp-body">
        @foreach($controlPanel as $group)
            <div class="cp-group">
                <div class="cp-group-title">{{ $group['group_title'] }}</div>
                <div class="cp-grid">
                    @foreach($group['items'] as $item)
                        <a href="{{ $item['url'] }}" class="cp-item">
                            <div class="cp-item-icon" style="background:{{ $group['color']['bg'] }};color:{{ $group['color']['fg'] }}">
                                <i class="{{ $item['icon'] }}"></i>
                            </div>
                            <span class="cp-item-label">{{ $item['title'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Row 3: Changelogs + Backups ─────── --}}
<div class="card-grid fade-in" style="animation-delay:.4s">
    <div class="card">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-code-branch"></i> Recent Changes <span class="card-subtitle">{{ $totalChangelogs }} total</span></span>
            <a href="{{ route('admin.changelog.index') }}" class="card-action">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body no-pad">
            <table class="card-table">
                <thead><tr><th>Version</th><th>Title</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($recentChangelogs as $log)
                    <tr>
                        <td><span class="badge blue">v{{ $log->version }}</span></td>
                        <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $log->title }}</td>
                        <td style="color:var(--text-faint);font-size:14px">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:var(--text-faint);padding:24px">No changelogs yet</td></tr>
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
                <thead><tr><th>Backup</th><th>Status</th><th>Size</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($recentBackups as $bk)
                    <tr>
                        <td style="font-weight:500">{{ Str::limit($bk->folder_name ?? 'Manual', 22) }}</td>
                        <td>
                            @if($bk->status === 'completed')<span class="badge green"><i class="fas fa-check"></i> Done</span>
                            @elseif($bk->status === 'failed')<span class="badge red"><i class="fas fa-times"></i> Failed</span>
                            @elseif($bk->status === 'running')<span class="badge amber"><i class="fas fa-spinner fa-spin"></i> Running</span>
                            @else<span class="badge gray">{{ $bk->status }}</span>@endif
                        </td>
                        <td style="font-size:14px;color:var(--text-muted)">{{ $bk->zip_size ? number_format($bk->zip_size/1048576,1).'M' : '—' }}</td>
                        <td style="color:var(--text-faint);font-size:14px">{{ $bk->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--text-faint);padding:24px">No backups yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Row 4: Quick Actions + Server Info + System Health ── --}}
<div class="card-grid three fade-in" style="animation-delay:.45s">
    <div class="card">
        <div class="card-head"><span class="card-title"><i class="fas fa-bolt"></i> Quick Actions</span></div>
        <div class="card-body">
            <div class="quick-links">
                <a href="{{ route('admin.database.connections.index') }}" class="quick-link">
                    <i style="background:var(--c-secondary-light);color:var(--c-secondary)" class="fas fa-database"></i>
                    <div class="ql-text"><span>Database</span><span>Manage connections</span></div>
                </a>
                <a href="{{ route('admin.backup.index') }}" class="quick-link">
                    <i style="background:var(--c-success-light);color:var(--c-success)" class="fas fa-shield-alt"></i>
                    <div class="ql-text"><span>Backup</span><span>Run & manage</span></div>
                </a>
                <a href="{{ route('admin.filemanager.index') }}" class="quick-link">
                    <i style="background:var(--c-warning-light);color:var(--c-warning)" class="fas fa-folder"></i>
                    <div class="ql-text"><span>File Manager</span><span>Browse files</span></div>
                </a>
                <a href="{{ route('admin.users.index') }}" class="quick-link">
                    <i style="background:var(--c-purple-light,#f5f3ff);color:var(--c-purple,#7c3aed)" class="fas fa-users"></i>
                    <div class="ql-text"><span>Users</span><span>Manage admins</span></div>
                </a>
                <a href="{{ route('admin.system-patch.index') }}" class="quick-link">
                    <i style="background:var(--c-info-light);color:var(--c-info)" class="fas fa-rocket"></i>
                    <div class="ql-text"><span>System Patch</span><span>Apply updates</span></div>
                </a>
                <a href="{{ route('admin.settings.configuration') }}" class="quick-link">
                    <i style="background:var(--c-danger-light);color:var(--c-danger)" class="fas fa-cogs"></i>
                    <div class="ql-text"><span>Configuration</span><span>Portal settings</span></div>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><span class="card-title"><i class="fas fa-server"></i> Server Info</span></div>
        <div class="card-body">
            <div class="info-list">
                <div class="info-row"><span class="info-label"><i class="fab fa-php"></i> PHP</span><span class="info-value">{{ $phpVersion }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fab fa-laravel"></i> Laravel</span><span class="info-value">{{ $laravelVersion }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-globe"></i> Server</span><span class="info-value" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $serverSoftware }}">{{ Str::limit($serverSoftware, 24) }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-database"></i> Database</span><span class="info-value">{{ $dbName }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-clock"></i> Timezone</span><span class="info-value">{{ $serverTimezone }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-users"></i> Admins</span><span class="info-value">{{ $totalAdmins }}</span></div>
                @if($patchStats['latestVersion'])
                <div class="info-row"><span class="info-label"><i class="fas fa-tag"></i> Version</span><span class="info-value" style="color:var(--c-secondary)">v{{ $patchStats['latestVersion']->version_code }}</span></div>
                @endif
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
                    <span class="info-label"><i class="fas fa-hdd"></i> Disk</span>
                    @php
                        $pct = $diskStats['percent'];
                        $barColor = $pct > 90 ? 'var(--c-danger)' : ($pct > 75 ? 'var(--c-warning)' : 'var(--c-success)');
                        $fmt = function($b) { return $b >= 1073741824 ? number_format($b/1073741824,1).' GB' : ($b >= 1048576 ? number_format($b/1048576,1).' MB' : number_format($b/1024,1).' KB'); };
                    @endphp
                    <div style="flex:1;max-width:140px;margin-left:auto">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span style="font-size:13px;font-weight:600;color:var(--text-heading)">{{ $pct }}%</span><span style="font-size:12px;color:var(--text-faint)">{{ $fmt($diskStats['free']) }} free</span></div>
                        <div class="progress-bar"><div class="progress-fill" style="width:{{ min(100,$pct) }}%;background:{{ $barColor }}"></div></div>
                    </div>
                </div>
                <div class="info-row"><span class="info-label"><i class="fas fa-table"></i> Tables</span><span class="info-value">{{ $totalTables }}</span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-code-branch"></i> Patches</span><span class="info-value">{{ $patchStats['totalPatches'] }} <span style="font-weight:400;color:var(--text-faint);font-size:13px">({{ $patchStats['totalRollbacks'] }} rollbacks)</span></span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-folder"></i> Storage</span><span class="info-value">{{ $fmt($diskStats['storage_size']) }}</span></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 5: Recent Logins (expandable) ──── --}}
<div class="fade-in" style="animation-delay:.5s;margin-bottom:24px">
    <div class="card">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-sign-in-alt"></i> Recent Logins</span>
            <a href="{{ route('admin.admin-log.index') }}" class="card-action">View all <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body no-pad">
            <table class="card-table" style="font-size:16px">
                <thead><tr><th>Admin</th><th>Status</th><th>IP</th><th>Device</th><th>When</th><th style="width:40px"></th></tr></thead>
                <tbody>
                @forelse($recentLogins as $idx => $login)
                    <tr class="login-row" onclick="toggleLoginDetail({{ $idx }})" style="cursor:pointer">
                        <td style="font-weight:600;font-size:16px">{{ $login->admin_name ?? '—' }}</td>
                        <td>
                            @if($login->status === 'active')<span class="badge green" style="font-size:14px"><i class="fas fa-circle" style="font-size:9px"></i> Active</span>
                            @elseif(str_starts_with($login->status ?? '', 'failed'))<span class="badge red" style="font-size:14px"><i class="fas fa-times-circle"></i> Failed</span>
                            @else<span class="badge gray" style="font-size:14px">{{ ucfirst($login->status ?? '—') }}</span>@endif
                        </td>
                        <td style="font-size:15px;color:var(--text-muted);font-family:var(--font-mono)">{{ $login->ip_address ?? '—' }}</td>
                        <td style="font-size:15px;color:var(--text-muted)">
                            @if($login->device_type === 'mobile')<i class="fas fa-mobile-alt" style="color:var(--c-info);margin-right:4px"></i>
                            @elseif($login->device_type === 'tablet')<i class="fas fa-tablet-alt" style="color:var(--c-warning);margin-right:4px"></i>
                            @else<i class="fas fa-desktop" style="color:var(--text-faint);margin-right:4px"></i>@endif
                            {{ $login->browser ?? '—' }}
                        </td>
                        <td style="font-size:15px;color:var(--text-faint)">{{ $login->login_at ? Carbon\Carbon::parse($login->login_at)->diffForHumans() : '—' }}</td>
                        <td style="text-align:center"><i class="fas fa-chevron-down login-arrow" id="arrow-{{ $idx }}" style="font-size:13px;color:var(--text-faint);transition:transform .2s"></i></td>
                    </tr>
                    <tr class="login-detail" id="detail-{{ $idx }}" style="display:none">
                        <td colspan="6" style="padding:0;background:var(--hover-bg);border-bottom:2px solid var(--border-light)">
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;padding:16px 22px">
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:var(--text-faint);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">User</div>
                                    <div style="font-size:16px;font-weight:600;color:var(--text-heading)">{{ $login->admin_name ?? '—' }}</div>
                                    <div style="font-size:14px;color:var(--text-muted)">{{ $login->admin_username ?? '' }} &middot; {{ $login->role_name ?? '' }}</div>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:var(--text-faint);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Location</div>
                                    <div style="font-size:16px;color:var(--text-heading)">{{ $login->ip_address ?? '—' }}</div>
                                    <div style="font-size:14px;color:var(--text-muted)">{{ implode(', ', array_filter([$login->ip_city, $login->ip_country])) ?: 'Unknown' }}{{ $login->ip_isp ? ' · '.$login->ip_isp : '' }}</div>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:var(--text-faint);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Device</div>
                                    <div style="font-size:16px;color:var(--text-heading)">{{ $login->browser ?? '—' }}</div>
                                    <div style="font-size:14px;color:var(--text-muted)">{{ $login->platform ?? '' }} &middot; {{ ucfirst($login->device_type ?? 'unknown') }}</div>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:var(--text-faint);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Session</div>
                                    <div style="font-size:16px;color:var(--text-heading)">{{ $login->login_at ? Carbon\Carbon::parse($login->login_at)->format('d M Y H:i:s') : '—' }}</div>
                                    <div style="font-size:14px;color:var(--text-muted)">
                                        @if($login->logout_at)
                                            Ended {{ Carbon\Carbon::parse($login->logout_at)->format('H:i:s') }}
                                            @if($login->duration_seconds) · {{ floor($login->duration_seconds/60) }}m {{ $login->duration_seconds%60 }}s @endif
                                            @if($login->logout_type) · {{ $login->logout_type }} @endif
                                        @elseif($login->status === 'active')
                                            Still active
                                        @elseif($login->fail_reason)
                                            {{ $login->fail_reason }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--text-faint);padding:24px;font-size:16px">No login records</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Row 6: Activity Timeline ────────── --}}
@if(!empty($recentActivities) && count($recentActivities) > 0)
<div class="card fade-in" style="animation-delay:.55s;margin-bottom:24px">
    <div class="card-head">
        <span class="card-title"><i class="fas fa-stream"></i> Activity Timeline</span>
        <a href="{{ route('admin.activity-log.index') }}" class="card-action">View all <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="card-body" style="padding-top:4px">
        <div style="position:relative;padding-left:20px">
            <div style="position:absolute;left:6px;top:8px;bottom:8px;width:2px;background:var(--border-light);border-radius:1px"></div>
            @foreach($recentActivities as $act)
            <div style="position:relative;padding:8px 0 8px 16px;display:flex;align-items:flex-start;gap:12px">
                <div style="position:absolute;left:-3px;top:14px;width:10px;height:10px;border-radius:50%;border:2px solid {{ $act->event === 'deleted' ? 'var(--c-danger)' : ($act->event === 'created' ? 'var(--c-success)' : 'var(--c-secondary)') }};background:var(--card-bg,#fff);z-index:1"></div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:15px;color:var(--text-body)">
                        <strong>{{ ucfirst($act->event ?? 'action') }}</strong>
                        @if($act->subject_type)<span style="color:var(--text-muted)">{{ class_basename($act->subject_type) }}</span>@endif
                        @if($act->description)<span style="color:var(--text-faint)">— {{ Str::limit($act->description, 60) }}</span>@endif
                    </div>
                    <div style="font-size:13px;color:var(--text-faint);margin-top:2px">{{ $act->created_at?->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// ── Animated counters ──
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.counter').forEach(function(el) {
        var target = parseInt(el.dataset.target) || 0;
        if (target === 0) { el.textContent = '0'; return; }
        var duration = Math.min(1200, Math.max(400, target * 2));
        var start = performance.now();
        function tick(now) {
            var pct = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - pct, 3);
            el.textContent = Math.floor(eased * target).toLocaleString();
            if (pct < 1) requestAnimationFrame(tick);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(tick);
    });
});

// ── Login detail expand/collapse ──
function toggleLoginDetail(idx) {
    var detail = document.getElementById('detail-' + idx);
    var arrow = document.getElementById('arrow-' + idx);
    var open = detail.style.display !== 'none';
    // Close all others first
    document.querySelectorAll('.login-detail').forEach(function(el) { el.style.display = 'none'; });
    document.querySelectorAll('.login-arrow').forEach(function(el) { el.style.transform = ''; });
    if (!open) {
        detail.style.display = 'table-row';
        arrow.style.transform = 'rotate(180deg)';
    }
}
</script>
@endpush
