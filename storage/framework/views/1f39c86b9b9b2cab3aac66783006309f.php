<?php $__env->startSection('title', 'Login Activity Log'); ?>

<?php $__env->startPush('styles'); ?>
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
.btn-purge { background: #fff; color: var(--c-danger); border: 1.5px solid var(--c-danger-border); }
.btn-purge:hover { background: var(--c-danger-light); border-color: var(--c-danger-border); }
.btn-outline { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--input-border); }
.btn-outline:hover { background: var(--table-header-bg); border-color: var(--text-faint); transform: none; box-shadow: none; }
.btn-sm { padding: 8px 14px; font-size: 13px; }
.btn-icon-kick { width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid var(--c-danger-border); background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; color: var(--c-danger); transition: all .2s; }
.btn-icon-kick:hover { background: var(--c-danger-light); border-color: var(--c-danger); transform: scale(1.08); }

/* ── Alert ── */
.alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
.alert-success { background: var(--c-success-light); color: var(--c-success); border: 1px solid var(--c-success-border); }
.alert-danger { background: var(--c-danger-light); color: var(--c-primary-hover); border: 1px solid var(--c-danger-border); }

/* ── Stats Row ── */
.stats-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 22px; }
@media(max-width:1200px) { .stats-row { grid-template-columns: repeat(3, 1fr); } }
@media(max-width:640px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
.stat-card { background: #fff; border-radius: 12px; padding: 20px 22px; border: 1px solid var(--border-color); transition: all .25s; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 12px 0 0 12px; }
.stat-card.c-blue::before { background: var(--c-secondary); }
.stat-card.c-green::before { background: var(--c-success); }
.stat-card.c-red::before { background: var(--c-danger); }
.stat-card.c-amber::before { background: var(--c-warning); }
.stat-card.c-purple::before { background: var(--c-purple); }
.stat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.07); border-color: var(--hover-border); transform: translateY(-2px); }
.stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.stat-icon.blue { background: linear-gradient(135deg, var(--c-secondary-light), var(--c-secondary-light)); color: var(--c-secondary); }
.stat-icon.green { background: linear-gradient(135deg, var(--c-success-light), var(--c-success-light)); color: var(--c-success); }
.stat-icon.red { background: linear-gradient(135deg, var(--c-danger-light), var(--c-danger-light)); color: var(--c-danger); }
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
.filter-tag.clear-all { background: var(--c-danger-light); color: var(--c-danger); }
.filter-tag.clear-all:hover { background: var(--c-danger-light); }

