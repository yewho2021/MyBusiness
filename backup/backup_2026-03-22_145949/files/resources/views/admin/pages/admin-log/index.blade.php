@extends('admin.layouts.app')
@section('title', 'Login Activity Log')

@push('styles')
<style>
/* ── Page Header ── */
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-header-left h1 { font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 5px; }
.page-header-left p { font-size: 14px; color: #64748b; }
.page-header-right { display: flex; gap: 10px; flex-wrap: wrap; }

/* ── Buttons ── */
.btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; transition: all .2s; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
.btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.btn-primary { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #fff; }
.btn-primary:hover { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.btn-export { background: #fff; color: #1e293b; border: 1.5px solid #e2e8f0; }
.btn-export:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-export i { color: #16a34a; }
.btn-purge { background: #fff; color: #dc2626; border: 1.5px solid #fecaca; }
.btn-purge:hover { background: #fef2f2; border-color: #fca5a5; }
.btn-outline { background: transparent; color: #475569; border: 1.5px solid #d1d5db; }
.btn-outline:hover { background: #f8fafc; border-color: #94a3b8; transform: none; box-shadow: none; }
.btn-sm { padding: 8px 14px; font-size: 13px; }
.btn-icon-kick { width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid #fecaca; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #dc2626; transition: all .2s; }
.btn-icon-kick:hover { background: #fef2f2; border-color: #f87171; transform: scale(1.08); }

/* ── Alert ── */
.alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
.alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
.alert-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; }

/* ── Stats Row ── */
.stats-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 22px; }
@media(max-width:1200px) { .stats-row { grid-template-columns: repeat(3, 1fr); } }
@media(max-width:640px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
.stat-card { background: #fff; border-radius: 12px; padding: 20px 22px; border: 1px solid #e2e8f0; transition: all .25s; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 12px 0 0 12px; }
.stat-card.c-blue::before { background: #3b82f6; }
.stat-card.c-green::before { background: #22c55e; }
.stat-card.c-red::before { background: #ef4444; }
.stat-card.c-amber::before { background: #f59e0b; }
.stat-card.c-purple::before { background: #8b5cf6; }
.stat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.07); border-color: #cbd5e1; transform: translateY(-2px); }
.stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.stat-icon.blue { background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb; }
.stat-icon.green { background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #16a34a; }
.stat-icon.red { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #dc2626; }
.stat-icon.amber { background: linear-gradient(135deg, #fffbeb, #fef3c7); color: #d97706; }
.stat-icon.purple { background: linear-gradient(135deg, #f5f3ff, #ede9fe); color: #7c3aed; }
.stat-value { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1.1; margin-bottom: 4px; }
.stat-label { font-size: 13px; color: #64748b; font-weight: 500; }

/* ── Filter Panel ── */
.filter-panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 18px; overflow: hidden; }
.filter-toggle { display: flex; justify-content: space-between; align-items: center; padding: 16px 22px; cursor: pointer; user-select: none; transition: background .15s; }
.filter-toggle:hover { background: #f8fafc; }
.filter-toggle h3 { font-size: 15px; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 10px; }
.filter-toggle h3 i { color: #64748b; font-size: 14px; }
.filter-toggle .arrow { transition: transform .25s; color: #94a3b8; font-size: 12px; }
.filter-toggle.open .arrow { transform: rotate(180deg); }
.filter-body { padding: 0 22px 22px; display: none; }
.filter-body.show { display: block; }
.filter-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
@media(max-width:1100px) { .filter-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width:640px) { .filter-grid { grid-template-columns: 1fr; } }
.filter-group label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .4px; }
.filter-group select,
.filter-group input { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 14px; color: #1e293b; background: #fff; transition: all .2s; }
.filter-group select:focus,
.filter-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.filter-actions { margin-top: 16px; display: flex; gap: 10px; justify-content: flex-end; }
.active-filters { display: flex; flex-wrap: wrap; gap: 8px; padding: 0 22px 16px; }
.filter-tag { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; background: #eff6ff; color: #1d4ed8; border-radius: 20px; font-size: 13px; font-weight: 500; }
.filter-tag a { color: #1d4ed8; text-decoration: none; font-weight: 700; margin-left: 3px; font-size: 15px; line-height: 1; }
.filter-tag a:hover { color: #dc2626; }
.filter-tag.clear-all { background: #fef2f2; color: #dc2626; }
.filter-tag.clear-all:hover { background: #fee2e2; }

/* ── Card & Table ── */
.card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-head { padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; }
.card-title { font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.card-title i { color: #94a3b8; font-size: 15px; }
.card-count { font-size: 14px; color: #64748b; font-weight: 500; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 13px 18px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; background: #f8fafc; border-bottom: 2px solid #f1f5f9; white-space: nowrap; }
.data-table td { padding: 14px 18px; font-size: 14px; color: #374151; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.data-table tbody tr:hover td { background: #f8fafc; }
.data-table tbody tr.expanded-parent td { background: #f1f5f9; border-bottom: none; }
.data-table .expand-icon { color: #94a3b8; font-size: 12px; cursor: pointer; transition: transform .25s; }
.data-table .expanded-parent .expand-icon { transform: rotate(180deg); color: #3b82f6; }

/* ── User cell ── */
.user-cell { display: flex; align-items: center; gap: 12px; }
.user-avatar { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
.user-avatar.success { background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #16a34a; }
.user-avatar.failed { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #dc2626; }
.user-avatar.active { background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb; }
.user-name { font-weight: 600; color: #0f172a; font-size: 14px; line-height: 1.3; }
.user-meta { font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 6px; margin-top: 1px; }

/* ── Status Badges ── */
.badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; letter-spacing: .2px; }
.badge.green { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.badge.red { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.badge.blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.badge.amber { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.badge.gray { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
.badge.purple { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
.badge-role { padding: 2px 8px; font-size: 11px; border-radius: 6px; font-weight: 600; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
.status-dot.green { background: #22c55e; }
.status-dot.red { background: #ef4444; }
.status-dot.amber { background: #f59e0b; }
.status-dot.blue { background: #3b82f6; }
.status-dot.gray { background: #9ca3af; }

/* ── IP & Location ── */
.ip-mono { font-family: 'SF Mono', 'Fira Code', monospace; font-size: 13px; color: #1e293b; background: #f8fafc; padding: 3px 8px; border-radius: 6px; border: 1px solid #f1f5f9; }
.location-text { font-size: 13px; color: #475569; }
.location-text i { color: #94a3b8; margin-right: 4px; font-size: 11px; }

/* ── Browser/Device ── */
.browser-cell { display: flex; align-items: center; gap: 8px; }
.device-icon-wrap { width: 30px; height: 30px; border-radius: 8px; background: #f8fafc; border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #64748b; flex-shrink: 0; }
.browser-text { font-size: 13px; color: #374151; font-weight: 500; }

/* ── Login time ── */
.time-date { font-size: 14px; color: #0f172a; font-weight: 500; }
.time-clock { font-size: 12px; color: #94a3b8; margin-top: 2px; }

/* ── Duration ── */
.duration-text { font-size: 13px; color: #475569; font-weight: 500; font-family: 'SF Mono', monospace; }
.online-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1d4ed8; border: 1px solid #bfdbfe; }
.online-badge .pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: #3b82f6; animation: pulse 1.8s ease-in-out infinite; }

/* ── Detail Row ── */
.detail-row { display: none; }
.detail-row.show { display: table-row; }
.detail-content { padding: 20px 28px 20px 48px; background: linear-gradient(180deg, #f8fafc 0%, #fff 100%); border-bottom: 2px solid #e2e8f0; }
.detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
@media(max-width:900px) { .detail-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width:600px) { .detail-grid { grid-template-columns: 1fr; } }
.detail-item { padding: 12px 16px; background: #fff; border-radius: 10px; border: 1px solid #f1f5f9; }
.detail-item label { display: block; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
.detail-item span { font-size: 14px; color: #0f172a; font-weight: 500; word-break: break-all; }
.detail-item span.mono { font-family: 'SF Mono', 'Fira Code', monospace; font-size: 12px; }
.detail-item span.fail { color: #dc2626; font-weight: 600; }
.detail-ua { margin-top: 16px; padding: 14px 18px; background: #0f172a; border-radius: 10px; color: #94a3b8; font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace; font-size: 12px; word-break: break-all; line-height: 1.6; border: 1px solid #1e293b; }

/* ── Modal ── */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.show { display: flex; }
.modal { background: #fff; border-radius: 16px; width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #f1f5f9; }
.modal-header h3 { font-size: 18px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.modal-close { width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; color: #64748b; transition: all .15s; }
.modal-close:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.modal-body { padding: 24px; }
.modal-body > p { font-size: 14px; color: #64748b; margin-bottom: 18px; line-height: 1.5; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 18px 24px; border-top: 1px solid #f1f5f9; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 14px; transition: all .2s; }
.form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

/* ── Pagination ── */
.pagination-wrap { padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; }
.pagination-info { font-size: 14px; color: #64748b; }
.pagination-links { display: flex; gap: 4px; }
.pagination-links a,
.pagination-links span { padding: 7px 14px; border-radius: 8px; font-size: 14px; text-decoration: none; border: 1px solid #e2e8f0; color: #374151; transition: all .15s; }
.pagination-links a:hover { background: #f1f5f9; border-color: #cbd5e1; }
.pagination-links .active span { background: #dc2626; color: #fff; border-color: #dc2626; }
.pagination-links .disabled span { color: #d1d5db; cursor: default; }

/* ── Utilities ── */
.text-muted { color: #94a3b8; }
.text-sm { font-size: 13px; }
.nowrap { white-space: nowrap; }
.empty-state { padding: 70px 20px; text-align: center; }
.empty-state i { font-size: 52px; margin-bottom: 18px; display: block; color: #cbd5e1; }
.empty-state p { font-size: 15px; color: #64748b; }

/* ── Animations ── */
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .25; } }
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
        <h1>Login Activity Log</h1>
        <p>Track all admin login sessions, failed attempts, and active connections</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.admin-log.export', request()->query()) }}" class="btn btn-export">
            <i class="fas fa-file-csv"></i> Export CSV
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
        <div class="stat-label">Total Entries</div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-top">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-value">{{ number_format($stats['success']) }}</div>
        <div class="stat-label">Successful Logins</div>
    </div>
    <div class="stat-card c-red">
        <div class="stat-top">
            <div class="stat-icon red"><i class="fas fa-shield-alt"></i></div>
        </div>
        <div class="stat-value">{{ number_format($stats['failed']) }}</div>
        <div class="stat-label">Failed Attempts</div>
    </div>
    <div class="stat-card c-amber">
        <div class="stat-top">
            <div class="stat-icon amber"><i class="fas fa-bolt"></i></div>
        </div>
        <div class="stat-value">{{ number_format($stats['active_now']) }}</div>
        <div class="stat-label">Active Now</div>
    </div>
    <div class="stat-card c-purple">
        <div class="stat-top">
            <div class="stat-icon purple"><i class="fas fa-fingerprint"></i></div>
        </div>
        <div class="stat-value">{{ number_format($stats['unique_ips']) }}</div>
        <div class="stat-label">Unique IPs</div>
    </div>
</div>

{{-- Filter Panel --}}
<div class="filter-panel">
    <div class="filter-toggle {{ request()->hasAny(['status','admin_id','role_id','ip_address','date_from','date_to','device_type','search']) ? 'open' : '' }}" onclick="toggleFilter()">
        <h3><i class="fas fa-sliders-h"></i> Filters</h3>
        <i class="fas fa-chevron-down arrow"></i>
    </div>

    @php
        $hasFilters = request()->hasAny(['status','admin_id','role_id','ip_address','date_from','date_to','device_type','search']);
    @endphp
    @if($hasFilters)
    <div class="active-filters">
        @if(request('status'))
            <span class="filter-tag"><i class="fas fa-circle" style="font-size:7px;"></i> Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }} <a href="{{ route('admin.admin-log.index', array_merge(request()->except('status'), ['page' => 1])) }}">&times;</a></span>
        @endif
        @if(request('admin_id'))
            @php $filterAdmin = $admins->firstWhere('id', request('admin_id')); @endphp
            <span class="filter-tag"><i class="fas fa-user" style="font-size:9px;"></i> {{ $filterAdmin?->name ?? '#'.request('admin_id') }} <a href="{{ route('admin.admin-log.index', array_merge(request()->except('admin_id'), ['page' => 1])) }}">&times;</a></span>
        @endif
        @if(request('role_id'))
            @php $filterRole = $roles->firstWhere('id', request('role_id')); @endphp
            <span class="filter-tag"><i class="fas fa-user-tag" style="font-size:9px;"></i> {{ $filterRole?->name ?? '#'.request('role_id') }} <a href="{{ route('admin.admin-log.index', array_merge(request()->except('role_id'), ['page' => 1])) }}">&times;</a></span>
        @endif
        @if(request('ip_address'))
            <span class="filter-tag"><i class="fas fa-network-wired" style="font-size:9px;"></i> {{ request('ip_address') }} <a href="{{ route('admin.admin-log.index', array_merge(request()->except('ip_address'), ['page' => 1])) }}">&times;</a></span>
        @endif
        @if(request('date_from'))
            <span class="filter-tag"><i class="fas fa-calendar" style="font-size:9px;"></i> From: {{ request('date_from') }} <a href="{{ route('admin.admin-log.index', array_merge(request()->except('date_from'), ['page' => 1])) }}">&times;</a></span>
        @endif
        @if(request('date_to'))
            <span class="filter-tag"><i class="fas fa-calendar" style="font-size:9px;"></i> To: {{ request('date_to') }} <a href="{{ route('admin.admin-log.index', array_merge(request()->except('date_to'), ['page' => 1])) }}">&times;</a></span>
        @endif
        @if(request('device_type'))
            <span class="filter-tag"><i class="fas fa-desktop" style="font-size:9px;"></i> {{ ucfirst(request('device_type')) }} <a href="{{ route('admin.admin-log.index', array_merge(request()->except('device_type'), ['page' => 1])) }}">&times;</a></span>
        @endif
        @if(request('search'))
            <span class="filter-tag"><i class="fas fa-search" style="font-size:9px;"></i> "{{ request('search') }}" <a href="{{ route('admin.admin-log.index', array_merge(request()->except('search'), ['page' => 1])) }}">&times;</a></span>
        @endif
        <a href="{{ route('admin.admin-log.index') }}" class="filter-tag clear-all" style="text-decoration:none;">Clear All &times;</a>
    </div>
    @endif

    <div class="filter-body {{ $hasFilters ? 'show' : '' }}">
        <form method="GET" action="{{ route('admin.admin-log.index') }}">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="filter-group">
                    <label>Admin User</label>
                    <select name="admin_id">
                        <option value="">All Users</option>
                        @foreach($admins as $a)
                            <option value="{{ $a->id }}" {{ request('admin_id') == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ $a->username }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Role</label>
                    <select name="role_id">
                        <option value="">All Roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Now</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>All Failed</option>
                        <option value="failed_password" {{ request('status') == 'failed_password' ? 'selected' : '' }}>Wrong Password</option>
                        <option value="failed_not_found" {{ request('status') == 'failed_not_found' ? 'selected' : '' }}>User Not Found</option>
                        <option value="failed_inactive" {{ request('status') == 'failed_inactive' ? 'selected' : '' }}>Inactive Account</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Device Type</label>
                    <select name="device_type">
                        <option value="">All Devices</option>
                        <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>Desktop</option>
                        <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                        <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>IP Address</label>
                    <input type="text" name="ip_address" value="{{ request('ip_address') }}" placeholder="e.g. 192.168.1">
                </div>
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, username, IP, country...">
                </div>
            </div>
            <div class="filter-actions">
                <a href="{{ route('admin.admin-log.index') }}" class="btn btn-outline btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Apply Filters</button>
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="card">
    <div class="card-head">
        <span class="card-title"><i class="fas fa-stream"></i> Login Sessions</span>
        <span class="card-count">{{ $logs->total() }} records</span>
    </div>

    @if($logs->isEmpty())
        <div class="empty-state">
            <i class="fas fa-user-shield"></i>
            <p>No login activity found matching your filters.</p>
        </div>
    @else
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:36px;"></th>
                    <th>User</th>
                    <th>Status</th>
                    <th>IP Address</th>
                    <th>Location</th>
                    <th>Browser / Device</th>
                    <th>Login Time</th>
                    <th>Duration</th>
                    <th style="width:60px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr class="main-row" onclick="toggleDetail({{ $log->id }}, this)" style="cursor:pointer;">
                    <td><i class="fas fa-chevron-down expand-icon"></i></td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar {{ $log->isFailed() ? 'failed' : ($log->status === 'active' ? 'active' : 'success') }}">
                                {{ $log->admin_name ? strtoupper(substr($log->admin_name, 0, 1)) : '?' }}
                            </div>
                            <div>
                                <div class="user-name">{{ $log->admin_name ?? '—' }}</div>
                                <div class="user-meta">
                                    {{ $log->admin_username ?? 'unknown' }}
                                    @if($log->role_name)
                                        <span style="color:#d1d5db;">·</span>
                                        <span class="badge-role {{ $log->role_name === 'Administrator' ? 'badge purple' : ($log->role_name === 'Supervisor' ? 'badge amber' : 'badge gray') }}" style="padding:2px 8px;font-size:10px;">{{ $log->role_name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @switch($log->status)
                            @case('active')
                                <span class="badge blue"><span class="status-dot blue"></span> Active</span>
                                @break
                            @case('success')
                                <span class="badge green"><span class="status-dot green"></span> Success</span>
                                @break
                            @case('expired')
                                <span class="badge gray"><span class="status-dot gray"></span> Expired</span>
                                @break
                            @case('failed_password')
                                <span class="badge red"><span class="status-dot red"></span> Wrong Pass</span>
                                @break
                            @case('failed_not_found')
                                <span class="badge red"><span class="status-dot red"></span> Not Found</span>
                                @break
                            @case('failed_inactive')
                                <span class="badge amber"><span class="status-dot amber"></span> Inactive</span>
                                @break
                            @default
                                <span class="badge gray">{{ $log->status }}</span>
                        @endswitch
                    </td>
                    <td>
                        <span class="ip-mono">{{ $log->ip_address }}</span>
                    </td>
                    <td>
                        @if($log->ip_country)
                            <span class="location-text"><i class="fas fa-map-marker-alt"></i>{{ $log->ip_city ?? '' }}{{ $log->ip_city && $log->ip_country ? ', ' : '' }}{{ $log->ip_country }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="browser-cell">
                            <div class="device-icon-wrap">
                                <i class="fas fa-{{ $log->device_type === 'mobile' ? 'mobile-alt' : ($log->device_type === 'tablet' ? 'tablet-alt' : 'desktop') }}"></i>
                            </div>
                            <span class="browser-text">{{ $log->browser ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="nowrap">
                        <div class="time-date">{{ $log->login_at?->format('d M Y') }}</div>
                        <div class="time-clock">{{ $log->login_at?->format('H:i:s') }}</div>
                    </td>
                    <td class="nowrap">
                        @if($log->status === 'active')
                            <span class="online-badge">
                                <span class="pulse-dot"></span> Online
                            </span>
                        @elseif($log->formatted_duration)
                            <span class="duration-text">{{ $log->formatted_duration }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td onclick="event.stopPropagation();">
                        @if($log->status === 'active')
                            <form action="{{ route('admin.admin-log.kick', $log->id) }}" method="POST" onsubmit="return confirm('Terminate this session for {{ $log->admin_name }}?');">
                                @csrf
                                <button type="submit" class="btn-icon-kick" title="Kick Session">
                                    <i class="fas fa-power-off"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                {{-- Expandable detail row --}}
                <tr class="detail-row" id="detail-{{ $log->id }}">
                    <td colspan="9">
                        <div class="detail-content">
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Session ID</label>
                                    <span class="mono">{{ $log->session_id }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>IP Address</label>
                                    <span>{{ $log->ip_address }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>ISP / Provider</label>
                                    <span>{{ $log->ip_isp ?? '—' }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Location</label>
                                    <span>{{ $log->ip_city ?? '—' }}{{ $log->ip_city && $log->ip_country ? ', ' : '' }}{{ $log->ip_country ?? '—' }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Platform / OS</label>
                                    <span>{{ $log->platform ?? '—' }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Browser</label>
                                    <span>{{ $log->browser ?? '—' }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Device Type</label>
                                    <span>{{ ucfirst($log->device_type) }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Login At</label>
                                    <span>{{ $log->login_at?->format('d M Y, H:i:s') }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Logout At</label>
                                    <span>{{ $log->logout_at?->format('d M Y, H:i:s') ?? ($log->status === 'active' ? '— (still active)' : '—') }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Duration</label>
                                    <span>{{ $log->formatted_duration ?? ($log->status === 'active' ? 'Ongoing' : '—') }}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Logout Type</label>
                                    <span>{{ $log->logout_type ? ucfirst($log->logout_type) : '—' }}</span>
                                </div>
                                @if($log->fail_reason)
                                <div class="detail-item">
                                    <label>Fail Reason</label>
                                    <span class="fail">{{ $log->fail_reason }}</span>
                                </div>
                                @endif
                            </div>
                            @if($log->user_agent)
                            <div class="detail-ua">{{ $log->user_agent }}</div>
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
            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
        </div>
        <div class="pagination-links">
            {{ $logs->links('pagination::simple-bootstrap-4') }}
        </div>
    </div>
    @endif
    @endif
</div>

{{-- Purge Modal --}}
<div class="modal-overlay" id="purgeModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-trash-alt" style="color:#dc2626;"></i> Purge Old Logs</h3>
            <button class="modal-close" onclick="closePurgeModal()">&times;</button>
        </div>
        <form action="{{ route('admin.admin-log.purge') }}" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
            @csrf
            <div class="modal-body">
                <p>Delete login log entries older than a specified number of days. Active sessions will not be affected.</p>
                <div class="form-group">
                    <label>Delete entries older than</label>
                    <select name="days" class="form-control">
                        <option value="30">30 days</option>
                        <option value="60">60 days</option>
                        <option value="90" selected>90 days</option>
                        <option value="180">180 days</option>
                        <option value="365">365 days</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closePurgeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-trash-alt"></i> Purge Logs</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleFilter() {
    document.querySelector('.filter-toggle').classList.toggle('open');
    document.querySelector('.filter-body').classList.toggle('show');
}

function toggleDetail(id, rowEl) {
    const detailRow = document.getElementById('detail-' + id);
    const isVisible = detailRow.classList.contains('show');

    if (isVisible) {
        detailRow.classList.remove('show');
        rowEl.classList.remove('expanded-parent');
    } else {
        detailRow.classList.add('show');
        rowEl.classList.add('expanded-parent');
    }
}

function openPurgeModal() {
    document.getElementById('purgeModal').classList.add('show');
}
function closePurgeModal() {
    document.getElementById('purgeModal').classList.remove('show');
}
document.getElementById('purgeModal').addEventListener('click', function(e) {
    if (e.target === this) closePurgeModal();
});
</script>
@endpush
