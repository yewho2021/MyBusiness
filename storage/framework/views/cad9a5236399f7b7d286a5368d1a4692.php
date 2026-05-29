<?php
    $__cfg = \App\Models\Configuration::getAll();
    $__fontUrl = \App\Models\Configuration::googleFontUrl();
    $__portalName = $__cfg['portal_name'] ?? 'Admin Portal';
    $__fontSource = $__cfg['font_source'] ?? 'google';
    $__faSource = $__cfg['fontawesome_source'] ?? 'cdn';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - <?php echo e($__portalName); ?></title>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($__cfg['favicon'])): ?>
    <link rel="icon" href="<?php echo e(asset($__cfg['favicon'])); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($__fontSource === 'local'): ?>
        <link href="<?php echo e(asset('vendor/fonts/fonts.css')); ?>" rel="stylesheet">
    <?php else: ?>
        <link href="<?php echo e($__fontUrl); ?>" rel="stylesheet">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($__faSource === 'local'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('vendor/fontawesome/css/all.min.css')); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <style>
    /* SyncComponent (sc-) — Centralised Component CSS */
    .sc-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:var(--btn-radius);font-size:var(--fs-base);font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:background .15s,opacity .15s;font-family:inherit;line-height:1.4}.sc-btn:disabled{opacity:.5;cursor:not-allowed}.sc-btn--primary{background:var(--c-primary);color:var(--card-bg)}.sc-btn--primary:hover:not(:disabled){background:var(--c-primary-hover)}.sc-btn--secondary{background:var(--border-color);color:var(--text-secondary)}.sc-btn--secondary:hover:not(:disabled){background:var(--hover-border)}.sc-btn--danger{background:var(--c-danger);color:var(--card-bg)}.sc-btn--danger:hover:not(:disabled){background:var(--c-primary-hover)}.sc-btn--success{background:var(--c-success);color:var(--card-bg)}.sc-btn--success:hover:not(:disabled){opacity:.9}.sc-btn--outline{background:transparent;border:1px solid var(--border-color);color:var(--text-body)}.sc-btn--outline:hover:not(:disabled){background:var(--hover-bg)}.sc-btn--ghost{background:transparent;color:var(--text-secondary);padding:8px 12px}.sc-btn--ghost:hover:not(:disabled){background:var(--hover-bg)}.sc-btn--sm{padding:6px 14px;font-size:var(--fs-sm)}.sc-btn--xs{padding:4px 10px;font-size:var(--fs-xs)}.sc-btn--lg{padding:12px 24px;font-size:var(--fs-lg)}.sc-btn--icon{width:32px;height:32px;padding:0;border-radius:6px;justify-content:center;font-size:14px}.sc-btn--icon.sc-btn--edit{background:var(--c-secondary-light);color:var(--c-secondary)}.sc-btn--icon.sc-btn--edit:hover{background:var(--c-info-border)}.sc-btn--icon.sc-btn--toggle{background:var(--c-warning-light);color:var(--c-warning)}.sc-btn--icon.sc-btn--toggle:hover{background:var(--c-warning-border)}.sc-btn--icon.sc-btn--delete{background:var(--c-danger-light);color:var(--c-danger)}.sc-btn--icon.sc-btn--delete:hover{background:var(--c-danger-border)}
    .sc-card{background:var(--card-bg);border-radius:var(--card-radius);border:1px solid var(--card-border);overflow:hidden}.sc-card-head{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--border-light)}.sc-card-title{font-size:var(--fs-lg);font-weight:600;color:var(--text-primary);margin:0}.sc-card-actions{display:flex;gap:8px;align-items:center}.sc-card-body{padding:20px}.sc-card-body.sc-no-pad{padding:0}.sc-card-footer{padding:16px 20px;border-top:1px solid var(--border-light);display:flex;justify-content:flex-end;gap:12px}
    .sc-modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:var(--modal-backdrop);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center}.sc-modal-overlay.show{display:flex}.sc-modal{background:var(--card-bg);border-radius:var(--card-radius);width:100%;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-md)}.sc-modal--sm{max-width:420px}.sc-modal--md{max-width:560px}.sc-modal--lg{max-width:720px}.sc-modal--xl{max-width:900px}.sc-modal-header{display:flex;justify-content:space-between;align-items:center;padding:18px 22px;border-bottom:1px solid var(--border-color)}.sc-modal-header h3{font-size:18px;font-weight:600;color:var(--text-primary);margin:0}.sc-modal-close{width:32px;height:32px;border-radius:6px;border:1px solid var(--border-color);background:var(--hover-bg);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted);transition:background .15s;font-size:14px}.sc-modal-close:hover{background:var(--c-danger-light);color:var(--c-danger);border-color:var(--c-danger-border)}.sc-modal-body{padding:22px}.sc-modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px;border-top:1px solid var(--border-color);background:var(--hover-bg)}
    .sc-form-group{margin-bottom:16px}.sc-label{display:block;font-size:var(--fs-base);font-weight:500;color:var(--text-body);margin-bottom:6px}.sc-required{color:var(--c-primary);margin-left:2px}.sc-input,.sc-select,.sc-textarea{width:100%;padding:10px 14px;border:1.5px solid var(--input-border);border-radius:var(--input-radius);font-size:var(--fs-base);font-family:inherit;background:var(--card-bg);color:var(--text-primary);transition:border-color .15s,box-shadow .15s;box-sizing:border-box}.sc-input:focus,.sc-select:focus,.sc-textarea:focus{outline:none;border-color:var(--c-secondary);box-shadow:0 0 0 3px var(--focus-ring)}.sc-input[readonly],.sc-input:read-only,.sc-select[readonly],.sc-textarea[readonly]{background:var(--hover-bg);color:var(--text-muted);cursor:not-allowed}.sc-form-help{font-size:var(--fs-xs);color:var(--text-placeholder);margin-top:4px}.sc-form-error{font-size:var(--fs-xs);color:var(--c-danger);margin-top:4px}.sc-checkbox-label{display:flex;align-items:center;gap:8px;cursor:pointer;font-size:var(--fs-base);color:var(--text-body)}.sc-checkbox-label input[type="checkbox"]{width:18px;height:18px;accent-color:var(--c-primary)}
    .sc-table{width:100%;border-collapse:collapse}.sc-table th,.sc-table td{padding:14px 16px;text-align:left;border-bottom:1px solid var(--border-light)}.sc-table th{background:var(--table-header-bg);font-weight:600;font-size:var(--fs-sm);color:var(--text-muted);text-transform:uppercase;border-bottom:2px solid var(--border-color)}.sc-table td{font-size:var(--fs-base);color:var(--text-body)}.sc-table tbody tr:hover{background:var(--hover-bg)}.sc-table code{background:var(--border-light);padding:2px 8px;border-radius:4px;font-size:var(--fs-sm);color:var(--text-secondary);font-family:var(--font-mono)}.sc-table .text-center{text-align:center}
    .sc-alert{padding:12px 16px;border-radius:var(--btn-radius);margin-bottom:20px;font-size:var(--fs-base);display:flex;align-items:center;justify-content:space-between;gap:12px}.sc-alert-content{flex:1}.sc-alert-close{background:none;border:none;cursor:pointer;color:inherit;opacity:.6;font-size:16px;padding:4px}.sc-alert-close:hover{opacity:1}.sc-alert--success{background:var(--c-success-light);color:var(--c-success);border:1px solid var(--c-success-border)}.sc-alert--danger{background:var(--c-danger-light);color:var(--c-danger);border:1px solid var(--c-danger-border)}.sc-alert--warning{background:var(--c-warning-light);color:var(--c-warning);border:1px solid var(--c-warning-border)}.sc-alert--info{background:var(--c-info-light);color:var(--c-info);border:1px solid var(--c-info-border)}
    .sc-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:var(--fs-xs);font-weight:500;line-height:1.4}.sc-badge--active,.sc-badge--success{background:var(--c-success-light);color:var(--c-success)}.sc-badge--inactive,.sc-badge--danger{background:var(--c-danger-light);color:var(--c-danger)}.sc-badge--warning{background:var(--c-warning-light);color:var(--c-warning)}.sc-badge--info{background:var(--c-info-light);color:var(--c-info)}.sc-badge--purple,.sc-badge--administrator{background:var(--c-purple-light);color:var(--c-purple)}.sc-badge--supervisor{background:var(--c-warning-light);color:var(--c-warning)}.sc-badge--staff{background:var(--c-success-light);color:var(--c-success)}.sc-badge--default{background:var(--border-light);color:var(--text-muted)}
    .sc-stat-card{background:var(--card-bg);border-radius:var(--card-radius);border:1px solid var(--card-border);padding:20px}.sc-stat-icon{font-size:20px;margin-bottom:8px}.sc-stat-value{font-size:28px;font-weight:700;color:var(--text-primary);line-height:1.2}.sc-stat-label{font-size:var(--fs-sm);color:var(--text-muted);margin-top:4px}.sc-stat--primary .sc-stat-icon{color:var(--c-primary)}.sc-stat--success .sc-stat-icon{color:var(--c-success)}.sc-stat--info .sc-stat-icon{color:var(--c-info)}.sc-stat--warning .sc-stat-icon{color:var(--c-warning)}
    .sc-page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px}.sc-page-title{font-size:24px;font-weight:600;color:var(--text-primary);margin:0}.sc-page-subtitle{font-size:var(--fs-base);color:var(--text-muted);margin-top:4px}
    .sc-actions{display:flex;gap:8px}.sc-text-center{text-align:center}.sc-text-muted{color:var(--text-muted)}.sc-mt-0{margin-top:0}.sc-mb-0{margin-bottom:0}.sc-overflow-x{overflow-x:auto;-webkit-overflow-scrolling:touch}
    .modal-overlay,.fm-modal-overlay,.sp-modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:var(--modal-backdrop);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center}.modal-overlay.show,.fm-modal-overlay.show,.sp-modal-overlay.show,.sp-modal-overlay.open{display:flex}.modal,.modal-box,.fm-modal,.sp-modal{background:var(--card-bg);border-radius:var(--card-radius);width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-md)}.modal.modal-lg{max-width:720px}.modal.modal-sm,.fm-modal{max-width:420px}.modal-header,.modal-head,.fm-modal-head{display:flex;justify-content:space-between;align-items:center;padding:18px 22px;border-bottom:1px solid var(--border-color)}.modal-header h3,.modal-head h3,.modal-title{font-size:17px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:10px;margin:0}.modal-close,.fm-modal-close,.sp-modal-close{width:32px;height:32px;border-radius:8px;background:var(--hover-bg);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;color:var(--text-muted);transition:all .15s}.modal-close:hover,.fm-modal-close:hover,.sp-modal-close:hover{background:var(--c-danger-light);color:var(--c-danger);border-color:var(--c-danger-border)}.modal-body,.fm-modal-body,.sp-modal-body{padding:22px}.modal-footer,.modal-foot,.fm-modal-foot,.sp-modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px;border-top:1px solid var(--border-color);background:var(--hover-bg)}
    .sc-card .dataTables_wrapper{font-size:var(--fs-base)}.sc-card .dataTables_filter input{padding:8px 14px;border:1.5px solid var(--border-color);border-radius:var(--input-radius);font-size:var(--fs-sm);outline:none}.sc-card .dataTables_filter input:focus{border-color:var(--c-secondary);box-shadow:0 0 0 3px var(--focus-ring)}.sc-card .dataTables_length select{padding:6px 10px;border:1.5px solid var(--border-color);border-radius:6px;font-size:var(--fs-sm)}.sc-card .dataTables_info{font-size:var(--fs-sm);color:var(--text-muted);padding:12px 0}.sc-card .dataTables_paginate .paginate_button{padding:6px 12px;border-radius:6px;font-size:var(--fs-sm);border:1px solid var(--border-color)!important;margin:0 2px}.sc-card .dataTables_paginate .paginate_button.current{background:var(--c-primary)!important;color:var(--card-bg)!important;border-color:var(--c-primary)!important}.sc-card .dataTables_paginate .paginate_button:hover{background:var(--hover-bg)!important;border-color:var(--hover-border)!important}.sc-card table.dataTable thead th{background:var(--table-header-bg);font-weight:600;font-size:var(--fs-xs);color:var(--text-muted);text-transform:uppercase;padding:14px 16px;border-bottom:2px solid var(--border-color)}.sc-card table.dataTable tbody td{padding:14px 16px;border-bottom:1px solid var(--border-light);vertical-align:middle}.sc-card table.dataTable tbody tr:hover{background:var(--hover-bg)!important}.sc-card table.dataTable.no-footer{border-bottom:none}
    @media(max-width:768px){.sc-page-header{flex-direction:column;align-items:flex-start}.sc-modal--md,.sc-modal--lg,.sc-modal--xl,.modal,.modal-box,.fm-modal,.sp-modal{max-width:95vw!important}.sc-btn{padding:8px 14px;font-size:var(--fs-sm)}.sc-card-head{flex-direction:column;align-items:flex-start;gap:10px}.sc-stat-value{font-size:22px}}
    </style>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            <?php echo \App\Models\Configuration::cssVariables(); ?>

        }
        body { font-family: var(--font-family, 'Inter', sans-serif); background: var(--body-bg, var(--border-light)); min-height: 100vh; font-size: var(--fs-base, 14px); }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            overflow-y: auto;
            transition: transform .3s ease;
        }
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left .3s ease;
        }
        .header {
            height: var(--header-height);
            background: var(--header-bg, #fff);
            border-bottom: 1px solid var(--header-border, var(--border-color));
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--content-padding, 24px);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .main-content { flex: 1; padding: var(--content-padding, 24px); }
        .footer {
            height: 50px;
            background: var(--card-bg, #fff);
            border-top: 1px solid var(--border-color, var(--border-color));
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--content-padding, 24px);
            font-size: var(--fs-sm, 13px);
            color: var(--text-muted);
        }

        /* Hamburger */
        .hamburger { display:none; background:none; border:none; color:var(--header-text,var(--text-heading)); font-size:20px; cursor:pointer; padding:8px; border-radius:8px; margin-right:12px; }
        .hamburger:hover { background:var(--border-light); }

        /* Overlay */
        .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(15,23,42,.5); backdrop-filter:blur(2px); z-index:999; }
        .sidebar-overlay.show { display:block; }

        /* Sidebar close btn (mobile only) */
        .sidebar-close { display:none; position:absolute; top:16px; right:16px; background:rgba(255,255,255,.1); border:none; color:#fff; width:32px; height:32px; border-radius:8px; cursor:pointer; font-size:14px; z-index:10; }
        .sidebar-close:hover { background:rgba(255,255,255,.2); }

        /* ── RESPONSIVE: Tablet (≤1024px) ────────────── */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-close { display:flex; align-items:center; justify-content:center; }
            .main-wrapper { margin-left: 0; }
            .hamburger { display:inline-flex; }
            .main-content { padding: 16px; }
            .header { padding: 0 16px; }
            .footer { padding: 0 16px; }

            /* Database Query: sidebar+main layout */
            .db-container { flex-direction:column !important; height:auto !important; }
            .db-sidebar { width:100% !important; min-width:100% !important; max-height:250px; border-right:none !important; border-bottom:1px solid var(--border-color); }
            .db-main { min-width:0 !important; }

            /* File Manager: full-width on tablet */
            .fm-wrapper { overflow:visible !important; }
            #fm-main-block .fm-body > .row { flex-direction:column !important; }
            #fm-main-block .fm-body > .row > [class*="col"] { width:100% !important; max-width:100% !important; flex:none !important; }

            /* Stats grids: 2 cols */
            .stats-row, .stat-grid, .stats-grid { grid-template-columns:repeat(2,1fr) !important; }
        }

        @media (max-width: 640px) {
            /* Stats: single col on small screens */
            .stats-row, .stat-grid, .stats-grid { grid-template-columns:1fr !important; }
        }

        /* ── Global: Mobile table overflow ────────────────────── */
        table { width:100%; }
        .table-wrap, .card > div:has(> table), .sp-card > div:has(> table) {
            overflow-x:auto; -webkit-overflow-scrolling:touch;
        }
        @media (max-width: 768px) {
            table { min-width:600px; }
            .card, .sp-card { overflow-x:auto; }
        }

        /* ── Skip to content (accessibility) ──────────────────── */
        .skip-to-content { position:absolute; left:-9999px; top:4px; z-index:99999; background:var(--c-primary); color:#fff; padding:8px 16px; border-radius:var(--btn-radius); font-size:var(--fs-sm); }
        .skip-to-content:focus { left:4px; }

        /* ── RESPONSIVE: Mobile (≤768px) ─────────────── */
        @media (max-width: 768px) {
            .main-content { padding: 12px; }
            .header { padding: 0 12px; gap:8px; }
            .footer { padding: 0 12px; font-size:11px; flex-direction:column; height:auto; padding:10px 12px; gap:2px; }
            .page-title { font-size:15px !important; }

            /* User dropdown: hide text */
            .user-info { display:none !important; }
            .user-btn { padding:6px !important; }

            /* ── Tables ── */
            .table-responsive, .table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
            table:not(.sp-table) { min-width:500px; }
            th, td { padding:8px 10px !important; font-size:12px !important; }

            /* ── Page headers ── */
            .page-header, .cfg-header { flex-direction:column !important; align-items:flex-start !important; gap:10px !important; }
            .page-header h2, .cfg-header h2 { font-size:18px !important; }
            .header-actions, .cfg-header-right { width:100% !important; display:flex !important; flex-wrap:wrap !important; gap:6px !important; }

            /* ── Panels ── */
            .panels { grid-template-columns:1fr !important; }

            /* ── Buttons ── */
            .btn { padding:8px 14px !important; font-size:12px !important; }
            .btn-sm { padding:6px 10px !important; font-size:11px !important; }

            /* ── Cards ── */
            .sp-card, .em-card, .cc-card, .card { padding:14px !important; }

            /* ── Configuration: tabs → horizontal scroll ── */
            .cfg-tabs { overflow-x:auto !important; flex-wrap:nowrap !important; -webkit-overflow-scrolling:touch; padding-bottom:4px; }
            .cfg-tab-btn { white-space:nowrap !important; flex-shrink:0 !important; font-size:12px !important; padding:8px 14px !important; }
            .cfg-sidebar { width:100% !important; min-width:100% !important; }
            .cfg-container { flex-direction:column !important; }
            .cfg-tab { border-left:none !important; white-space:nowrap; }
            .cfg-grid { grid-template-columns:1fr !important; }

            /* ── All modals ── */
            .sp-modal, .modal, .fm-modal { width:95vw !important; max-width:95vw !important; }
            .modal-lg { max-width:95vw !important; }

            /* ── Grids: force 1 col ── */
            .cc-grid, .form-row, .form-grid { grid-template-columns:1fr !important; }

            /* ── Stats: 2 cols then 1 ── */
            .stats-row, .stat-grid, .stats-grid { grid-template-columns:repeat(2,1fr) !important; gap:8px !important; }
            .stat-card { padding:12px !important; }
            .stat-value { font-size:18px !important; }

            /* ── Panel actions: wrap ── */
            .panel-actions { flex-wrap:wrap !important; gap:4px !important; }

            /* ── Forms: full width ── */
            input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="url"],
            input[type="search"], input[type="tel"], select, textarea, .form-control {
                width:100% !important; min-width:0 !important; max-width:100% !important;
            }
            .em-layout input, .em-layout select { width:100% !important; }
            .color-hex-wrap { width:120px !important; }
            .num-input-wrap input { width:70px !important; }

            /* ── System Patch ── */
            .sp-btn-group { flex-direction:column !important; }
            .sp-btn-group .sp-btn { width:100% !important; justify-content:center !important; }
            .sp-stats { flex-direction:column !important; }
            .sp-drop { padding:32px 16px !important; }
            .sp-drop-icon { font-size:32px !important; }
            .sp-drop-text { font-size:13px !important; }
            .sp-table td, .sp-table th { padding:6px 8px !important; font-size:11px !important; }

            /* ── Cache tab ── */
            .cc-grid { grid-template-columns:1fr !important; }
            .cc-summary-strip { flex-direction:column !important; gap:8px !important; }

            /* ── Database Manager ── */
            .db-tools { grid-template-columns:1fr !important; }
            .db-sidebar { max-height:200px; }
            .result-table { font-size:11px !important; }
            .result-table td, .result-table th { padding:6px 8px !important; }
            .db-statusbar { flex-wrap:wrap !important; font-size:11px !important; gap:6px !important; }
            .nav-pills, .nav-pill { flex-wrap:nowrap !important; overflow-x:auto !important; -webkit-overflow-scrolling:touch; }
            .nav-pill { white-space:nowrap !important; flex-shrink:0 !important; font-size:12px !important; }

            /* ── Database table view ── */
            .table-header { flex-direction:column !important; gap:8px !important; }
            .table-info { flex-direction:column !important; gap:4px !important; }
            .filter-row { flex-direction:column !important; gap:6px !important; }
            .filter-row select, .filter-row input { width:100% !important; }

            /* ── File Manager ── */
            .fm-action-bar { flex-wrap:wrap !important; gap:6px !important; }
            .fm-path-display { min-width:0 !important; width:100% !important; font-size:11px !important; }
            .fm-statusbar { flex-wrap:wrap !important; font-size:10px !important; }
            .editor-tabs { overflow-x:auto !important; flex-wrap:nowrap !important; }
            .editor-tab { white-space:nowrap !important; flex-shrink:0 !important; }

            /* ── Menus page ── */
            .menu-item { padding:10px 12px !important; }
            .item-meta { display:none !important; }
            .btn-action { width:28px !important; height:28px !important; }

            /* ── Backup pages ── */
            .backup-grid, .job-grid { grid-template-columns:1fr !important; }
            .backup-header { flex-direction:column !important; gap:8px !important; }
            .backup-actions { width:100% !important; flex-wrap:wrap !important; }
            .info-grid, .info-row { grid-template-columns:1fr !important; }
            .info-row { flex-direction:column !important; gap:4px !important; }
            .info-label { min-width:0 !important; }

            /* ── Changelog ── */
            .changelog-item { padding:14px !important; }
            .changelog-header { flex-direction:column !important; gap:6px !important; }
            .changelog-meta { flex-wrap:wrap !important; gap:6px !important; }
            .changelog-table { min-width:500px; }

            /* ── Permissions ── */
            .perm-table-wrap, .permission-container { overflow-x:auto !important; -webkit-overflow-scrolling:touch; }
            .perm-grid { grid-template-columns:1fr !important; }
            .permission-section { min-width:600px; }

            /* ── Users/Roles ── */
            .user-grid, .role-grid { grid-template-columns:1fr !important; }
            .user-card, .role-card { padding:14px !important; }
            .user-actions, .role-actions, .action-buttons { flex-wrap:wrap !important; gap:4px !important; }
            .user-info-cell .avatar { width:30px !important; height:30px !important; font-size:12px !important; }

            /* ── Database Connections ── */
            .conn-grid { grid-template-columns:1fr !important; }
            .conn-card { padding:14px !important; }

            /* ── Dashboard ── */
            .dash-grid { grid-template-columns:1fr !important; }
            .welcome-card { padding:16px !important; }

            /* ── Charts ── */
            .chart-grid { grid-template-columns:1fr !important; }
            .chart-card { min-height:280px !important; }

            /* ── Admin Log ── */
            .log-filters { flex-direction:column !important; gap:8px !important; }
            .log-filters select, .log-filters input { width:100% !important; }

            /* ── Login page ── */
            .login-card { width:95vw !important; max-width:95vw !important; padding:24px !important; }

            /* ── Log/code textareas ── */
            #ccLog, .sp-guide-pre, #exportLog, .panel-output, textarea[readonly] {
                font-size:10px !important; min-height:150px;
            }

            /* ── File Structure panels ── */
            .panel-header { flex-direction:column !important; gap:8px !important; align-items:flex-start !important; }
            .panel-title { font-size:14px !important; }

            /* ── Generic helpers ── */
            [style*="display:flex"], [style*="display: flex"] { flex-wrap:wrap !important; }
            pre, code { font-size:11px !important; word-break:break-all; }
        }

        /* ── RESPONSIVE: Small mobile (≤480px) ──────── */
        @media (max-width: 480px) {
            .main-content { padding: 8px; }
            .page-title { font-size:14px !important; }
            .sidebar { width:85vw !important; }

            /* Stats: 1 col on tiny screens */
            .stats-row, .stat-grid, .stats-grid { grid-template-columns:1fr !important; }

            /* Even smaller text */
            th, td { font-size:11px !important; padding:6px 8px !important; }
            .btn { font-size:11px !important; padding:6px 12px !important; }

            /* Tab pills: smaller */
            .nav-pill { padding:6px 10px !important; font-size:11px !important; }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($__cfg['custom_css'])): ?>
    <style><?php echo \App\Services\HtmlSanitizer::sanitizeCSS($__cfg['custom_css'] ?? ''); ?></style>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($__cfg['custom_head_html'])): ?>
    <?php echo \App\Services\HtmlSanitizer::sanitizeHeadHtml($__cfg['custom_head_html'] ?? ''); ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</head>
