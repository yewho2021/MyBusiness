@extends('admin.layouts.app')
@section('title', 'Admin Profile — ' . $admin->name)

@push('styles')
<style>
/* existing styles preserved below */
.perm-toggle{display:inline-flex;align-items:center;gap:4px;cursor:pointer;padding:4px 8px;border-radius:6px;transition:all .2s}
.perm-toggle:hover{background:var(--hover-bg)}
.perm-toggle input{display:none}
.perm-check{width:20px;height:20px;border:2px solid var(--input-border);border-radius:5px;display:flex;align-items:center;justify-content:center;transition:all .2s;background:var(--card-bg,#fff);flex-shrink:0}
.perm-check::after{content:'';display:none;width:5px;height:9px;border:solid #fff;border-width:0 2px 2px 0;transform:rotate(45deg);margin-bottom:2px}
.perm-toggle input:checked ~ .perm-check{background:var(--c-secondary);border-color:var(--c-secondary)}
.perm-toggle input:checked ~ .perm-check::after{display:block}
/* Override states: green border for user-granted, red for user-denied */
.perm-toggle.override-on .perm-check{background:var(--c-success);border-color:var(--c-success)}
.perm-toggle.override-on .perm-check::after{display:block}
.perm-toggle.override-off .perm-check{background:var(--card-bg,#fff);border-color:var(--c-danger)}
.perm-toggle.override-off .perm-check::after{display:none}
.perm-indicator{font-size:11px;font-weight:500;color:var(--text-faint);display:flex;align-items:center;gap:3px;white-space:nowrap}
.perm-indicator i{font-size:10px;color:var(--text-faint)}
.ep-back{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:var(--text-muted);text-decoration:none;padding:6px 14px;border-radius:8px;border:1.5px solid var(--border-color,var(--border-color));background:var(--card-bg,#fff);transition:all .2s;margin-bottom:18px}
.ep-back:hover{background:var(--table-header-bg,var(--table-header-bg));border-color:var(--hover-border);color:var(--header-text,var(--text-heading));transform:translateY(-1px)}
.profile-hero{background:var(--card-bg,#fff);border-radius:16px;border:1px solid var(--card-border,var(--border-color));overflow:hidden;margin-bottom:0;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.profile-banner{height:56px;background:var(--c-primary,var(--c-secondary));position:relative}
.profile-main{display:flex;align-items:center;gap:20px;padding:20px 28px 24px;position:relative;flex-wrap:wrap}
.profile-avatar{width:72px;height:72px;border-radius:14px;background:var(--text-heading);color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;box-shadow:0 2px 8px rgba(0,0,0,.08);flex-shrink:0;letter-spacing:-1px}
.profile-info{flex:1;min-width:200px;padding-bottom:2px}
.profile-name{font-size:22px;font-weight:800;color:var(--header-text,var(--code-bg));line-height:1.2;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.profile-name .role-badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;background:var(--c-secondary-light);color:var(--c-secondary);border:1px solid var(--c-secondary-border);letter-spacing:.3px}
.profile-name .status-pill{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:5px}
.profile-name .status-pill.active{background:var(--c-success-light);color:var(--c-success);border:1px solid var(--c-success-border)}
.profile-name .status-pill.inactive{background:var(--c-danger-light);color:var(--c-primary-hover);border:1px solid var(--c-danger-border)}
.profile-meta{display:flex;gap:20px;margin-top:8px;flex-wrap:wrap}
.profile-meta-item{font-size:13px;color:var(--text-muted);display:flex;align-items:center;gap:6px}
.profile-meta-item i{font-size:12px;color:var(--text-faint);width:14px;text-align:center}
.profile-meta-item strong{color:var(--text-body);font-weight:600}
.ep-tabs-bar{display:flex;gap:0;background:var(--card-bg,#fff);border-left:1px solid var(--card-border,var(--border-color));border-right:1px solid var(--card-border,var(--border-color));border-bottom:1px solid var(--card-border,var(--border-color));border-radius:0 0 16px 16px;overflow-x:auto}
.ep-tab{padding:14px 24px;font-size:14px;font-weight:600;color:var(--text-muted);text-decoration:none;border-bottom:3px solid transparent;transition:all .15s;display:flex;align-items:center;gap:8px;white-space:nowrap}
.ep-tab:hover{color:var(--header-text,var(--text-heading));background:var(--table-header-bg,var(--table-header-bg))}
.ep-tab.active{color:var(--c-primary,var(--c-danger));border-bottom-color:var(--c-primary,var(--c-danger))}
.ep-tab i{font-size:14px}
.ep-tab .tab-count{font-size:11px;padding:1px 7px;border-radius:var(--card-radius,10px);background:var(--border-color);color:var(--text-muted);font-weight:700}
.ep-tab.active .tab-count{background:var(--c-primary,var(--c-danger));color:#fff}
.ep-body{margin-top:22px}
.ep-card{background:var(--card-bg,#fff);border-radius:14px;border:1px solid var(--card-border,var(--border-color));overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:20px}
.ep-card-head{padding:18px 24px;border-bottom:1px solid var(--border-light,var(--border-light));display:flex;justify-content:space-between;align-items:center}
.ep-card-head h3{font-size:16px;font-weight:700;color:var(--header-text,var(--code-bg));display:flex;align-items:center;gap:10px}
.ep-card-head h3 i{color:var(--text-faint);font-size:15px}
.ep-card-body{padding:24px}
.ep-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:700px){.ep-form-grid{grid-template-columns:1fr}}
.ep-form-group{display:flex;flex-direction:column;gap:6px}
.ep-form-group.full{grid-column:1/-1}
.ep-form-group label{font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.4px}
.ep-form-group label .req{color:var(--c-primary,var(--c-danger))}
.ep-form-group label .hint{font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-faint);font-size:11px;margin-left:4px}
.ep-form-group input,.ep-form-group select{padding:11px 14px;border:1.5px solid var(--border-color,var(--border-color));border-radius:var(--card-radius,10px);font-size:14px;color:var(--header-text,var(--text-heading));background:var(--card-bg,#fff);transition:all .2s;width:100%;box-sizing:border-box}
.ep-form-group input:focus,.ep-form-group select:focus{outline:none;border-color:var(--c-secondary);box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.ep-toggle-row{display:flex;align-items:center;gap:12px;padding:6px 0}
.ep-toggle{position:relative;width:44px;height:24px;flex-shrink:0}
.ep-toggle input{opacity:0;width:0;height:0}
.ep-toggle .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:var(--hover-border);border-radius:24px;transition:.25s}
.ep-toggle .slider::before{content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:var(--card-bg,#fff);border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.15)}
.ep-toggle input:checked+.slider{background:var(--c-success)}
.ep-toggle input:checked+.slider::before{transform:translateX(20px)}
.ep-toggle-label{font-size:14px;font-weight:500;color:var(--text-body)}
.ep-form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:8px;padding-top:20px;border-top:1px solid var(--border-light,var(--border-light))}
.ep-btn{padding:11px 24px;border-radius:var(--card-radius,10px);font-size:14px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none}
.ep-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.ep-btn-primary{background:linear-gradient(135deg,var(--c-primary,var(--c-danger)),var(--c-primary-hover));color:#fff}
.ep-btn-secondary{background:var(--card-bg,#fff);color:var(--text-secondary);border:1.5px solid var(--border-color,var(--border-color))}
.ep-btn-secondary:hover{background:var(--table-header-bg,var(--table-header-bg))}
.ep-meta-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:20px;padding-top:20px;border-top:1px solid var(--border-light,var(--border-light))}
.ep-meta-item{display:flex;flex-direction:column;gap:3px}
.ep-meta-item .label{font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;letter-spacing:.4px}
.ep-meta-item .value{font-size:14px;color:var(--header-text,var(--text-heading));font-weight:500}
.ep-alert{padding:14px 18px;border-radius:var(--card-radius,10px);margin-bottom:18px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:10px}
.ep-alert-success{background:var(--c-success-light);color:var(--c-success);border:1px solid var(--c-success-border)}
.ep-alert-danger{background:var(--c-danger-light);color:var(--c-primary-hover);border:1px solid var(--c-danger-border)}
.ep-alert ul{margin:0;padding-left:18px;list-style:disc}
.pwd-section{background:var(--table-header-bg,var(--table-header-bg));border:1px solid var(--border-color,var(--border-color));border-radius:var(--card-radius,12px);padding:20px 24px;margin-top:24px}
.pwd-section h4{font-size:14px;font-weight:700;color:var(--text-body);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.pwd-section h4 i{color:var(--text-faint)}
.twofa-status{padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px}
.twofa-status.enabled{background:var(--c-success-light);color:var(--c-success);border:1px solid var(--c-success-border)}
.twofa-status.disabled{background:var(--hover-bg);color:var(--text-muted);border:1px solid var(--border-color)}
.twofa-desc{font-size:14px;color:var(--text-muted);margin-bottom:20px;line-height:1.6}
.btn-enable-2fa{background:linear-gradient(135deg,var(--c-secondary),var(--c-secondary));color:#fff;padding:12px 24px;border-radius:var(--card-radius,10px);font-size:14px;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s}
.btn-enable-2fa:hover{box-shadow:0 4px 12px rgba(37,99,235,.3);transform:translateY(-1px)}
.btn-disable-2fa{background:var(--card-bg,#fff);color:var(--c-primary,var(--c-danger));border:1.5px solid var(--c-danger-border);padding:10px 20px;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s;margin-top:16px}
.btn-disable-2fa:hover{background:var(--c-danger-light);border-color:var(--c-danger)}
.btn-cancel-setup{background:none;color:var(--text-muted);border:none;padding:8px 0;font-size:13px;cursor:pointer;margin-top:12px}
.twofa-setup-card{background:var(--table-header-bg,var(--table-header-bg));border:1px solid var(--border-color,var(--border-color));border-radius:var(--card-radius,12px);padding:24px}
.twofa-step{display:flex;align-items:flex-start;gap:14px;margin-bottom:16px}
.step-num{width:28px;height:28px;background:var(--c-primary,var(--c-danger));color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0}
.twofa-step strong{font-size:14px;color:var(--header-text,var(--text-heading));display:block;margin-bottom:4px}
.twofa-step p{font-size:13px;color:var(--text-muted);margin:0;line-height:1.5}
.qr-container{text-align:center;margin:20px 0;padding:20px;background:var(--card-bg,#fff);border:1px solid var(--border-color,var(--border-color));border-radius:var(--card-radius,10px);display:inline-block;width:100%}
.qr-container canvas{margin:0 auto;display:block}
.verify-row{display:flex;gap:12px;align-items:center;margin-top:16px}
.otp-input{width:180px;padding:12px 16px;border:2px solid var(--border-color);border-radius:var(--card-radius,10px);font-size:22px;font-weight:700;text-align:center;letter-spacing:10px;outline:none;transition:all .2s;font-family:inherit}
.otp-input:focus{border-color:var(--c-secondary,var(--c-secondary));box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.verify-result{margin-top:12px;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:8px}
.verify-result.success{background:var(--c-success-light);color:var(--c-success);border:1px solid var(--c-success-border)}
.verify-result.error{background:var(--c-danger-light);color:var(--c-primary-hover);border:1px solid var(--c-danger-border)}
.twofa-enabled-info{display:flex;align-items:flex-start;gap:14px;padding:16px 20px;background:var(--c-success-light);border:1px solid var(--c-success-border);border-radius:var(--card-radius,10px)}
.twofa-enabled-info strong{font-size:14px;color:var(--c-success);display:block;margin-bottom:4px}
.twofa-enabled-info p{font-size:13px;color:var(--c-success,var(--c-success));margin:0}
.log-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
@media(max-width:900px){.log-stats{grid-template-columns:repeat(2,1fr)}}
.log-stat{background:var(--card-bg,#fff);border-radius:var(--card-radius,12px);padding:16px 18px;border:1px solid var(--card-border,var(--border-color));position:relative;overflow:hidden}
.log-stat::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:var(--card-radius,12px) 0 0 12px}
.log-stat.s-blue::before{background:var(--c-secondary)}
.log-stat.s-green::before{background:var(--c-success)}
.log-stat.s-red::before{background:var(--c-danger)}
.log-stat.s-amber::before{background:var(--c-warning)}
.log-stat-val{font-size:24px;font-weight:800;color:var(--header-text,var(--code-bg));line-height:1}
.log-stat-label{font-size:12px;color:var(--text-muted);font-weight:500;margin-top:4px}
.log-filter{background:var(--card-bg,#fff);border-radius:var(--card-radius,12px);border:1px solid var(--card-border,var(--border-color));margin-bottom:18px;overflow:hidden}
.log-filter-toggle{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;cursor:pointer;user-select:none;transition:background .15s}
.log-filter-toggle:hover{background:var(--table-header-bg,var(--table-header-bg))}
.log-filter-toggle h4{font-size:14px;font-weight:600;color:var(--header-text,var(--text-heading));display:flex;align-items:center;gap:8px;margin:0}
.log-filter-toggle h4 i{color:var(--text-muted);font-size:13px}
.log-filter-toggle .arrow{transition:transform .25s;color:var(--text-faint);font-size:11px}
.log-filter-toggle.open .arrow{transform:rotate(180deg)}
.log-filter-body{padding:0 20px 20px;display:none}
.log-filter-body.show{display:block}
.log-filter-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
@media(max-width:1100px){.log-filter-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:700px){.log-filter-grid{grid-template-columns:1fr 1fr}}
.log-filter-grid label{display:block;font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.3px}
.log-filter-grid select,.log-filter-grid input{width:100%;padding:9px 12px;border:1.5px solid var(--border-color,var(--border-color));border-radius:8px;font-size:13px;color:var(--header-text,var(--text-heading));background:var(--card-bg,#fff);transition:all .2s;box-sizing:border-box}
.log-filter-grid select:focus,.log-filter-grid input:focus{outline:none;border-color:var(--c-secondary);box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.log-filter-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:14px}
.log-active-filters{display:flex;flex-wrap:wrap;gap:6px;padding:0 20px 14px}
.log-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:var(--c-secondary-light);color:var(--c-secondary);border-radius:16px;font-size:12px;font-weight:600}
.log-tag a{color:var(--c-secondary);text-decoration:none;font-weight:700;margin-left:3px;font-size:14px;line-height:1}
.log-tag a:hover{color:var(--c-primary,var(--c-danger))}
.log-table{width:100%;border-collapse:collapse}
.log-table th{text-align:left;padding:12px 16px;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;background:var(--table-header-bg,var(--table-header-bg));border-bottom:2px solid var(--border-light);white-space:nowrap}
.log-table td{padding:12px 16px;font-size:13px;color:var(--text-body);border-bottom:1px solid var(--border-light,var(--border-light));vertical-align:middle}
.log-table tbody tr:hover td{background:var(--table-header-bg,var(--table-header-bg))}
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:16px;font-size:11px;font-weight:600;white-space:nowrap}
.badge.green{background:var(--c-success-light);color:var(--c-success);border:1px solid var(--c-success-border)}
.badge.red{background:var(--c-danger-light);color:var(--c-primary-hover);border:1px solid var(--c-danger-border)}
.badge.blue{background:var(--c-secondary-light);color:var(--c-secondary);border:1px solid var(--c-secondary-border)}
.badge.amber{background:var(--c-warning-light);color:var(--c-warning);border:1px solid var(--c-warning-border)}
.badge.gray{background:var(--hover-bg);color:var(--text-secondary);border:1px solid var(--border-color)}
.status-dot{width:7px;height:7px;border-radius:50%;display:inline-block;flex-shrink:0}
.status-dot.green{background:var(--c-success)}.status-dot.red{background:var(--c-danger)}.status-dot.blue{background:var(--c-secondary)}.status-dot.amber{background:var(--c-warning)}
.ip-mono{font-family:'SF Mono','Fira Code',monospace;font-size:12px;color:var(--header-text,var(--text-heading));background:var(--table-header-bg,var(--table-header-bg));padding:2px 7px;border-radius:5px;border:1px solid var(--border-light)}
.device-icon{width:26px;height:26px;border-radius:6px;background:var(--table-header-bg,var(--table-header-bg));border:1px solid var(--border-light);display:inline-flex;align-items:center;justify-content:center;font-size:12px;color:var(--text-muted)}
.log-empty{padding:60px 20px;text-align:center;color:var(--text-faint)}
.log-empty i{font-size:36px;display:block;margin-bottom:12px;color:var(--hover-border)}
.log-empty p{font-size:14px;margin-top:6px}
.ep-pagination{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-top:1px solid var(--border-light,var(--border-light));flex-wrap:wrap;gap:10px}
.ep-pagination-info{font-size:13px;color:var(--text-muted)}
.log-detail-row{display:none}
.log-detail-row.show{display:table-row}
.log-detail-row td{padding:0 16px 16px;background:var(--table-header-bg,var(--table-header-bg))}
.log-detail-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;padding:16px;background:var(--card-bg,#fff);border-radius:var(--card-radius,10px);border:1px solid var(--border-color,var(--border-color))}
.log-detail-grid .item{display:flex;flex-direction:column;gap:2px}
.log-detail-grid .item label{font-size:10px;font-weight:700;color:var(--text-faint);text-transform:uppercase;letter-spacing:.4px}
.log-detail-grid .item span{font-size:13px;color:var(--header-text,var(--text-heading));font-weight:500}
.log-detail-ua{font-size:11px;color:var(--text-faint);font-family:'SF Mono',monospace;margin-top:8px;padding:8px 12px;background:var(--border-light,var(--border-light));border-radius:6px;word-break:break-all}
.toast{position:fixed;top:24px;right:24px;padding:14px 22px;border-radius:var(--card-radius,10px);color:#fff;font-size:14px;font-weight:500;z-index:99999;display:flex;align-items:center;gap:8px;box-shadow:0 8px 24px rgba(0,0,0,.15);transition:opacity .3s}
</style>
@endpush

@php
    $editUrl = route('admin.users.edit', $admin->getRouteToken());
    $logCount = \App\Models\AdminLog::where('admin_id', $admin->id)->count();
@endphp

@section('content')
<a href="{{ route('admin.users.index') }}" class="ep-back"><i class="fas fa-arrow-left"></i> Back to List</a>

@if(session('success'))
<div class="ep-alert ep-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="ep-alert ep-alert-danger"><i class="fas fa-exclamation-circle"></i><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

{{-- ── Hero ── --}}
<div class="profile-hero">
    <div class="profile-banner"></div>
    <div class="profile-main">
        <div class="profile-avatar">{{ strtoupper(substr($admin->name, 0, 2)) }}</div>
        <div class="profile-info">
            <div class="profile-name">
                {{ $admin->name }}
                <span class="role-badge">{{ $admin->role->name ?? '—' }}</span>
                <span class="status-pill {{ $admin->is_active ? 'active' : 'inactive' }}">
                    <span class="status-dot {{ $admin->is_active ? 'green' : 'red' }}"></span>
                    {{ $admin->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="profile-meta">
                <span class="profile-meta-item"><i class="fas fa-at"></i> <strong>{{ $admin->username }}</strong></span>
                <span class="profile-meta-item"><i class="fas fa-envelope"></i> {{ $admin->email }}</span>
                <span class="profile-meta-item"><i class="fas fa-clock"></i> Joined {{ $admin->created_at ? $admin->created_at->format('d M Y') : '—' }}</span>
                @if($admin->datetime_lastlogin)
                <span class="profile-meta-item"><i class="fas fa-sign-in-alt"></i> Last login {{ $admin->datetime_lastlogin->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Tab Bar ── --}}
<div class="ep-tabs-bar">
    <a href="{{ $editUrl }}?tab=profile" class="ep-tab {{ $activeTab === 'profile' ? 'active' : '' }}"><i class="fas fa-user"></i> Profile</a>
    <a href="{{ $editUrl }}?tab=security" class="ep-tab {{ $activeTab === 'security' ? 'active' : '' }}"><i class="fas fa-shield-alt"></i> Security</a>
    <a href="{{ $editUrl }}?tab=log" class="ep-tab {{ $activeTab === 'log' ? 'active' : '' }}"><i class="fas fa-history"></i> Access Log <span class="tab-count">{{ $logCount }}</span></a>
    @if(!$admin->isAdministrator())
    <a href="{{ $editUrl }}?tab=permissions" class="ep-tab {{ $activeTab === 'permissions' ? 'active' : '' }}"><i class="fas fa-key"></i> Permissions</a>
    @endif
</div>

<div class="ep-body">
{{-- ═══ TAB: Profile ═══ --}}
@if($activeTab === 'profile')
<div class="ep-card">
    <div class="ep-card-head"><h3><i class="fas fa-id-card"></i> Account Information</h3></div>
    <div class="ep-card-body">
        <form action="{{ route('admin.users.update', $admin->getRouteToken()) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="_tab" value="profile">
            <div class="ep-form-grid">
                <div class="ep-form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required>
                </div>
                <div class="ep-form-group">
                    <label>Username <span class="req">*</span></label>
                    <input type="text" name="username" value="{{ old('username', $admin->username) }}" required>
                </div>
                <div class="ep-form-group">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required>
                </div>
                <div class="ep-form-group">
                    <label>Role <span class="req">*</span></label>
                    <select name="role_id" required>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $admin->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ep-form-group">
                    <label>Status</label>
                    <div class="ep-toggle-row">
                        <label class="ep-toggle"><input type="checkbox" name="is_active" {{ $admin->is_active ? 'checked' : '' }}><span class="slider"></span></label>
                        <span class="ep-toggle-label">Account is {{ $admin->is_active ? 'active' : 'inactive' }}</span>
                    </div>
                </div>
            </div>
            <div class="pwd-section">
                <h4><i class="fas fa-key"></i> Change Password</h4>
                <div class="ep-form-grid">
                    <div class="ep-form-group"><label>New Password <span class="hint">(leave blank to keep current)</span></label><input type="password" name="password" minlength="6" autocomplete="new-password"></div>
                    <div class="ep-form-group"><label>Confirm Password</label><input type="password" name="password_confirmation" minlength="6" autocomplete="new-password"></div>
                </div>
            </div>
            <div class="ep-meta-row">
                <div class="ep-meta-item"><span class="label">Admin ID</span><span class="value">#{{ $admin->id }}</span></div>
                <div class="ep-meta-item"><span class="label">Created</span><span class="value">{{ $admin->created_at ? $admin->created_at->format('d M Y, H:i') : '—' }}</span></div>
                <div class="ep-meta-item"><span class="label">Last Updated</span><span class="value">{{ $admin->updated_at ? $admin->updated_at->format('d M Y, H:i') : '—' }}</span></div>
                <div class="ep-meta-item"><span class="label">Last Login</span><span class="value">{{ $admin->datetime_lastlogin ? $admin->datetime_lastlogin->format('d M Y, H:i') : 'Never' }}</span></div>
            </div>
            <div class="ep-form-actions">
                <a href="{{ route('admin.users.index') }}" class="ep-btn ep-btn-secondary">Cancel</a>
                <button type="submit" class="ep-btn ep-btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ TAB: Security ═══ --}}
@elseif($activeTab === 'security')
<div class="ep-card">
    <div class="ep-card-head">
        <h3><i class="fas fa-shield-alt"></i> Two-Factor Authentication</h3>
        <span class="twofa-status {{ $admin->twofa_enabled ? 'enabled' : 'disabled' }}" id="twofaStatusBadge">
            <i class="fas fa-{{ $admin->twofa_enabled ? 'check-circle' : 'times-circle' }}"></i>
            {{ $admin->twofa_enabled ? 'Enabled' : 'Disabled' }}
        </span>
    </div>
    <div class="ep-card-body">
        <p class="twofa-desc">Add an extra layer of security. When enabled, a 6-digit code from your authenticator app is required during login.</p>
        <div id="twofa-off" style="{{ $admin->twofa_enabled ? 'display:none' : '' }}">
            <button type="button" class="btn-enable-2fa" onclick="setup2FA()"><i class="fas fa-qrcode"></i> Setup Two-Factor Authentication</button>
        </div>
        <div id="twofa-setup" style="display:none;">
            <div class="twofa-setup-card">
                <div class="twofa-step"><span class="step-num">1</span><div><strong>Scan this QR code</strong><p>Open your authenticator app and scan the code below.</p></div></div>
                <div class="qr-container" id="qrContainer"></div>
                <div class="twofa-step"><span class="step-num">2</span><div><strong>Enter the 6-digit code</strong><p>Enter the code shown in your authenticator app to verify.</p></div></div>
                <div class="verify-row">
                    <input type="text" id="verifyCode" class="otp-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="000000">
                    <button type="button" class="ep-btn ep-btn-primary" onclick="verify2FA()"><i class="fas fa-check"></i> Verify & Activate</button>
                </div>
                <div id="verifyResult" class="verify-result" style="display:none;"></div>
                <button type="button" class="btn-cancel-setup" onclick="cancelSetup()">Cancel Setup</button>
            </div>
        </div>
        <div id="twofa-on" style="{{ $admin->twofa_enabled ? '' : 'display:none' }}">
            <div class="twofa-enabled-info"><i class="fas fa-lock" style="color:var(--c-success,var(--c-success));font-size:20px;"></i><div><strong>Two-factor authentication is active</strong><p>A verification code is required on every login.</p></div></div>
            <button type="button" class="btn-disable-2fa" onclick="disable2FA()"><i class="fas fa-times"></i> Disable Two-Factor Authentication</button>
        </div>
    </div>
</div>
<div class="ep-card">
    <div class="ep-card-head"><h3><i class="fas fa-key"></i> Change Password</h3></div>
    <div class="ep-card-body">
        <form action="{{ route('admin.users.update', $admin->getRouteToken()) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="_tab" value="security">
            <input type="hidden" name="name" value="{{ $admin->name }}">
            <input type="hidden" name="username" value="{{ $admin->username }}">
            <input type="hidden" name="email" value="{{ $admin->email }}">
            <input type="hidden" name="role_id" value="{{ $admin->role_id }}">
            @if($admin->is_active)<input type="hidden" name="is_active" value="1">@endif
            <div class="ep-form-grid">
                <div class="ep-form-group"><label>New Password <span class="req">*</span></label><input type="password" name="password" minlength="6" required autocomplete="new-password"></div>
                <div class="ep-form-group"><label>Confirm Password <span class="req">*</span></label><input type="password" name="password_confirmation" minlength="6" required autocomplete="new-password"></div>
            </div>
            <div class="ep-form-actions"><button type="submit" class="ep-btn ep-btn-primary"><i class="fas fa-key"></i> Update Password</button></div>
        </form>
    </div>
</div>

{{-- ═══ TAB: Access Log ═══ --}}
@elseif($activeTab === 'log')
@php
    $hasFilters = request()->hasAny(['status','device','date_from','date_to','ip']);
    $filterParams = request()->only(['status','device','date_from','date_to','ip']);
@endphp

@if(!empty($logStats))
<div class="log-stats">
    <div class="log-stat s-blue"><div class="log-stat-val">{{ number_format($logStats['total'] ?? 0) }}</div><div class="log-stat-label">Total Sessions</div></div>
    <div class="log-stat s-green"><div class="log-stat-val">{{ number_format($logStats['success'] ?? 0) }}</div><div class="log-stat-label">Successful</div></div>
    <div class="log-stat s-red"><div class="log-stat-val">{{ number_format($logStats['failed'] ?? 0) }}</div><div class="log-stat-label">Failed</div></div>
    <div class="log-stat s-amber"><div class="log-stat-val">{{ number_format($logStats['active'] ?? 0) }}</div><div class="log-stat-label">Active Now</div></div>
</div>
@endif

<div class="log-filter">
    <div class="log-filter-toggle {{ $hasFilters ? 'open' : '' }}" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('show')">
        <h4><i class="fas fa-filter"></i> Filters</h4>
        <i class="fas fa-chevron-down arrow"></i>
    </div>
    <div class="log-filter-body {{ $hasFilters ? 'show' : '' }}">
        <form method="GET" action="{{ $editUrl }}">
            <input type="hidden" name="tab" value="log">
            <div class="log-filter-grid">
                <div><label>Status</label><select name="status"><option value="">All</option><option value="success" {{ request('status')==='success'?'selected':'' }}>Success</option><option value="failed" {{ request('status')==='failed'?'selected':'' }}>Failed</option><option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option></select></div>
                <div><label>Device</label><select name="device"><option value="">All</option><option value="desktop" {{ request('device')==='desktop'?'selected':'' }}>Desktop</option><option value="mobile" {{ request('device')==='mobile'?'selected':'' }}>Mobile</option><option value="tablet" {{ request('device')==='tablet'?'selected':'' }}>Tablet</option></select></div>
                <div><label>From</label><input type="date" name="date_from" value="{{ request('date_from') }}"></div>
                <div><label>To</label><input type="date" name="date_to" value="{{ request('date_to') }}"></div>
                <div><label>IP Address</label><input type="text" name="ip" value="{{ request('ip') }}" placeholder="e.g. 192.168"></div>
            </div>
            <div class="log-filter-actions">
                <a href="{{ $editUrl }}?tab=log" class="ep-btn ep-btn-secondary" style="padding:8px 16px;font-size:13px"><i class="fas fa-times"></i> Clear</a>
                <button type="submit" class="ep-btn ep-btn-primary" style="padding:8px 16px;font-size:13px"><i class="fas fa-search"></i> Apply</button>
            </div>
        </form>
    </div>

    @if($hasFilters)
    <div class="log-active-filters">
        @foreach($filterParams as $key => $val)
            @if($val)
            @php $without = array_merge(array_diff_key($filterParams, [$key => '']), ['tab' => 'log']); @endphp
            <span class="log-tag">
                <i class="fas fa-circle" style="font-size:6px"></i>
                {{ str_replace('_', ' ', ucfirst($key)) }}: {{ $val }}
                <a href="{{ $editUrl . '?' . http_build_query($without) }}">&times;</a>
            </span>
            @endif
        @endforeach
    </div>
    @endif
</div>

<div class="ep-card">
    <div class="ep-card-head">
        <h3><i class="fas fa-list-alt"></i> Login History</h3>
        @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator && $logs->total() > 0)
        <span style="font-size:13px;color:var(--text-muted)">{{ $logs->total() }} record{{ $logs->total() !== 1 ? 's' : '' }}</span>
        @endif
    </div>

    @if($logs->isEmpty())
    <div class="log-empty">
        <i class="fas fa-inbox"></i>
        <strong>No login records found</strong>
        <p>{{ $hasFilters ? 'Try adjusting your filters.' : 'This admin has no login activity yet.' }}</p>
    </div>
    @else
    <div style="overflow-x:auto">
        <table class="log-table">
            <thead><tr><th style="width:30px"></th><th>Status</th><th>IP / Location</th><th>Browser</th><th>Device</th><th>Login At</th><th>Duration</th></tr></thead>
            <tbody>
            @foreach($logs as $log)
            @php
                if ($log->status === 'active') { $sBadge='blue'; $sLabel='Active'; $sDot='blue'; }
                elseif (in_array($log->status, ['success','expired'])) { $sBadge='green'; $sLabel=$log->status==='expired'?'Expired':'Success'; $sDot='green'; }
                elseif (str_starts_with($log->status, 'failed_')) { $sBadge='red'; $sLabel='Failed'; $sDot='red'; }
                else { $sBadge='gray'; $sLabel=ucfirst($log->status); $sDot='gray'; }
                $deviceIcon = match($log->device_type) { 'mobile'=>'fa-mobile-alt', 'tablet'=>'fa-tablet-alt', default=>'fa-desktop' };
            @endphp
            <tr onclick="toggleLogDetail({{ $log->id }},this)" style="cursor:pointer" id="row-{{ $log->id }}">
                <td><i class="fas fa-chevron-down" style="font-size:10px;color:var(--text-faint);transition:transform .2s"></i></td>
                <td><span class="badge {{ $sBadge }}"><span class="status-dot {{ $sDot }}"></span> {{ $sLabel }}</span></td>
                <td>
                    <span class="ip-mono">{{ $log->ip_address }}</span>
                    @if($log->ip_city || $log->ip_country)
                    <div style="font-size:12px;color:var(--text-muted);margin-top:3px"><i class="fas fa-map-marker-alt" style="color:var(--text-faint);font-size:10px;margin-right:3px"></i>{{ $log->ip_city }}{{ $log->ip_city && $log->ip_country ? ', ' : '' }}{{ $log->ip_country }}</div>
                    @endif
                </td>
                <td style="font-size:13px;color:var(--text-body)">{{ $log->browser ?? '—' }}</td>
                <td><span class="device-icon"><i class="fas {{ $deviceIcon }}"></i></span></td>
                <td>
                    <div style="font-size:13px;color:var(--header-text,var(--text-heading));font-weight:500">{{ $log->login_at?->format('d M Y') }}</div>
                    <div style="font-size:12px;color:var(--text-faint)">{{ $log->login_at?->format('H:i:s') }}</div>
                </td>
                <td>
                    @if($log->status === 'active')
                    <span class="badge blue" style="font-size:11px"><i class="fas fa-circle" style="font-size:6px"></i> Online</span>
                    @else
                    <span style="font-size:13px;color:var(--text-muted)">{{ $log->formatted_duration ?? '—' }}</span>
                    @endif
                </td>
            </tr>
            <tr class="log-detail-row" id="detail-{{ $log->id }}">
                <td colspan="7">
                    <div class="log-detail-grid">
                        <div class="item"><label>Session ID</label><span style="font-family:monospace;font-size:11px">{{ Str::limit($log->session_id, 16) }}</span></div>
                        <div class="item"><label>IP Address</label><span>{{ $log->ip_address }}</span></div>
                        <div class="item"><label>ISP</label><span>{{ $log->ip_isp ?? '—' }}</span></div>
                        <div class="item"><label>Location</label><span>{{ $log->ip_city ?? '—' }}{{ $log->ip_city && $log->ip_country ? ', ' : '' }}{{ $log->ip_country ?? '—' }}</span></div>
                        <div class="item"><label>Platform</label><span>{{ $log->platform ?? '—' }}</span></div>
                        <div class="item"><label>Browser</label><span>{{ $log->browser ?? '—' }}</span></div>
                        <div class="item"><label>Device Type</label><span>{{ ucfirst($log->device_type) }}</span></div>
                        <div class="item"><label>Login At</label><span>{{ $log->login_at?->format('d M Y, H:i:s') }}</span></div>
                        <div class="item"><label>Logout At</label><span>{{ $log->logout_at?->format('d M Y, H:i:s') ?? ($log->status === 'active' ? '— (active)' : '—') }}</span></div>
                        <div class="item"><label>Duration</label><span>{{ $log->formatted_duration ?? ($log->status === 'active' ? 'Ongoing' : '—') }}</span></div>
                        <div class="item"><label>Logout Type</label><span>{{ $log->logout_type ? ucfirst($log->logout_type) : '—' }}</span></div>
                        @if($log->fail_reason)
                        <div class="item"><label>Fail Reason</label><span style="color:var(--c-primary,var(--c-danger))">{{ $log->fail_reason }}</span></div>
                        @endif
                    </div>
                    @if($log->user_agent)
                    <div class="log-detail-ua">{{ $log->user_agent }}</div>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="ep-pagination">
        <div class="ep-pagination-info">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</div>
        <div>{{ $logs->links() }}</div>
    </div>
    @endif
    @endif
</div>
@endif

{{-- ═══ TAB: Permissions (per-user overrides) ═══ --}}
@if($activeTab === 'permissions' && !$admin->isAdministrator())
<div style="margin-bottom:20px;padding:16px 20px;background:var(--c-info-light);border-radius:var(--card-radius,10px);border:1px solid var(--c-info);border-left:4px solid var(--c-info);display:flex;gap:12px;align-items:flex-start;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <i class="fas fa-info-circle" style="color:var(--c-info);margin-top:2px"></i>
    <div style="font-size:14px;color:var(--text-body);line-height:1.6">
        <strong>User-level permission overrides</strong> take priority over the role (<strong>{{ $admin->role->name ?? 'Unknown' }}</strong>) permissions.
        Only set overrides where this user needs different access than their role. Leave unchecked to inherit from role defaults.
        <span style="color:var(--c-secondary);font-weight:600">Blue = inherited from role</span> &middot;
        <span style="color:var(--c-success);font-weight:600">Green = user override (granted)</span> &middot;
        <span style="color:var(--c-danger);font-weight:600">Red = user override (denied)</span>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:16px">
    <button onclick="resetPerms()" class="ep-btn ep-btn-secondary" style="padding:8px 16px;font-size:13px"><i class="fas fa-undo"></i> Reset to Role Defaults</button>
    <button onclick="savePerms()" class="ep-btn ep-btn-primary" style="padding:8px 16px;font-size:13px"><i class="fas fa-save"></i> Save Overrides</button>
</div>

@php
    $groupPalette = [
        ['accent' => 'var(--c-secondary)', 'bg' => 'var(--c-secondary-light)', 'border' => 'rgba(37,99,235,.2)', 'icon_bg' => 'var(--c-secondary)', 'icon_fg' => '#fff'],
        ['accent' => 'var(--c-success)', 'bg' => 'var(--c-success-light)', 'border' => 'rgba(22,163,74,.2)', 'icon_bg' => 'var(--c-success)', 'icon_fg' => '#fff'],
        ['accent' => 'var(--c-purple,#7c3aed)', 'bg' => 'var(--c-purple-light,#f5f3ff)', 'border' => 'rgba(124,58,237,.2)', 'icon_bg' => 'var(--c-purple,#7c3aed)', 'icon_fg' => '#fff'],
        ['accent' => 'var(--c-warning)', 'bg' => 'var(--c-warning-light)', 'border' => 'rgba(217,119,6,.2)', 'icon_bg' => 'var(--c-warning)', 'icon_fg' => '#fff'],
        ['accent' => 'var(--c-info)', 'bg' => 'var(--c-info-light)', 'border' => 'rgba(14,165,233,.2)', 'icon_bg' => 'var(--c-info)', 'icon_fg' => '#fff'],
        ['accent' => 'var(--c-danger)', 'bg' => 'var(--c-danger-light)', 'border' => 'rgba(220,38,38,.2)', 'icon_bg' => 'var(--c-danger)', 'icon_fg' => '#fff'],
    ];
    $gIdx = 0;
@endphp

@foreach($menuGroups as $group)
@php
    $groupMenus = $menus->where('group_id', $group->id);
    $pal = $groupPalette[$gIdx % count($groupPalette)];
    $gIdx++;
@endphp
@if($groupMenus->isNotEmpty())
<div style="margin-bottom:24px;border-radius:var(--card-radius,12px);border:1px solid {{ $pal['accent'] }};overflow:hidden;background:var(--card-bg,#fff);box-shadow:0 1px 3px rgba(0,0,0,.06)">
    {{-- Group header --}}
    <div style="display:flex;align-items:center;gap:14px;padding:16px 22px;background:{{ $pal['bg'] }};border-bottom:2px solid {{ $pal['border'] }}">
        <div style="width:36px;height:36px;border-radius:10px;background:{{ $pal['icon_bg'] }};color:{{ $pal['icon_fg'] }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
            <i class="{{ $groupMenus->first()->icon ?? 'fas fa-folder' }}"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--text-heading)">{{ $group->title }}</div>
            <div style="font-size:12px;color:var(--text-muted)">{{ $groupMenus->count() }} {{ Str::plural('menu', $groupMenus->count()) }}</div>
        </div>
        <div style="margin-left:auto;display:flex;gap:6px">
            <button type="button" onclick="selectAllGroup(this)" class="ep-btn ep-btn-secondary" style="padding:5px 12px;font-size:11px;border-radius:6px"><i class="fas fa-check-double"></i> All</button>
            <button type="button" onclick="deselectAllGroup(this)" class="ep-btn ep-btn-secondary" style="padding:5px 12px;font-size:11px;border-radius:6px"><i class="fas fa-times"></i> None</button>
        </div>
    </div>

    {{-- Permission table --}}
    <table class="card-table" style="font-size:14px">
        <thead>
            <tr>
                <th style="width:28%;padding-left:22px">Menu</th>
                <th style="text-align:center;width:13%"><i class="fas fa-eye" style="margin-right:4px;font-size:11px;color:var(--text-faint)"></i> View</th>
                <th style="text-align:center;width:13%"><i class="fas fa-plus" style="margin-right:4px;font-size:11px;color:var(--text-faint)"></i> Create</th>
                <th style="text-align:center;width:13%"><i class="fas fa-pen" style="margin-right:4px;font-size:11px;color:var(--text-faint)"></i> Edit</th>
                <th style="text-align:center;width:13%"><i class="fas fa-trash" style="margin-right:4px;font-size:11px;color:var(--text-faint)"></i> Delete</th>
                <th style="text-align:center;width:20%">Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($groupMenus as $menu)
            @php
                $rp = $rolePerms[$menu->id] ?? null;
                $up = $userOverrides[$menu->id] ?? null;
                $hasOverride = $up !== null;
            @endphp
            <tr data-menu-id="{{ $menu->id }}" style="{{ $menu->level === 2 ? 'background:var(--hover-bg)' : '' }}">
                <td style="font-weight:{{ $menu->level === 2 ? '500' : '600' }};padding-left:{{ $menu->level === 2 ? '44px' : '22px' }}">
                    @if($menu->level === 2)<span style="color:var(--border-color);margin-right:6px">└</span>@endif
                    @if($menu->icon)<i class="{{ $menu->icon }}" style="color:{{ $pal['accent'] }};margin-right:8px;width:18px;text-align:center;font-size:13px"></i>@endif
                    {{ $menu->title }}
                </td>
                @foreach(['can_view','can_create','can_edit','can_delete'] as $perm)
                    @php
                        $roleVal = $rp ? (bool)$rp->{$perm} : false;
                        $userVal = $up ? (bool)$up->{$perm} : null;
                        $effective = $userVal !== null ? $userVal : $roleVal;
                    @endphp
                    <td style="text-align:center">
                        <div class="perm-toggle {{ $hasOverride ? ($effective ? 'override-on' : 'override-off') : '' }}" title="{{ $hasOverride ? 'User override' : 'From role: '.($roleVal ? 'granted' : 'denied') }}">
                            <input type="checkbox"
                                class="perm-cb"
                                data-menu="{{ $menu->id }}"
                                data-perm="{{ $perm }}"
                                data-role-default="{{ $roleVal ? 1 : 0 }}"
                                {{ $effective ? 'checked' : '' }}>
                            <span class="perm-check"></span>
                        </div>
                    </td>
                @endforeach
                <td style="text-align:center">
                    @if($hasOverride)
                        <span class="badge" style="background:var(--c-warning-light);color:var(--c-warning);font-size:12px"><i class="fas fa-user-edit"></i> Custom</span>
                    @else
                        <span style="font-size:12px;color:var(--text-faint)">Role default</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endforeach

<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">
    <button onclick="resetPerms()" class="ep-btn ep-btn-secondary" style="padding:8px 16px;font-size:13px"><i class="fas fa-undo"></i> Reset to Role Defaults</button>
    <button onclick="savePerms()" class="ep-btn ep-btn-primary" style="padding:8px 16px;font-size:13px"><i class="fas fa-save"></i> Save Overrides</button>
</div>
@endif

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const ADMIN_TOKEN = '{{ $admin->getRouteToken() }}';

function toast(msg, type) {
    const t = document.createElement('div');
    t.className = 'toast';
    t.style.background = type === 'success' ? 'var(--c-success)' : 'var(--c-danger)';
    t.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'times-circle') + '"></i> ' + msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
}

async function setup2FA() {
    try {
        const res = await fetch('/users/' + ADMIN_TOKEN + '/2fa/setup', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (!data.success) { toast(data.message || 'Setup failed', 'error'); return; }
        document.getElementById('twofa-off').style.display = 'none';
        document.getElementById('twofa-setup').style.display = 'block';
        const container = document.getElementById('qrContainer');
        container.innerHTML = '';
        new QRCode(container, { text: data.qr_uri, width: 200, height: 200, colorDark: '#000', colorLight: '#fff', correctLevel: QRCode.CorrectLevel.M });
        document.getElementById('verifyCode').value = '';
        document.getElementById('verifyCode').focus();
    } catch (err) { toast('Failed: ' + err.message, 'error'); }
}

async function verify2FA() {
    const code = document.getElementById('verifyCode').value.replace(/\D/g, '');
    if (code.length !== 6) { toast('Enter a 6-digit code', 'error'); return; }
    const el = document.getElementById('verifyResult');
    try {
        const res = await fetch('/users/' + ADMIN_TOKEN + '/2fa/verify', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: code })
        });
        const data = await res.json();
        el.style.display = 'flex';
        if (data.success) {
            el.className = 'verify-result success';
            el.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
            setTimeout(() => {
                document.getElementById('twofa-setup').style.display = 'none';
                document.getElementById('twofa-on').style.display = 'block';
                const badge = document.getElementById('twofaStatusBadge');
                badge.className = 'twofa-status enabled';
                badge.innerHTML = '<i class="fas fa-check-circle"></i> Enabled';
            }, 1000);
        } else {
            el.className = 'verify-result error';
            el.innerHTML = '<i class="fas fa-times-circle"></i> ' + data.message;
        }
    } catch (err) {
        el.style.display = 'flex'; el.className = 'verify-result error';
        el.innerHTML = '<i class="fas fa-times-circle"></i> Failed: ' + err.message;
    }
}

function cancelSetup() {
    document.getElementById('twofa-setup').style.display = 'none';
    document.getElementById('twofa-off').style.display = 'block';
}

async function disable2FA() {
    if (!confirm('Disable two-factor authentication? The existing secret will be removed.')) return;
    try {
        const res = await fetch('/users/' + ADMIN_TOKEN + '/2fa/disable', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            toast(data.message, 'success');
            document.getElementById('twofa-on').style.display = 'none';
            document.getElementById('twofa-off').style.display = 'block';
            const badge = document.getElementById('twofaStatusBadge');
            badge.className = 'twofa-status disabled';
            badge.innerHTML = '<i class="fas fa-times-circle"></i> Disabled';
        } else { toast(data.message || 'Failed', 'error'); }
    } catch (err) { toast('Request failed: ' + err.message, 'error'); }
}

document.addEventListener('DOMContentLoaded', function() {
    const otp = document.getElementById('verifyCode');
    if (otp) otp.addEventListener('input', function() { this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6); });
});

function toggleLogDetail(id, rowEl) {
    const detail = document.getElementById('detail-' + id);
    const isVisible = detail.classList.contains('show');
    document.querySelectorAll('.log-detail-row.show').forEach(el => el.classList.remove('show'));
    document.querySelectorAll('tr[id^="row-"]').forEach(el => {
        el.style.background = '';
        const ic = el.querySelector('.fa-chevron-down');
        if (ic) { ic.style.transform = ''; ic.style.color = 'var(--text-faint)'; }
    });
    if (!isVisible) {
        detail.classList.add('show');
        rowEl.style.background = 'var(--border-light)';
        const icon = rowEl.querySelector('.fa-chevron-down');
        if (icon) { icon.style.transform = 'rotate(180deg)'; icon.style.color = 'var(--c-secondary)'; }
    }
}

// ── Permission override toggle ──
document.querySelectorAll('.perm-toggle').forEach(function(toggle) {
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        var cb = this.querySelector('.perm-cb');
        if (!cb) return;
        cb.checked = !cb.checked;
        updatePermToggle(cb);
    });
});

function selectAllGroup(btn) {
    var card = btn.closest('[style*="margin-bottom"]');
    if (!card) return;
    card.querySelectorAll('.perm-cb').forEach(function(cb) { cb.checked = true; updatePermToggle(cb); });
}
function deselectAllGroup(btn) {
    var card = btn.closest('[style*="margin-bottom"]');
    if (!card) return;
    card.querySelectorAll('.perm-cb').forEach(function(cb) { cb.checked = false; updatePermToggle(cb); });
}

function updatePermToggle(cb) {
    var toggle = cb.closest('.perm-toggle');
    var roleDefault = cb.dataset.roleDefault === '1';
    var isOverride = cb.checked !== roleDefault;
    // Remove old classes
    toggle.classList.remove('override-on', 'override-off');
    // Add override class only when different from role
    if (isOverride) {
        toggle.classList.add(cb.checked ? 'override-on' : 'override-off');
    }
    // Update the Override column badge for this row
    var row = cb.closest('tr');
    if (row) {
        var anyOverride = false;
        row.querySelectorAll('.perm-cb').forEach(function(c) {
            if (c.checked !== (c.dataset.roleDefault === '1')) anyOverride = true;
        });
        var overrideCell = row.querySelector('td:last-child');
        if (overrideCell) {
            overrideCell.innerHTML = anyOverride
                ? '<span class="badge" style="background:var(--c-warning-light);color:var(--c-warning);font-size:12px"><i class="fas fa-user-edit"></i> Custom</span>'
                : '<span style="font-size:12px;color:var(--text-faint)">Role default</span>';
        }
    }
}

async function savePerms() {
    var overrides = {};
    document.querySelectorAll('.perm-cb').forEach(function(cb) {
        var menuId = cb.dataset.menu;
        var perm = cb.dataset.perm;
        var roleDefault = cb.dataset.roleDefault === '1';
        var current = cb.checked;
        if (current !== roleDefault) {
            if (!overrides[menuId]) overrides[menuId] = {};
            overrides[menuId][perm] = current ? 1 : 0;
        }
    });

    try {
        var res = await fetch('/users/' + ADMIN_TOKEN + '/permissions', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ overrides: overrides })
        });
        var data = await res.json();
        if (data.success) { toast(data.message, 'success'); setTimeout(function(){ location.reload(); }, 1000); }
        else { toast(data.message || 'Failed', 'error'); }
    } catch (err) { toast('Request failed: ' + err.message, 'error'); }
}

async function resetPerms() {
    if (!confirm('Reset all permission overrides for this user? They will inherit role defaults.')) return;
    try {
        var res = await fetch('/users/' + ADMIN_TOKEN + '/permissions/reset', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        });
        var data = await res.json();
        if (data.success) { toast(data.message, 'success'); setTimeout(function(){ location.reload(); }, 1000); }
        else { toast(data.message || 'Failed', 'error'); }
    } catch (err) { toast('Request failed: ' + err.message, 'error'); }
}
</script>
@endpush
