@extends('admin.layouts.app')
@section('title', 'Activity Log')

@push('styles')
<style>
/* ── Page Header ── */
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-header-left h1 { font-size: 24px; font-weight: 700; color: var(--code-bg); margin-bottom: 5px; }
.page-header-left p { font-size: 14px; color: var(--text-muted); }
.page-header-right { display: flex; gap: 10px; flex-wrap: wrap; }

/* ── Buttons ── */
.btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; transition: all .2s; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
.btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.btn-primary { background: linear-gradient(135deg, var(--c-danger) 0%, var(--c-primary-hover) 100%); color: #fff; }
.btn-primary:hover { background: linear-gradient(135deg, var(--c-danger) 0%, var(--c-danger) 100%); }
.btn-export { background: #fff; color: var(--text-heading); border: 1.5px solid var(--border-color); }
.btn-export:hover { background: var(--table-header-bg); border-color: var(--hover-border); }
.btn-export i { color: var(--c-success); }
.btn-pdf { background: #fff; color: var(--text-heading); border: 1.5px solid var(--border-color); }
.btn-pdf:hover { background: var(--table-header-bg); border-color: var(--hover-border); }
.btn-pdf i { color: var(--c-danger); }
.btn-purge { background: #fff; color: var(--c-danger); border: 1.5px solid var(--c-danger-border); }
.btn-purge:hover { background: var(--c-danger-light); border-color: var(--c-danger-border); }
.btn-outline { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--input-border); }
.btn-outline:hover { background: var(--table-header-bg); border-color: var(--text-faint); transform: none; box-shadow: none; }
.btn-sm { padding: 8px 14px; font-size: 13px; }

/* ── Alert ── */
.alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
.alert-success { background: var(--c-success-light); color: var(--c-success); border: 1px solid var(--c-success-border); }
.alert-danger { background: var(--c-danger-light); color: var(--c-primary-hover); border: 1px solid var(--c-danger-border); }

/* ── Stats Row ── */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
@media(max-width:1200px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
@media(max-width:640px) { .stats-row { grid-template-columns: 1fr; } }
.stat-card { background: #fff; border-radius: 12px; padding: 20px 22px; border: 1px solid var(--border-color); transition: all .25s; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 12px 0 0 12px; }
.stat-card.c-blue::before { background: var(--c-secondary); }
.stat-card.c-green::before { background: var(--c-success); }
.stat-card.c-amber::before { background: var(--c-warning); }
.stat-card.c-purple::before { background: var(--c-purple); }
.stat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.07); border-color: var(--hover-border); transform: translateY(-2px); }
.stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.stat-icon.blue { background: linear-gradient(135deg, var(--c-secondary-light), var(--c-secondary-light)); color: var(--c-secondary); }
.stat-icon.green { background: linear-gradient(135deg, var(--c-success-light), var(--c-success-light)); color: var(--c-success); }
.stat-icon.amber { background: linear-gradient(135deg, var(--c-warning-light), var(--c-warning-light)); color: var(--c-warning); }
.stat-icon.purple { background: linear-gradient(135deg, var(--c-purple-light), var(--c-purple-light)); color: var(--c-purple); }
.stat-value { font-size: 28px; font-weight: 800; color: var(--code-bg); line-height: 1.1; margin-bottom: 4px; }
.stat-label { font-size: 13px; color: var(--text-muted); font-weight: 500; }

/* ── Filter Panel ── */
.filter-panel { background: #fff; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 18px; overflow: hidden; }
.filter-toggle { display: flex; justify-content: space-between; align-items: center; padding: 16px 22px; cursor: pointer; user-select: none; transition: background .15s; }
.filter-toggle:hover { background: var(--table-header-bg); }
.filter-toggle h3 { font-size: 15px; font-weight: 600; color: var(--text-heading); display: flex; align-items: center; gap: 10px; }
.filter-toggle h3 i { color: var(--text-muted); font-size: 14px; }
.filter-toggle .arrow { transition: transform .25s; color: var(--text-faint); font-size: 12px; }
.filter-toggle.open .arrow { transform: rotate(180deg); }
.filter-body { padding: 0 22px 22px; display: none; }
.filter-body.show { display: block; }
.filter-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
@media(max-width:1100px) { .filter-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width:640px) { .filter-grid { grid-template-columns: 1fr; } }
.filter-group label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .4px; }
.filter-group select,
.filter-group input { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 14px; color: var(--text-heading); background: #fff; transition: all .2s; }
.filter-group select:focus,
.filter-group input:focus { outline: none; border-color: var(--c-secondary); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.filter-actions { margin-top: 16px; display: flex; gap: 10px; justify-content: flex-end; }
.active-filters { display: flex; flex-wrap: wrap; gap: 8px; padding: 0 22px 16px; }
.filter-tag { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; background: var(--c-secondary-light); color: var(--c-secondary); border-radius: 20px; font-size: 13px; font-weight: 500; }
.filter-tag a { color: var(--c-secondary); text-decoration: none; font-weight: 700; margin-left: 3px; font-size: 15px; line-height: 1; }
.filter-tag a:hover { color: var(--c-danger); }
.filter-tag.clear-all { background: var(--c-danger-light); color: var(--c-danger); cursor: pointer; }

/* ── Card & Table ── */
.card { background: #fff; border-radius: 14px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-head { padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light); }
.card-title { font-size: 16px; font-weight: 600; color: var(--code-bg); display: flex; align-items: center; gap: 10px; }
.card-title i { color: var(--text-faint); font-size: 15px; }
.card-count { font-size: 14px; color: var(--text-muted); font-weight: 500; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 13px 18px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; background: var(--table-header-bg); border-bottom: 2px solid var(--border-light); white-space: nowrap; }
.data-table td { padding: 14px 18px; font-size: 14px; color: var(--text-body); border-bottom: 1px solid var(--border-light); vertical-align: middle; }
.data-table tbody tr { cursor: pointer; transition: background .15s; }
.data-table tbody tr:hover td { background: var(--table-header-bg); }

/* ── Event Badges ── */
.badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.badge.green { background: var(--c-success-light); color: var(--c-success); border: 1px solid var(--c-success-border); }
.badge.blue { background: var(--c-secondary-light); color: var(--c-secondary); border: 1px solid var(--c-secondary-border); }
.badge.red { background: var(--c-danger-light); color: var(--c-primary-hover); border: 1px solid var(--c-danger-border); }
.badge.amber { background: var(--c-warning-light); color: var(--c-warning); border: 1px solid var(--c-warning-border); }
.badge.gray { background: var(--hover-bg); color: var(--text-secondary); border: 1px solid var(--border-color); }
.badge.purple { background: var(--c-purple-light); color: var(--c-purple); border: 1px solid var(--c-purple-light); }
.badge-model { padding: 3px 10px; font-size: 11px; border-radius: 6px; font-weight: 600; background: var(--border-light); color: var(--text-secondary); border: 1px solid var(--border-color); font-family: 'SF Mono', 'Fira Code', monospace; }

/* ── Changes Preview ── */
.changes-preview { font-size: 13px; color: var(--text-muted); max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.changes-preview .field-name { font-weight: 600; color: var(--text-secondary); }

/* ── Time Cell ── */
.time-date { font-size: 14px; color: var(--code-bg); font-weight: 500; }
.time-clock { font-size: 12px; color: var(--text-faint); margin-top: 2px; }

/* ── Causer Cell ── */
.causer-cell { display: flex; align-items: center; gap: 10px; }
.causer-avatar { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; background: linear-gradient(135deg, var(--c-secondary-light), var(--c-secondary-light)); color: var(--c-secondary); flex-shrink: 0; }
.causer-name { font-weight: 600; color: var(--code-bg); font-size: 14px; }

/* ── Modal ── */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.show { display: flex; }
.modal { background: #fff; border-radius: 16px; width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-light); }
.modal-header h3 { font-size: 18px; font-weight: 700; color: var(--code-bg); display: flex; align-items: center; gap: 10px; }
.modal-close { width: 32px; height: 32px; border-radius: 8px; background: var(--table-header-bg); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; color: var(--text-muted); transition: all .15s; }
.modal-close:hover { background: var(--c-danger-light); color: var(--c-danger); border-color: var(--c-danger-border); }
.modal-body { padding: 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 18px 24px; border-top: 1px solid var(--border-light); }

/* ── Detail Modal Content ── */
.detail-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
@media(max-width:600px) { .detail-meta { grid-template-columns: 1fr; } }
.detail-meta-item { padding: 12px 16px; background: var(--table-header-bg); border-radius: 10px; border: 1px solid var(--border-light); }
.detail-meta-item label { display: block; font-size: 11px; font-weight: 700; color: var(--text-faint); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
.detail-meta-item span { font-size: 14px; color: var(--code-bg); font-weight: 500; }
.diff-table { width: 100%; border-collapse: collapse; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); }
.diff-table th { padding: 10px 16px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; background: var(--table-header-bg); border-bottom: 2px solid var(--border-color); text-align: left; }
.diff-table td { padding: 10px 16px; font-size: 13px; border-bottom: 1px solid var(--border-light); vertical-align: top; word-break: break-word; }
.diff-table .field-col { font-weight: 600; color: var(--text-heading); font-family: 'SF Mono', 'Fira Code', monospace; font-size: 12px; background: var(--table-header-bg); width: 140px; }
.diff-table .old-val { color: var(--c-primary-hover); background: var(--c-danger-light); }
.diff-table .new-val { color: var(--c-success); background: var(--c-success-light); }
.diff-empty { padding: 40px 20px; text-align: center; color: var(--text-faint); font-size: 14px; }
.diff-empty i { font-size: 36px; margin-bottom: 12px; display: block; color: var(--hover-border); }

/* ── Purge Modal ── */
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 14px; font-weight: 600; color: var(--text-body); margin-bottom: 6px; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 14px; transition: all .2s; }
.form-control:focus { outline: none; border-color: var(--c-secondary); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

/* ── Pagination ── */
.pagination-wrap { padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-light); }
.pagination-info { font-size: 14px; color: var(--text-muted); }
.pagination-links { display: flex; gap: 4px; }
.pagination-links a,
.pagination-links span { padding: 7px 14px; border-radius: 8px; font-size: 14px; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-body); transition: all .15s; }
.pagination-links a:hover { background: var(--border-light); border-color: var(--hover-border); }
.pagination-links .active span { background: var(--c-danger); color: #fff; border-color: var(--c-danger); }
.pagination-links .disabled span { color: var(--input-border); cursor: default; }

/* ── Empty State ── */
.empty-state { padding: 70px 20px; text-align: center; }
.empty-state i { font-size: 52px; margin-bottom: 18px; display: block; color: var(--hover-border); }
.empty-state p { font-size: 15px; color: var(--text-muted); }

/* ── Loading ── */
.detail-loading { padding: 40px; text-align: center; color: var(--text-faint); }
.detail-loading i { font-size: 24px; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>Activity Log</h1>
        <p>Track all data changes — who changed what, when, with full audit trail</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.activity-log.export', request()->query()) }}" class="btn btn-export">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('admin.activity-log.export-pdf', request()->query()) }}" class="btn btn-pdf">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <button class="btn btn-purge" onclick="openPurgeModal()">
            <i class="fas fa-trash-alt"></i> Purge Old
        </button>
    </div>
</div>

{{-- Stats Row --}}
<div class="stats-row">
    <div class="stat-card c-blue">
        <div class="stat-top">
            <div class="stat-icon blue"><i class="fas fa-list-ul"></i></div>
        </div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
        <div class="stat-label">Total Activities</div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-top">
            <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
        </div>
        <div class="stat-value">{{ number_format($stats['today']) }}</div>
        <div class="stat-label">Today</div>
    </div>
    <div class="stat-card c-amber">
        <div class="stat-top">
            <div class="stat-icon amber"><i class="fas fa-calendar-week"></i></div>
        </div>
        <div class="stat-value">{{ number_format($stats['this_week']) }}</div>
        <div class="stat-label">This Week</div>
    </div>
    <div class="stat-card c-purple">
        <div class="stat-top">
            <div class="stat-icon purple"><i class="fas fa-user-edit"></i></div>
        </div>
        <div class="stat-value">{{ $stats['top_admin'] ?? '—' }}</div>
        <div class="stat-label">Most Active Admin{{ isset($stats['top_admin_count']) ? ' ('.$stats['top_admin_count'].')' : '' }}</div>
    </div>
</div>

{{-- Filter Panel --}}
<div class="filter-panel">
    <div class="filter-toggle {{ request()->hasAny(['subject_type','event','causer_id','log_name','date_from','date_to','search']) ? 'open' : '' }}" onclick="toggleFilter()">
        <h3><i class="fas fa-filter"></i> Filters</h3>
        <i class="fas fa-chevron-down arrow"></i>
    </div>

    @php $hasFilters = request()->hasAny(['subject_type','event','causer_id','log_name','date_from','date_to','search']); @endphp

    @if($hasFilters)
    <div class="active-filters">
        @if(request('subject_type'))
            <span class="filter-tag">Model: {{ class_basename(request('subject_type')) }} <a href="{{ request()->fullUrlWithoutQuery('subject_type') }}">×</a></span>
        @endif
        @if(request('event'))
            <span class="filter-tag">Event: {{ request('event') }} <a href="{{ request()->fullUrlWithoutQuery('event') }}">×</a></span>
        @endif
        @if(request('causer_id'))
            @php $causerAdmin = $admins->firstWhere('id', request('causer_id')); @endphp
            <span class="filter-tag">Admin: {{ $causerAdmin?->name ?? request('causer_id') }} <a href="{{ request()->fullUrlWithoutQuery('causer_id') }}">×</a></span>
        @endif
        @if(request('date_from'))
            <span class="filter-tag">From: {{ request('date_from') }} <a href="{{ request()->fullUrlWithoutQuery('date_from') }}">×</a></span>
        @endif
        @if(request('date_to'))
            <span class="filter-tag">To: {{ request('date_to') }} <a href="{{ request()->fullUrlWithoutQuery('date_to') }}">×</a></span>
        @endif
        @if(request('search'))
            <span class="filter-tag">Search: {{ request('search') }} <a href="{{ request()->fullUrlWithoutQuery('search') }}">×</a></span>
        @endif
        <a href="{{ route('admin.activity-log.index') }}" class="filter-tag clear-all"><i class="fas fa-times"></i> Clear All</a>
    </div>
    @endif

    <form method="GET" action="{{ route('admin.activity-log.index') }}">
        <div class="filter-body {{ $hasFilters ? 'show' : '' }}" id="filterBody">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Model Type</label>
                    <select name="subject_type">
                        <option value="">All Models</option>
                        @foreach($subjectTypes as $type)
                            <option value="{{ $type['full'] }}" {{ request('subject_type') == $type['full'] ? 'selected' : '' }}>{{ $type['short'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Event</label>
                    <select name="event">
                        <option value="">All Events</option>
                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Admin</label>
                    <select name="causer_id">
                        <option value="">All Admins</option>
                        @foreach($admins as $a)
                            <option value="{{ $a->id }}" {{ request('causer_id') == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ $a->username }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search changes...">
                </div>
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="filter-group">
                    <label>Log Name</label>
                    <select name="log_name">
                        <option value="">All Logs</option>
                        @foreach($logNames as $ln)
                            <option value="{{ $ln }}" {{ request('log_name') == $ln ? 'selected' : '' }}>{{ ucfirst($ln) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Apply Filters</button>
            </div>
        </div>
    </form>
</div>

{{-- Data Table --}}
<div class="card">
    <div class="card-head">
        <div class="card-title"><i class="fas fa-shoe-prints"></i> Activity Log</div>
        <div class="card-count">{{ $logs->total() }} entries</div>
    </div>

    @if($logs->count() > 0)
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Admin</th>
                    <th>Event</th>
                    <th>Model</th>
                    <th>ID</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr onclick="showDetail({{ $log->id }})">
                    <td>
                        <div class="time-date">{{ $log->created_at?->format('d M Y') }}</div>
                        <div class="time-clock">{{ $log->created_at?->format('H:i:s') }}</div>
                    </td>
                    <td>
                        @if($log->causer_id)
                            @php
                                $causerAdmin = $admins->firstWhere('id', $log->causer_id);
                                $initials = $causerAdmin ? strtoupper(substr($causerAdmin->name, 0, 2)) : '??';
                            @endphp
                            <div class="causer-cell">
                                <div class="causer-avatar">{{ $initials }}</div>
                                <div class="causer-name">{{ $causerAdmin?->name ?? 'Unknown' }}</div>
                            </div>
                        @else
                            <span style="color: var(--text-faint);">System</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $eventClass = match($log->event) {
                                'created' => 'green',
                                'updated' => 'blue',
                                'deleted' => 'red',
                                default => 'gray',
                            };
                            $eventIcon = match($log->event) {
                                'created' => 'fa-plus-circle',
                                'updated' => 'fa-edit',
                                'deleted' => 'fa-trash',
                                default => 'fa-circle',
                            };
                        @endphp
                        <span class="badge {{ $eventClass }}"><i class="fas {{ $eventIcon }}"></i> {{ ucfirst($log->event ?? $log->description) }}</span>
                    </td>
                    <td>
                        <span class="badge-model">{{ $log->subject_type ? class_basename($log->subject_type) : '—' }}</span>
                    </td>
                    <td style="color: var(--text-faint); font-family: monospace;">{{ $log->subject_id ?? '—' }}</td>
                    <td>
                        @php
                            $props = $log->properties ? $log->properties->toArray() : [];
                            $changedKeys = array_keys(array_merge($props['old'] ?? [], $props['attributes'] ?? []));
                        @endphp
                        <div class="changes-preview">
                            @if(count($changedKeys) > 0)
                                <span class="field-name">{{ implode(', ', array_slice($changedKeys, 0, 3)) }}</span>
                                @if(count($changedKeys) > 3)
                                    <span style="color: var(--text-faint);"> +{{ count($changedKeys) - 3 }} more</span>
                                @endif
                            @else
                                <span style="color: var(--hover-border);">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">
            Showing {{ $logs->firstItem() }} – {{ $logs->lastItem() }} of {{ number_format($logs->total()) }}
        </div>
        <div class="pagination-links">
            {!! $logs->links('pagination::simple-bootstrap-4') !!}
        </div>
    </div>
    @endif

    @else
    <div class="empty-state">
        <i class="fas fa-shoe-prints"></i>
        <p>No activity logged yet. Changes will appear here once models with tracking are modified.</p>
    </div>
    @endif
</div>

{{-- Detail Modal --}}
<div class="modal-overlay" id="detailModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-eye" style="color: var(--c-secondary);"></i> Activity Detail</h3>
            <button class="modal-close" onclick="closeDetailModal()">×</button>
        </div>
        <div class="modal-body" id="detailContent">
            <div class="detail-loading"><i class="fas fa-spinner"></i><br>Loading...</div>
        </div>
    </div>
</div>

{{-- Purge Modal --}}
<div class="modal-overlay" id="purgeModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-trash-alt" style="color: var(--c-danger);"></i> Purge Old Activities</h3>
            <button class="modal-close" onclick="closePurgeModal()">×</button>
        </div>
        <form method="POST" action="{{ route('admin.activity-log.purge') }}">
            @csrf
            <div class="modal-body">
                <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 18px;">Delete activity log entries older than the specified number of days. This action cannot be undone.</p>
                <div class="form-group">
                    <label>Delete entries older than (days)</label>
                    <input type="number" name="days" class="form-control" value="90" min="30" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" onclick="closePurgeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, var(--c-danger), var(--c-danger));" onclick="return confirm('Are you sure? This will permanently delete old activity log entries.')">
                    <i class="fas fa-trash"></i> Purge
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// ── Filter Toggle ──
function toggleFilter() {
    const toggle = document.querySelector('.filter-toggle');
    const body = document.getElementById('filterBody');
    toggle.classList.toggle('open');
    body.classList.toggle('show');
}

// ── Detail Modal ──
function showDetail(id) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');
    modal.classList.add('show');
    content.innerHTML = '<div class="detail-loading"><i class="fas fa-spinner"></i><br>Loading...</div>';

    fetch(`{{ url('activity-log') }}/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            content.innerHTML = '<div class="diff-empty"><i class="fas fa-exclamation-circle"></i><p>Activity not found.</p></div>';
            return;
        }

        let html = '<div class="detail-meta">';
        html += metaItem('Event', eventBadge(data.event));
        html += metaItem('Model', `<span class="badge-model">${data.subject_type || '—'}</span> #${data.subject_id || '—'}`);
        html += metaItem('Admin', data.causer ? `${data.causer.name} (${data.causer.username})` : 'System');
        html += metaItem('Date', data.created_at || '—');
        html += metaItem('Log Name', data.log_name ? data.log_name.charAt(0).toUpperCase() + data.log_name.slice(1) : '—');
        html += metaItem('Description', data.description || '—');
        html += '</div>';

        if (data.changes && data.changes.length > 0) {
            html += '<table class="diff-table"><thead><tr><th>Field</th><th>Old Value</th><th>New Value</th></tr></thead><tbody>';
            data.changes.forEach(c => {
                html += `<tr><td class="field-col">${c.field}</td><td class="old-val">${escHtml(c.old)}</td><td class="new-val">${escHtml(c.new)}</td></tr>`;
            });
            html += '</tbody></table>';
        } else {
            html += '<div class="diff-empty"><i class="fas fa-check-circle"></i><p>No field-level changes recorded for this activity.</p></div>';
        }

        content.innerHTML = html;
    })
    .catch(() => {
        content.innerHTML = '<div class="diff-empty"><i class="fas fa-exclamation-triangle"></i><p>Failed to load activity detail.</p></div>';
    });
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.remove('show');
}

function metaItem(label, value) {
    return `<div class="detail-meta-item"><label>${label}</label><span>${value}</span></div>`;
}

function eventBadge(event) {
    const map = {
        created: { cls: 'green', icon: 'fa-plus-circle' },
        updated: { cls: 'blue', icon: 'fa-edit' },
        deleted: { cls: 'red', icon: 'fa-trash' },
    };
    const e = map[event] || { cls: 'gray', icon: 'fa-circle' };
    return `<span class="badge ${e.cls}"><i class="fas ${e.icon}"></i> ${(event || 'unknown').charAt(0).toUpperCase() + (event || 'unknown').slice(1)}</span>`;
}

function escHtml(str) {
    if (!str || str === '—') return str || '—';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// ── Purge Modal ──
function openPurgeModal() { document.getElementById('purgeModal').classList.add('show'); }
function closePurgeModal() { document.getElementById('purgeModal').classList.remove('show'); }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeDetailModal();
        closePurgeModal();
    }
});
</script>
@endpush