<body>
    <a href="#main-content" class="skip-to-content">Skip to content</a>
    <div class="admin-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>
        <?php echo $__env->make('admin.partials.menu_left', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="main-wrapper">
            <?php echo $__env->make('admin.partials.menu_upper', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <main class="main-content" id="main-content" role="main">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
            <?php echo $__env->make('admin.partials.menu_footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
    <div id="toast-container" aria-live="polite" aria-atomic="true" style="position:fixed;top:16px;right:16px;z-index:99999;"></div>
    <script>
        // Sidebar toggle for mobile
        function openSidebar() {
            document.querySelector('.sidebar').classList.add('show');
            document.getElementById('sidebarOverlay').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            document.querySelector('.sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
            document.body.style.overflow = '';
        }
        document.getElementById('sidebarOverlay').addEventListener('click', closeSidebar);

        // Close sidebar on nav click (mobile)
        document.querySelectorAll('.nav-link:not([onclick]), .nav-sublink').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) closeSidebar();
            });
        });

        // Submenu toggle
        document.querySelectorAll('.nav-link.has-submenu').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                this.classList.toggle('expanded');
                submenu.classList.toggle('show');
            });
        });

        // Wrap bare tables in responsive container
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.main-content table').forEach(function(tbl) {
                if (!tbl.closest('.table-responsive') && !tbl.closest('.table-wrap') && !tbl.closest('.sp-table') && !tbl.closest('pre')) {
                    const wrap = document.createElement('div');
                    wrap.className = 'table-responsive';
                    wrap.style.cssText = 'overflow-x:auto;-webkit-overflow-scrolling:touch;';
                    tbl.parentNode.insertBefore(wrap, tbl);
                    wrap.appendChild(tbl);
                }
            });

            // Auto-add ARIA attributes to all modal overlays
            document.querySelectorAll('.modal-overlay, .fm-modal-overlay, [class*="modal-overlay"]').forEach(function(m) {
                m.setAttribute('role', 'dialog');
                m.setAttribute('aria-modal', 'true');
                var title = m.querySelector('.modal-title, .modal-head h3, .modal-head span');
                if (title) {
                    var id = 'modal-title-' + Math.random().toString(36).substr(2,6);
                    title.id = id;
                    m.setAttribute('aria-labelledby', id);
                }
            });

            // Auto-add aria-label to icon-only buttons (no text content)
            document.querySelectorAll('button, a.btn, .tg-btn, .conn-btn, .tb-btn').forEach(function(btn) {
                if (!btn.getAttribute('aria-label') && !btn.textContent.trim() && btn.querySelector('i')) {
                    var icon = btn.querySelector('i');
                    var title = btn.getAttribute('title');
                    if (title) btn.setAttribute('aria-label', title);
                }
            });
        });
    </script>
    
    <script>
    window.scOpenModal=function(id){var el=document.getElementById(id);if(el){el.classList.add('show');document.body.style.overflow='hidden'}};
    window.scCloseModal=function(id){var el=document.getElementById(id);if(el){el.classList.remove('show');document.body.style.overflow=''}};
    window.scCloseAllModals=function(){document.querySelectorAll('.sc-modal-overlay.show').forEach(function(m){m.classList.remove('show')});document.body.style.overflow=''};
    window.scConfirmDelete=function(modalId,actionUrl){var form=document.getElementById(modalId+'_form');if(form)form.action=actionUrl;scOpenModal(modalId)};
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/mybusiness/office.mybusiness.com.my/resources/views/admin/layouts/app.blade.php ENDPATH**/ ?>