/* ── Card & Table ── */
.card { background: #fff; border-radius: 14px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-head { padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light); }
.card-title { font-size: 16px; font-weight: 600; color: var(--code-bg); display: flex; align-items: center; gap: 10px; }
.card-title i { color: var(--text-faint); font-size: 15px; }
.card-count { font-size: 14px; color: var(--text-muted); font-weight: 500; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 13px 18px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; background: var(--table-header-bg); border-bottom: 2px solid var(--border-light); white-space: nowrap; }
.data-table td { padding: 14px 18px; font-size: 14px; color: var(--text-body); border-bottom: 1px solid var(--border-light); vertical-align: middle; }
.data-table tbody tr:hover td { background: var(--table-header-bg); }
.data-table tbody tr.expanded-parent td { background: var(--border-light); border-bottom: none; }
.data-table .expand-icon { color: var(--text-faint); font-size: 12px; cursor: pointer; transition: transform .25s; }
.data-table .expanded-parent .expand-icon { transform: rotate(180deg); color: var(--c-secondary); }

/* ── User cell ── */
.user-cell { display: flex; align-items: center; gap: 12px; }
.user-avatar { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
.user-avatar.success { background: linear-gradient(135deg, var(--c-success-light), var(--c-success-light)); color: var(--c-success); }
.user-avatar.failed { background: linear-gradient(135deg, var(--c-danger-light), var(--c-danger-light)); color: var(--c-danger); }
.user-avatar.active { background: linear-gradient(135deg, var(--c-secondary-light), var(--c-secondary-light)); color: var(--c-secondary); }
.user-name { font-weight: 600; color: var(--code-bg); font-size: 14px; line-height: 1.3; }
.user-meta { font-size: 12px; color: var(--text-faint); display: flex; align-items: center; gap: 6px; margin-top: 1px; }

/* ── Status Badges ── */
.badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; letter-spacing: .2px; }
.badge.green { background: var(--c-success-light); color: var(--c-success); border: 1px solid var(--c-success-border); }
.badge.red { background: var(--c-danger-light); color: var(--c-primary-hover); border: 1px solid var(--c-danger-border); }
.badge.blue { background: var(--c-secondary-light); color: var(--c-secondary); border: 1px solid var(--c-secondary-border); }
.badge.amber { background: var(--c-warning-light); color: var(--c-warning); border: 1px solid var(--c-warning-border); }
.badge.gray { background: var(--hover-bg); color: var(--text-secondary); border: 1px solid var(--border-color); }
.badge.purple { background: var(--c-purple-light); color: var(--c-purple); border: 1px solid var(--c-purple-light); }
.badge-role { padding: 2px 8px; font-size: 11px; border-radius: 6px; font-weight: 600; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
.status-dot.green { background: var(--c-success); }
.status-dot.red { background: var(--c-danger); }
.status-dot.amber { background: var(--c-warning); }
.status-dot.blue { background: var(--c-secondary); }
.status-dot.gray { background: var(--text-faint); }

/* ── IP & Location ── */
.ip-mono { font-family: 'SF Mono', 'Fira Code', monospace; font-size: 13px; color: var(--text-heading); background: var(--table-header-bg); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-light); }
.location-text { font-size: 13px; color: var(--text-secondary); }
.location-text i { color: var(--text-faint); margin-right: 4px; font-size: 11px; }

/* ── Browser/Device ── */
.browser-cell { display: flex; align-items: center; gap: 8px; }
.device-icon-wrap { width: 30px; height: 30px; border-radius: 8px; background: var(--table-header-bg); border: 1px solid var(--border-light); display: flex; align-items: center; justify-content: center; font-size: 14px; color: var(--text-muted); flex-shrink: 0; }
.browser-text { font-size: 13px; color: var(--text-body); font-weight: 500; }

/* ── Login time ── */
.time-date { font-size: 14px; color: var(--code-bg); font-weight: 500; }
.time-clock { font-size: 12px; color: var(--text-faint); margin-top: 2px; }

/* ── Duration ── */
.duration-text { font-size: 13px; color: var(--text-secondary); font-weight: 500; font-family: 'SF Mono', monospace; }
.online-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: linear-gradient(135deg, var(--c-secondary-light), var(--c-secondary-light)); color: var(--c-secondary); border: 1px solid var(--c-secondary-border); }
.online-badge .pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--c-secondary); animation: pulse 1.8s ease-in-out infinite; }

