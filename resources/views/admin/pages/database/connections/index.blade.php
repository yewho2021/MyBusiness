@extends('admin.layouts.app')
@section('title', 'Database Connections')

@push('styles')
<style>
/* ═══════════════════════════════════════════════
   DATABASE CONNECTIONS — Enhanced UI
   ═══════════════════════════════════════════════ */
.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.page-title { font-size:22px; font-weight:700; color:var(--text-heading); display:flex; align-items:center; gap:10px; }
.page-title-icon { width:38px; height:38px; border-radius:10px; background:linear-gradient(135deg,var(--c-secondary),var(--c-secondary)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:17px; box-shadow:0 4px 10px rgba(37,99,235,.25); }
.page-sub { font-size:13px; color:var(--text-muted); margin-top:4px; }

.btn-add { background:linear-gradient(135deg,var(--c-danger),var(--c-primary-hover)); color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all .2s; box-shadow:0 2px 8px rgba(220,38,38,.25); }
.btn-add:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(220,38,38,.3); }

/* ── Stats ── */
.stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px; }
@media(max-width:768px) { .stats-row { grid-template-columns:1fr; } }
.stat-card { background:#fff; border-radius:12px; padding:20px 24px; border:1px solid var(--border-light); display:flex; align-items:center; gap:16px; transition:all .2s; }
.stat-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.06); border-color:var(--border-color); }
.stat-icon { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.stat-icon.blue   { background:var(--c-secondary-light); color:var(--c-secondary); }
.stat-icon.green  { background:var(--c-success-light); color:var(--c-success); }
.stat-icon.amber  { background:var(--c-warning-light); color:var(--c-warning); }
.stat-val { font-size:24px; font-weight:700; color:var(--text-heading); line-height:1.2; margin-bottom:2px; }
.stat-label { font-size:13px; color:var(--text-muted); font-weight:500; }

/* ── Main Card ── */
.card { background:#fff; border-radius:12px; border:1px solid var(--border-light); margin-bottom:20px; overflow:hidden; }
.card-head { padding:18px 22px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-light); }
.card-title { font-size:15px; font-weight:600; color:var(--text-heading); display:flex; align-items:center; gap:8px; }
.card-title i { color:var(--text-faint); font-size:14px; }
.btn-switch-default { background:var(--c-info-light); color:var(--c-info); border:1px solid var(--c-info-border); padding:7px 14px; border-radius:7px; font-size:13px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:6px; text-decoration:none; transition:all .15s; }
.btn-switch-default:hover { background:var(--c-info-light); }

/* ── Connection Grid ── */
.conn-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(380px,1fr)); gap:20px; padding:22px; }
@media(max-width:480px) { .conn-grid { grid-template-columns:1fr; padding:16px; } }

/* ── Connection Card ── */
.conn-card { background:#fff; border:1px solid var(--border-color); border-radius:14px; overflow:hidden; transition:all .25s; position:relative; }
.conn-card:hover { border-color:var(--hover-border); box-shadow:0 8px 24px rgba(0,0,0,.06); transform:translateY(-2px); }
.conn-card.inactive { opacity:.55; }
.conn-card.active-session { border-color:var(--c-secondary); box-shadow:0 0 0 1px var(--c-secondary), 0 8px 24px rgba(37,99,235,.1); }
.conn-card.is-env { border-color:var(--c-success); }

/* Card accent stripe */
.conn-accent { height:4px; width:100%; }
.conn-accent.env    { background:linear-gradient(90deg,var(--c-success),var(--c-success)); }
.conn-accent.active { background:linear-gradient(90deg,var(--c-secondary),var(--c-secondary)); }
.conn-accent.saved  { background:linear-gradient(90deg,var(--text-faint),var(--text-muted)); }
.conn-accent.off    { background:var(--border-color); }

.conn-body { padding:20px; }

/* Header row */
.conn-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; gap:12px; }
.conn-identity { display:flex; align-items:center; gap:12px; flex:1; min-width:0; }

.conn-db-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.conn-db-icon.env    { background:linear-gradient(135deg,var(--c-success-light),var(--c-success-light)); color:var(--c-success); }
.conn-db-icon.blue   { background:linear-gradient(135deg,var(--c-secondary-light),var(--c-secondary-light)); color:var(--c-secondary); }
.conn-db-icon.gray   { background:var(--hover-bg); color:var(--text-faint); }

.conn-db-icon svg { width:24px; height:24px; }

.conn-name { font-size:16px; font-weight:700; color:var(--text-heading); line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.conn-name-sub { font-size:12px; color:var(--text-faint); font-weight:400; margin-top:1px; }

/* Badges */
.conn-badges { display:flex; gap:6px; flex-shrink:0; }
.conn-badge { font-size:11px; padding:3px 10px; border-radius:20px; font-weight:600; letter-spacing:.3px; }
.conn-badge.env-badge     { background:var(--c-success-light); color:var(--c-success); border:1px solid var(--c-success-border); }
.conn-badge.active-badge  { background:var(--c-secondary-light); color:var(--c-secondary); border:1px solid var(--c-secondary-border); }
.conn-badge.session-badge { background:var(--c-success-light); color:var(--c-success); border:1px solid var(--c-success-border); }
.conn-badge.inactive-badge{ background:var(--c-danger-light); color:var(--c-danger); border:1px solid var(--c-danger-border); }

/* Pulse for active session */
.pulse-dot { width:8px; height:8px; border-radius:50%; background:var(--c-success); display:inline-block; margin-right:4px; animation:pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }

/* Info grid */
.conn-info { display:grid; grid-template-columns:1fr 1fr; gap:0; margin-bottom:16px; background:var(--table-header-bg); border-radius:10px; overflow:hidden; border:1px solid var(--border-light); }
.conn-info-item { padding:10px 14px; display:flex; align-items:center; gap:8px; border-bottom:1px solid var(--border-light); }
.conn-info-item:nth-last-child(-n+2) { border-bottom:none; }
.conn-info-item.full { grid-column:1/-1; }
.conn-info-icon { width:16px; text-align:center; color:var(--text-faint); font-size:12px; flex-shrink:0; }
.conn-info-label { font-size:11px; color:var(--text-faint); text-transform:uppercase; letter-spacing:.4px; font-weight:600; }
.conn-info-val { font-size:13px; color:var(--text-heading); font-weight:600; font-family:'JetBrains Mono',monospace; word-break:break-all; }

/* Description */
.conn-desc { font-size:13px; color:var(--text-muted); padding:10px 14px; background:var(--hover-bg); border-radius:8px; border:1px solid var(--border-light); margin-bottom:16px; line-height:1.5; display:flex; align-items:flex-start; gap:8px; }
.conn-desc i { color:var(--hover-border); margin-top:2px; flex-shrink:0; }

/* Actions */
.conn-actions { display:flex; gap:6px; flex-wrap:wrap; }
.conn-btn { padding:7px 14px; font-size:13px; font-weight:600; border-radius:8px; cursor:pointer; border:1px solid transparent; display:inline-flex; align-items:center; gap:6px; transition:all .15s; text-decoration:none; }
.conn-btn.browse   { background:linear-gradient(135deg,var(--c-danger),var(--c-primary-hover)); color:#fff; box-shadow:0 2px 6px rgba(220,38,38,.2); }
.conn-btn.browse:hover { box-shadow:0 4px 10px rgba(220,38,38,.3); transform:translateY(-1px); }
.conn-btn.edit     { background:#fff; color:var(--c-secondary); border-color:var(--c-secondary-border); }
.conn-btn.edit:hover { background:var(--c-secondary-light); }
.conn-btn.toggle   { background:#fff; color:var(--text-muted); border-color:var(--border-color); }
.conn-btn.toggle:hover { background:var(--table-header-bg); }
.conn-btn.delete   { background:#fff; color:var(--c-danger); border-color:var(--c-danger-border); }
.conn-btn.delete:hover { background:var(--c-danger-light); }

/* Footer timestamp */
.conn-footer { padding:12px 20px; background:var(--hover-bg); border-top:1px solid var(--border-light); display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-faint); }
.conn-footer i { margin-right:4px; }

/* ── Empty State ── */
.empty-state { text-align:center; padding:70px 30px; }
.empty-state-icon { width:80px; height:80px; border-radius:20px; background:linear-gradient(135deg,var(--c-secondary-light),var(--c-secondary-light)); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
.empty-state-icon i { font-size:36px; color:var(--c-secondary); }
.empty-state h3 { font-size:18px; font-weight:600; color:var(--text-heading); margin-bottom:8px; }
.empty-state p { font-size:14px; color:var(--text-muted); margin-bottom:20px; max-width:400px; margin-left:auto; margin-right:auto; }

/* ── Alerts ── */
.alert { padding:14px 18px; border-radius:10px; margin-bottom:18px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px; }
.alert-success { background:var(--c-success-light); color:var(--c-success); border:1px solid var(--c-success-border); }
.alert-danger  { background:var(--c-danger-light); color:var(--c-primary-hover); border:1px solid var(--c-danger-border); }
.alert-info    { background:var(--c-secondary-light); color:var(--c-secondary); border:1px solid var(--c-secondary-border); }

/* ── Modal ── */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9998; display:none; justify-content:center; align-items:center; backdrop-filter:blur(3px); }
.modal-overlay.show { display:flex; }
.modal { background:#fff; border-radius:16px; width:100%; max-width:560px; max-height:90vh; overflow:auto; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.modal-head { padding:20px 24px; border-bottom:1px solid var(--border-light); display:flex; justify-content:space-between; align-items:center; }
.modal-title { font-size:17px; font-weight:700; color:var(--text-heading); display:flex; align-items:center; gap:10px; }
.modal-title i { color:var(--c-secondary); }
.modal-close { background:none; border:none; font-size:20px; color:var(--text-faint); cursor:pointer; padding:4px; width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.modal-close:hover { color:var(--text-body); background:var(--border-light); }
.modal-body { padding:24px; }
.modal-foot { padding:16px 24px; border-top:1px solid var(--border-light); display:flex; justify-content:flex-end; gap:8px; align-items:center; }

.form-group { margin-bottom:18px; }
.form-label { display:block; font-size:13px; font-weight:600; color:var(--text-body); margin-bottom:6px; }
.form-input { width:100%; padding:10px 14px; border:1px solid var(--input-border); border-radius:8px; font-size:14px; transition:all .2s; outline:none; }
.form-input:focus { border-color:var(--c-secondary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-hint { font-size:12px; color:var(--text-faint); margin-top:4px; }

.btn-cancel { background:var(--border-light); color:var(--text-secondary); border:none; padding:10px 18px; border-radius:8px; font-weight:500; font-size:14px; cursor:pointer; transition:all .15s; }
.btn-cancel:hover { background:var(--border-color); }
.btn-save { background:linear-gradient(135deg,var(--c-danger),var(--c-primary-hover)); color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all .15s; }
.btn-save:hover { background:var(--c-primary-hover); }
.btn-test { background:var(--c-success-light); color:var(--c-success); border:1px solid var(--c-success-border); padding:10px 18px; border-radius:8px; font-weight:500; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all .15s; }
.btn-test:hover { background:var(--c-success-light); }

.test-result { margin-top:12px; padding:10px 14px; border-radius:8px; font-size:14px; display:none; }
.test-result.success { display:block; background:var(--c-success-light); border:1px solid var(--c-success-border); color:var(--c-success); }
.test-result.error   { display:block; background:var(--c-danger-light); border:1px solid var(--c-danger-border); color:var(--c-danger); }

/* ── Toast ── */
.toast { position:fixed; bottom:24px; right:24px; padding:12px 20px; border-radius:10px; color:#fff; font-size:14px; z-index:10000; display:flex; align-items:center; gap:8px; box-shadow:0 8px 24px rgba(0,0,0,.15); animation:slideIn .3s ease; }
@keyframes slideIn { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <div class="page-title-icon"><i class="fas fa-database"></i></div>
            Database Connections
        </h1>
        <p class="page-sub">Manage and switch between multiple database connections</p>
    </div>
    <button class="btn-add" onclick="openModal()"><i class="fas fa-plus"></i> Add Connection</button>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

{{-- Active session banner --}}
@if(session('db_connection_id'))
    @php $activeConn = $connections->firstWhere('id', session('db_connection_id')); @endphp
    @if($activeConn)
    <div class="alert alert-info">
        <i class="fas fa-plug"></i>
        Currently browsing <strong>{{ $activeConn->name }}</strong> ({{ $activeConn->dbname }}@{{ $activeConn->dbhost }}).
        <a href="{{ route('admin.database.connections.clear') }}" style="color:var(--c-secondary);font-weight:600;text-decoration:underline;margin-left:4px">Switch back to default</a>
    </div>
    @endif
@endif

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-database"></i></div>
        <div>
            <div class="stat-val">{{ $connections->count() + 1 }}</div>
            <div class="stat-label">Total Connections</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-val">{{ $connections->where('is_active', true)->count() + 1 }}</div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-server"></i></div>
        <div>
            @php
                $hosts = $connections->pluck('dbhost')->push(config('database.connections.mysql.host'))->unique();
            @endphp
            <div class="stat-val">{{ $hosts->count() }}</div>
            <div class="stat-label">Unique Hosts</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title"><i class="fas fa-layer-group"></i> All Connections</span>
        @if(session('db_connection_id'))
            <a href="{{ route('admin.database.connections.clear') }}" class="btn-switch-default">
                <i class="fas fa-undo"></i> Switch to Default DB
            </a>
        @endif
    </div>

    <div class="conn-grid">
            {{-- ──────────── Default .env Connection ──────────── --}}
            <div class="conn-card is-env">
                <div class="conn-accent env"></div>
                <div class="conn-body">
                    <div class="conn-header">
                        <div class="conn-identity">
                            <div class="conn-db-icon env">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                                </svg>
                            </div>
                            <div>
                                <div class="conn-name">Default Connection</div>
                                <div class="conn-name-sub">From .env configuration</div>
                            </div>
                        </div>
                        <div class="conn-badges">
                            <span class="conn-badge env-badge">ENV</span>
                        </div>
                    </div>

                    <div class="conn-info">
                        <div class="conn-info-item">
                            <i class="fas fa-server conn-info-icon"></i>
                            <div>
                                <div class="conn-info-label">Host</div>
                                <div class="conn-info-val">{{ config('database.connections.mysql.host') }}:{{ config('database.connections.mysql.port', '3306') }}</div>
                            </div>
                        </div>
                        <div class="conn-info-item">
                            <i class="fas fa-database conn-info-icon"></i>
                            <div>
                                <div class="conn-info-label">Database</div>
                                <div class="conn-info-val">{{ config('database.connections.mysql.database') }}</div>
                            </div>
                        </div>
                        <div class="conn-info-item full">
                            <i class="fas fa-user conn-info-icon"></i>
                            <div>
                                <div class="conn-info-label">Username</div>
                                <div class="conn-info-val">{{ config('database.connections.mysql.username') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="conn-actions">
                        @if(session('db_connection_id'))
                            <a href="{{ route('admin.database.connections.clear') }}" class="conn-btn browse"><i class="fas fa-undo"></i> Switch Back</a>
                        @else
                            <a href="{{ route('admin.database.index') }}" class="conn-btn browse"><i class="fas fa-terminal"></i> Browse</a>
                        @endif
                    </div>
                </div>
                <div class="conn-footer">
                    <span><i class="fas fa-shield-alt"></i> Primary connection</span>
                    <span><i class="fas fa-lock"></i> Read from .env</span>
                </div>
            </div>

            {{-- ──────────── Saved Connections ──────────── --}}
            @foreach($connections as $conn)
            @php $isSession = session('db_connection_id') == $conn->id; @endphp
            <div class="conn-card {{ !$conn->is_active ? 'inactive' : '' }} {{ $isSession ? 'active-session' : '' }}" id="conn-{{ $conn->id }}">
                <div class="conn-accent {{ $isSession ? 'active' : ($conn->is_active ? 'saved' : 'off') }}"></div>
                <div class="conn-body">
                    <div class="conn-header">
                        <div class="conn-identity">
                            <div class="conn-db-icon {{ $isSession ? 'blue' : ($conn->is_active ? 'blue' : 'gray') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                                </svg>
                            </div>
                            <div style="min-width:0">
                                <div class="conn-name">{{ $conn->name }}</div>
                                <div class="conn-name-sub">{{ $conn->dbname }}</div>
                            </div>
                        </div>
                        <div class="conn-badges">
                            @if($isSession)
                                <span class="conn-badge session-badge"><span class="pulse-dot"></span>BROWSING</span>
                            @endif
                            <span class="conn-badge {{ $conn->is_active ? 'active-badge' : 'inactive-badge' }}">
                                {{ $conn->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                    </div>

                    <div class="conn-info">
                        <div class="conn-info-item">
                            <i class="fas fa-server conn-info-icon"></i>
                            <div>
                                <div class="conn-info-label">Host</div>
                                <div class="conn-info-val">{{ $conn->dbhost }}:{{ $conn->dbport }}</div>
                            </div>
                        </div>
                        <div class="conn-info-item">
                            <i class="fas fa-database conn-info-icon"></i>
                            <div>
                                <div class="conn-info-label">Database</div>
                                <div class="conn-info-val">{{ $conn->dbname }}</div>
                            </div>
                        </div>
                        <div class="conn-info-item full">
                            <i class="fas fa-user conn-info-icon"></i>
                            <div>
                                <div class="conn-info-label">Username</div>
                                <div class="conn-info-val">{{ $conn->dbusername }}</div>
                            </div>
                        </div>
                    </div>

                    @if($conn->description)
                        <div class="conn-desc"><i class="fas fa-info-circle"></i> {{ $conn->description }}</div>
                    @endif

                    <div class="conn-actions">
                        @if($conn->is_active)
                            <a href="{{ route('admin.database.connections.browse', $conn->id) }}" class="conn-btn browse"><i class="fas fa-terminal"></i> Browse</a>
                        @endif
                        <button class="conn-btn edit" onclick="openEditModal({{ $conn->id }}, {{ json_encode($conn->only(['name','dbhost','dbport','dbname','dbusername','description'])) }})"><i class="fas fa-pen"></i> Edit</button>
                        <button class="conn-btn toggle" onclick="toggleConn({{ $conn->id }})">
                            <i class="fas fa-{{ $conn->is_active ? 'pause' : 'play' }}"></i> {{ $conn->is_active ? 'Disable' : 'Enable' }}
                        </button>
                        <button class="conn-btn delete" onclick="deleteConn({{ $conn->id }}, '{{ addslashes($conn->name) }}')"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="conn-footer">
                    <span>
                        @if($isSession)
                            <i class="fas fa-plug" style="color:var(--c-success)"></i> Currently connected
                        @elseif($conn->is_active)
                            <i class="fas fa-check-circle" style="color:var(--c-success)"></i> Ready to connect
                        @else
                            <i class="fas fa-ban" style="color:var(--text-faint)"></i> Disabled
                        @endif
                    </span>
                    @if($conn->last_connected_at)
                        <span><i class="fas fa-clock"></i> Last browsed {{ $conn->last_connected_at->diffForHumans() }}</span>
                    @else
                        <span><i class="fas fa-clock"></i> Never browsed</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @if($connections->isEmpty())
        <div style="text-align:center;padding:16px 22px 22px;color:var(--text-muted);font-size:13px;">
            <i class="fas fa-info-circle" style="margin-right:4px"></i> Add external connections to switch between multiple databases.
        </div>
        @endif
</div>

{{-- ──────────── Add/Edit Modal ──────────── --}}
<div class="modal-overlay" id="connModal">
    <div class="modal">
        <div class="modal-head">
            <span class="modal-title" id="modalTitle"><i class="fas fa-database"></i> Add Database Connection</span>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="connForm" onsubmit="saveConnection(event)">
            @csrf
            <input type="hidden" id="connId" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Connection Name *</label>
                    <input type="text" class="form-input" id="fName" name="name" placeholder="e.g. Production DB, Staging, Client XYZ" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Host *</label>
                        <input type="text" class="form-input" id="fHost" name="dbhost" placeholder="localhost or IP" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Port</label>
                        <input type="text" class="form-input" id="fPort" name="dbport" placeholder="3306" value="3306">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Database Name *</label>
                    <input type="text" class="form-input" id="fDbname" name="dbname" placeholder="database_name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-input" id="fUser" name="dbusername" placeholder="db_user" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-input" id="fPass" name="dbpassword" placeholder="••••••••">
                        <div class="form-hint" id="passHint" style="display:none">Leave blank to keep current password</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-input" id="fDesc" name="description" placeholder="Brief note about this connection (optional)">
                </div>
                <div id="testResult" class="test-result"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-test" onclick="testConnection()"><i class="fas fa-plug"></i> Test</button>
                <div style="flex:1"></div>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-save" id="btnSave"><i class="fas fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-database"></i> Add Database Connection';
    document.getElementById('connId').value = '';
    document.getElementById('connForm').reset();
    document.getElementById('fPort').value = '3306';
    document.getElementById('fPass').required = true;
    document.getElementById('passHint').style.display = 'none';
    document.getElementById('testResult').className = 'test-result';
    document.getElementById('testResult').textContent = '';
    document.getElementById('connModal').classList.add('show');
}

function openEditModal(id, data) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-pen"></i> Edit Connection';
    document.getElementById('connId').value = id;
    document.getElementById('fName').value = data.name || '';
    document.getElementById('fHost').value = data.dbhost || '';
    document.getElementById('fPort').value = data.dbport || '3306';
    document.getElementById('fDbname').value = data.dbname || '';
    document.getElementById('fUser').value = data.dbusername || '';
    document.getElementById('fPass').value = '';
    document.getElementById('fPass').required = false;
    document.getElementById('passHint').style.display = 'block';
    document.getElementById('fDesc').value = data.description || '';
    document.getElementById('testResult').className = 'test-result';
    document.getElementById('testResult').textContent = '';
    document.getElementById('connModal').classList.add('show');
}

function closeModal() {
    document.getElementById('connModal').classList.remove('show');
}

async function saveConnection(e) {
    e.preventDefault();
    const id = document.getElementById('connId').value;
    const form = document.getElementById('connForm');
    const data = Object.fromEntries(new FormData(form));
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    const url = id
        ? "{{ url('database/connections') }}/" + id
        : "{{ route('admin.database.connections.store') }}";
    if (id) data._method = 'PUT';

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify(data)
        });
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON (status ' + res.status + '). Check Laravel logs.');
        }
        const result = await res.json();
        if (result.success) {
            location.reload();
        } else {
            if (result.errors) {
                toast(Object.values(result.errors).flat().join(', '), 'error');
            } else {
                toast(result.message || 'Save failed', 'error');
            }
        }
    } catch(err) {
        toast('Error: ' + err.message, 'error');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save';
    }
}

async function testConnection() {
    const el = document.getElementById('testResult');
    el.className = 'test-result'; el.style.display = 'block';
    el.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing connection...';

    const data = {
        dbhost: document.getElementById('fHost').value,
        dbport: document.getElementById('fPort').value || '3306',
        dbname: document.getElementById('fDbname').value,
        dbusername: document.getElementById('fUser').value,
        dbpassword: document.getElementById('fPass').value,
    };

    try {
        const res = await fetch("{{ route('admin.database.connections.test') }}", {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        el.className = 'test-result ' + (result.success ? 'success' : 'error');
        el.innerHTML = '<i class="fas fa-' + (result.success ? 'check-circle' : 'times-circle') + '"></i> ' + result.message;
    } catch(err) {
        el.className = 'test-result error';
        el.innerHTML = '<i class="fas fa-times-circle"></i> Test failed: ' + err.message;
    }
}

async function deleteConn(id, name) {
    if (!confirm('Delete connection "' + name + '"? This cannot be undone.')) return;
    try {
        await fetch("{{ url('database/connections') }}/" + id, {
            method:'POST',
            headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({_method: 'DELETE'})
        });
        location.reload();
    } catch(err) { toast('Delete failed', 'error'); }
}

async function toggleConn(id) {
    try {
        await fetch("{{ url('database/connections') }}/" + id + "/toggle", {
            method:'POST',
            headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
        });
        location.reload();
    } catch(err) { toast('Toggle failed', 'error'); }
}

function toast(msg, type='info') {
    document.querySelectorAll('.toast').forEach(t => t.remove());
    const t = document.createElement('div');
    t.className = 'toast';
    t.style.background = type==='success'?'var(--c-success)':type==='error'?'var(--c-danger)':'var(--c-secondary)';
    t.innerHTML = '<i class="fas fa-' + (type==='success'?'check-circle':type==='error'?'times-circle':'info-circle') + '"></i> ' + msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 300); }, 3500);
}
</script>
@endpush