/* ── Detail Row ── */
.detail-row { display: none; }
.detail-row.show { display: table-row; }
.detail-content { padding: 20px 28px 20px 48px; background: linear-gradient(180deg, var(--table-header-bg) 0%, #fff 100%); border-bottom: 2px solid var(--border-color); }
.detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
@media(max-width:900px) { .detail-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width:600px) { .detail-grid { grid-template-columns: 1fr; } }
.detail-item { padding: 12px 16px; background: #fff; border-radius: 10px; border: 1px solid var(--border-light); }
.detail-item label { display: block; font-size: 11px; font-weight: 700; color: var(--text-faint); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
.detail-item span { font-size: 14px; color: var(--code-bg); font-weight: 500; word-break: break-all; }
.detail-item span.mono { font-family: 'SF Mono', 'Fira Code', monospace; font-size: 12px; }
.detail-item span.fail { color: var(--c-danger); font-weight: 600; }
.detail-ua { margin-top: 16px; padding: 14px 18px; background: var(--code-bg); border-radius: 10px; color: var(--text-faint); font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace; font-size: 12px; word-break: break-all; line-height: 1.6; border: 1px solid var(--text-heading); }

/* ── Modal ── */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.show { display: flex; }
.modal { background: #fff; border-radius: 16px; width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-light); }
.modal-header h3 { font-size: 18px; font-weight: 700; color: var(--code-bg); display: flex; align-items: center; gap: 10px; }
.modal-close { width: 32px; height: 32px; border-radius: 8px; background: var(--table-header-bg); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; color: var(--text-muted); transition: all .15s; }
.modal-close:hover { background: var(--c-danger-light); color: var(--c-danger); border-color: var(--c-danger-border); }
.modal-body { padding: 24px; }
.modal-body > p { font-size: 14px; color: var(--text-muted); margin-bottom: 18px; line-height: 1.5; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 18px 24px; border-top: 1px solid var(--border-light); }
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

/* ── Utilities ── */
.text-muted { color: var(--text-faint); }
.text-sm { font-size: 13px; }
.nowrap { white-space: nowrap; }
.empty-state { padding: 70px 20px; text-align: center; }
.empty-state i { font-size: 52px; margin-bottom: 18px; display: block; color: var(--hover-border); }
.empty-state p { font-size: 15px; color: var(--text-muted); }

/* ── Animations ── */
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .25; } }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?></div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?></div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<div class="page-header">
    <div class="page-header-left">
        <h1>Login Activity Log</h1>
        <p>Track all admin login sessions, failed attempts, and active connections</p>
    </div>
    <div class="page-header-right">
        <a href="<?php echo e(route('admin.admin-log.export', request()->query())); ?>" class="btn btn-export">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
        <button class="btn btn-purge" onclick="openPurgeModal()">
            <i class="fas fa-trash-alt"></i> Purge Old
        </button>
    </div>
</div>


<div class="stats-row">
    <div class="stat-card c-blue">
        <div class="stat-top">
            <div class="stat-icon blue"><i class="fas fa-list-ul"></i></div>
        </div>
        <div class="stat-value"><?php echo e(number_format($stats['total'])); ?></div>
        <div class="stat-label">Total Entries</div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-top">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-value"><?php echo e(number_format($stats['success'])); ?></div>
        <div class="stat-label">Successful Logins</div>
    </div>
    <div class="stat-card c-red">
        <div class="stat-top">
            <div class="stat-icon red"><i class="fas fa-shield-alt"></i></div>
        </div>
        <div class="stat-value"><?php echo e(number_format($stats['failed'])); ?></div>
        <div class="stat-label">Failed Attempts</div>
    </div>
    <div class="stat-card c-amber">
        <div class="stat-top">
            <div class="stat-icon amber"><i class="fas fa-bolt"></i></div>
        </div>
        <div class="stat-value"><?php echo e(number_format($stats['active_now'])); ?></div>
        <div class="stat-label">Active Now</div>
    </div>
    <div class="stat-card c-purple">
        <div class="stat-top">
            <div class="stat-icon purple"><i class="fas fa-fingerprint"></i></div>
        </div>
        <div class="stat-value"><?php echo e(number_format($stats['unique_ips'])); ?></div>
        <div class="stat-label">Unique IPs</div>
    </div>
</div>


<div class="filter-panel">
    <div class="filter-toggle <?php echo e(request()->hasAny(['status','admin_id','role_id','ip_address','date_from','date_to','device_type','search']) ? 'open' : ''); ?>" onclick="toggleFilter()">
        <h3><i class="fas fa-sliders-h"></i> Filters</h3>
        <i class="fas fa-chevron-down arrow"></i>
    </div>

    <?php
        $hasFilters = request()->hasAny(['status','admin_id','role_id','ip_address','date_from','date_to','device_type','search']);
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasFilters): ?>
    <div class="active-filters">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('status')): ?>
            <span class="filter-tag"><i class="fas fa-circle" style="font-size:7px;"></i> Status: <?php echo e(ucfirst(str_replace('_', ' ', request('status')))); ?> <a href="<?php echo e(route('admin.admin-log.index', array_merge(request()->except('status'), ['page' => 1]))); ?>">&times;</a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('admin_id')): ?>
            <?php $filterAdmin = $admins->firstWhere('id', request('admin_id')); ?>
            <span class="filter-tag"><i class="fas fa-user" style="font-size:9px;"></i> <?php echo e($filterAdmin?->name ?? '#'.request('admin_id')); ?> <a href="<?php echo e(route('admin.admin-log.index', array_merge(request()->except('admin_id'), ['page' => 1]))); ?>">&times;</a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('role_id')): ?>
            <?php $filterRole = $roles->firstWhere('id', request('role_id')); ?>
            <span class="filter-tag"><i class="fas fa-user-tag" style="font-size:9px;"></i> <?php echo e($filterRole?->name ?? '#'.request('role_id')); ?> <a href="<?php echo e(route('admin.admin-log.index', array_merge(request()->except('role_id'), ['page' => 1]))); ?>">&times;</a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('ip_address')): ?>
            <span class="filter-tag"><i class="fas fa-network-wired" style="font-size:9px;"></i> <?php echo e(request('ip_address')); ?> <a href="<?php echo e(route('admin.admin-log.index', array_merge(request()->except('ip_address'), ['page' => 1]))); ?>">&times;</a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('date_from')): ?>
            <span class="filter-tag"><i class="fas fa-calendar" style="font-size:9px;"></i> From: <?php echo e(request('date_from')); ?> <a href="<?php echo e(route('admin.admin-log.index', array_merge(request()->except('date_from'), ['page' => 1]))); ?>">&times;</a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('date_to')): ?>
            <span class="filter-tag"><i class="fas fa-calendar" style="font-size:9px;"></i> To: <?php echo e(request('date_to')); ?> <a href="<?php echo e(route('admin.admin-log.index', array_merge(request()->except('date_to'), ['page' => 1]))); ?>">&times;</a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('device_type')): ?>
            <span class="filter-tag"><i class="fas fa-desktop" style="font-size:9px;"></i> <?php echo e(ucfirst(request('device_type'))); ?> <a href="<?php echo e(route('admin.admin-log.index', array_merge(request()->except('device_type'), ['page' => 1]))); ?>">&times;</a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('search')): ?>
            <span class="filter-tag"><i class="fas fa-search" style="font-size:9px;"></i> "<?php echo e(request('search')); ?>" <a href="<?php echo e(route('admin.admin-log.index', array_merge(request()->except('search'), ['page' => 1]))); ?>">&times;</a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <a href="<?php echo e(route('admin.admin-log.index')); ?>" class="filter-tag clear-all" style="text-decoration:none;">Clear All &times;</a>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="filter-body <?php echo e($hasFilters ? 'show' : ''); ?>">
        <form method="GET" action="<?php echo e(route('admin.admin-log.index')); ?>">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="filter-group">
                    <label>Admin User</label>
                    <select name="admin_id">
                        <option value="">All Users</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($a->id); ?>" <?php echo e(request('admin_id') == $a->id ? 'selected' : ''); ?>><?php echo e($a->name); ?> (<?php echo e($a->username); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Role</label>
                    <select name="role_id">
                        <option value="">All Roles</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r->id); ?>" <?php echo e(request('role_id') == $r->id ? 'selected' : ''); ?>><?php echo e($r->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="success" <?php echo e(request('status') == 'success' ? 'selected' : ''); ?>>Success</option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active Now</option>
                        <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>>Expired</option>
                        <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>All Failed</option>
                        <option value="failed_password" <?php echo e(request('status') == 'failed_password' ? 'selected' : ''); ?>>Wrong Password</option>
                        <option value="failed_not_found" <?php echo e(request('status') == 'failed_not_found' ? 'selected' : ''); ?>>User Not Found</option>
                        <option value="failed_inactive" <?php echo e(request('status') == 'failed_inactive' ? 'selected' : ''); ?>>Inactive Account</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Device Type</label>
                    <select name="device_type">
                        <option value="">All Devices</option>
                        <option value="desktop" <?php echo e(request('device_type') == 'desktop' ? 'selected' : ''); ?>>Desktop</option>
                        <option value="mobile" <?php echo e(request('device_type') == 'mobile' ? 'selected' : ''); ?>>Mobile</option>
                        <option value="tablet" <?php echo e(request('device_type') == 'tablet' ? 'selected' : ''); ?>>Tablet</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>IP Address</label>
                    <input type="text" name="ip_address" value="<?php echo e(request('ip_address')); ?>" placeholder="e.g. 192.168.1">
                </div>
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Name, username, IP, country...">
                </div>
            </div>
            <div class="filter-actions">
                <a href="<?php echo e(route('admin.admin-log.index')); ?>" class="btn btn-outline btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Apply Filters</button>
            </div>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-head">
        <span class="card-title"><i class="fas fa-stream"></i> Login Sessions</span>
        <span class="card-count"><?php echo e($logs->total()); ?> records</span>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->isEmpty()): ?>
        <div class="empty-state">
            <i class="fas fa-user-shield"></i>
            <p>No login activity found matching your filters.</p>
        </div>
    <?php else: ?>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="main-row" onclick="toggleDetail(<?php echo e($log->id); ?>, this)" style="cursor:pointer;">
                    <td><i class="fas fa-chevron-down expand-icon"></i></td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar <?php echo e($log->isFailed() ? 'failed' : ($log->status === 'active' ? 'active' : 'success')); ?>">
                                <?php echo e($log->admin_name ? strtoupper(substr($log->admin_name, 0, 1)) : '?'); ?>

                            </div>
                            <div>
                                <div class="user-name"><?php echo e($log->admin_name ?? '—'); ?></div>
                                <div class="user-meta">
                                    <?php echo e($log->admin_username ?? 'unknown'); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->role_name): ?>
                                        <span style="color:var(--input-border);">·</span>
                                        <span class="badge-role <?php echo e($log->role_name === 'Administrator' ? 'badge purple' : ($log->role_name === 'Supervisor' ? 'badge amber' : 'badge gray')); ?>" style="padding:2px 8px;font-size:10px;"><?php echo e($log->role_name); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($log->status):
                            case ('active'): ?>
                                <span class="badge blue"><span class="status-dot blue"></span> Active</span>
                                <?php break; ?>
                            <?php case ('success'): ?>
                                <span class="badge green"><span class="status-dot green"></span> Success</span>
                                <?php break; ?>
                            <?php case ('expired'): ?>
                                <span class="badge gray"><span class="status-dot gray"></span> Expired</span>
                                <?php break; ?>
                            <?php case ('failed_password'): ?>
                                <span class="badge red"><span class="status-dot red"></span> Wrong Pass</span>
                                <?php break; ?>
                            <?php case ('failed_not_found'): ?>
                                <span class="badge red"><span class="status-dot red"></span> Not Found</span>
                                <?php break; ?>
                            <?php case ('failed_inactive'): ?>
                                <span class="badge amber"><span class="status-dot amber"></span> Inactive</span>
                                <?php break; ?>
                            <?php default: ?>
                                <span class="badge gray"><?php echo e($log->status); ?></span>
                        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td>
                        <span class="ip-mono"><?php echo e($log->ip_address); ?></span>
                    </td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->ip_country): ?>
                            <span class="location-text"><i class="fas fa-map-marker-alt"></i><?php echo e($log->ip_city ?? ''); ?><?php echo e($log->ip_city && $log->ip_country ? ', ' : ''); ?><?php echo e($log->ip_country); ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td>
                        <div class="browser-cell">
                            <div class="device-icon-wrap">
                                <i class="fas fa-<?php echo e($log->device_type === 'mobile' ? 'mobile-alt' : ($log->device_type === 'tablet' ? 'tablet-alt' : 'desktop')); ?>"></i>
                            </div>
                            <span class="browser-text"><?php echo e($log->browser ?? '—'); ?></span>
                        </div>
                    </td>
                    <td class="nowrap">
                        <div class="time-date"><?php echo e($log->login_at?->format('d M Y')); ?></div>
                        <div class="time-clock"><?php echo e($log->login_at?->format('H:i:s')); ?></div>
                    </td>
                    <td class="nowrap">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->status === 'active'): ?>
                            <span class="online-badge">
                                <span class="pulse-dot"></span> Online
                            </span>
                        <?php elseif($log->formatted_duration): ?>
                            <span class="duration-text"><?php echo e($log->formatted_duration); ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td onclick="event.stopPropagation();">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->status === 'active'): ?>
                            <form action="<?php echo e(route('admin.admin-log.kick', $log->id)); ?>" method="POST" onsubmit="return confirm('Terminate this session for <?php echo e($log->admin_name); ?>?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-icon-kick" title="Kick Session">
                                    <i class="fas fa-power-off"></i>
                                </button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
                
                <tr class="detail-row" id="detail-<?php echo e($log->id); ?>">
                    <td colspan="9">
                        <div class="detail-content">
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Session ID</label>
                                    <span class="mono"><?php echo e($log->session_id); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>IP Address</label>
                                    <span><?php echo e($log->ip_address); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>ISP / Provider</label>
                                    <span><?php echo e($log->ip_isp ?? '—'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Location</label>
                                    <span><?php echo e($log->ip_city ?? '—'); ?><?php echo e($log->ip_city && $log->ip_country ? ', ' : ''); ?><?php echo e($log->ip_country ?? '—'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Platform / OS</label>
                                    <span><?php echo e($log->platform ?? '—'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Browser</label>
                                    <span><?php echo e($log->browser ?? '—'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Device Type</label>
                                    <span><?php echo e(ucfirst($log->device_type)); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Login At</label>
                                    <span><?php echo e($log->login_at?->format('d M Y, H:i:s')); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Logout At</label>
                                    <span><?php echo e($log->logout_at?->format('d M Y, H:i:s') ?? ($log->status === 'active' ? '— (still active)' : '—')); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Duration</label>
                                    <span><?php echo e($log->formatted_duration ?? ($log->status === 'active' ? 'Ongoing' : '—')); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Logout Type</label>
                                    <span><?php echo e($log->logout_type ? ucfirst($log->logout_type) : '—'); ?></span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->fail_reason): ?>
                                <div class="detail-item">
                                    <label>Fail Reason</label>
                                    <span class="fail"><?php echo e($log->fail_reason); ?></span>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->user_agent): ?>
                            <div class="detail-ua"><?php echo e($log->user_agent); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->hasPages()): ?>
    <div class="pagination-wrap">
        <div class="pagination-info">
            Showing <?php echo e($logs->firstItem()); ?>–<?php echo e($logs->lastItem()); ?> of <?php echo e($logs->total()); ?>

        </div>
        <div class="pagination-links">
            <?php echo e($logs->links('pagination::simple-bootstrap-4')); ?>

        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<div class="modal-overlay" id="purgeModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-trash-alt" style="color:var(--c-danger);"></i> Purge Old Logs</h3>
            <button class="modal-close" onclick="closePurgeModal()">&times;</button>
        </div>
        <form action="<?php echo e(route('admin.admin-log.purge')); ?>" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
            <?php echo csrf_field(); ?>
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mybusiness/office.mybusiness.com.my/resources/views/admin/pages/admin-log/index.blade.php ENDPATH**/ ?>