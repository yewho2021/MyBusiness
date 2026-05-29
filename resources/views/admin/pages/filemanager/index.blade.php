@extends('admin.layouts.app')

@section('title', 'File Manager')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ url('vendor-asset/file-manager/css/file-manager.css') }}">
<style>
/* ═══════════════════════════════════════════════
   FILE MANAGER — World-Class Portal Theme
   ALL colors use CSS vars from Configuration.
   ═══════════════════════════════════════════════ */

/* ── Reset & Wrapper ────────────────────────── */
.fm-wrapper { background:var(--card-bg,#fff); border-radius:var(--card-radius,12px); border:1px solid var(--border-light); overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.06); }

/* ── Action Toolbar ─────────────────────────── */
.fm-action-bar { display:flex; align-items:center; gap:8px; padding:12px 20px; background:var(--card-bg,#fff); border-bottom:1px solid var(--border-light); flex-wrap:wrap; }
.fm-action-bar .bar-title { font-size:15px; font-weight:700; color:var(--text-heading); display:flex; align-items:center; gap:8px; margin-right:12px; }
.fm-action-bar .bar-title i { color:var(--c-secondary); font-size:18px; }
.fm-action-bar .sep { width:1px; height:28px; background:var(--border-light); margin:0 4px; }
.fm-action-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:var(--btn-radius,8px); border:1px solid var(--border-color); background:var(--card-bg,#fff); color:var(--text-body); font-size:13px; font-weight:500; cursor:pointer; transition:all .15s; white-space:nowrap; }
.fm-action-btn:hover { background:var(--hover-bg); border-color:var(--c-secondary); color:var(--c-secondary); }
.fm-action-btn.danger:hover { background:var(--c-danger-light); border-color:var(--c-danger); color:var(--c-danger); }
.fm-action-btn i { font-size:14px; }
.fm-action-btn.primary { background:var(--c-secondary); color:#fff; border-color:var(--c-secondary); }
.fm-action-btn.primary:hover { background:var(--c-primary); border-color:var(--c-primary); }
.fm-path-display { flex:1; min-width:200px; display:flex; align-items:center; gap:8px; padding:8px 14px; background:var(--hover-bg); border:1px solid var(--border-light); border-radius:var(--btn-radius,8px); color:var(--text-body); font-size:13px; font-family:var(--font-mono,'JetBrains Mono',monospace); overflow:hidden; }
.fm-path-display i { color:var(--c-secondary); flex-shrink:0; }
.fm-path-display span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* ── Editor Tabs ────────────────────────────── */
.editor-tabs { display:flex; background:var(--card-bg,#fff); border-bottom:1px solid var(--border-light); padding:0; overflow-x:auto; min-height:42px; }
.editor-tabs::-webkit-scrollbar { height:3px; }
.editor-tabs::-webkit-scrollbar-thumb { background:var(--text-faint); border-radius:3px; }
.editor-tab { display:flex; align-items:center; gap:8px; padding:10px 18px; background:transparent; border:none; border-right:1px solid var(--border-light); cursor:pointer; font-size:13px; color:var(--text-muted); white-space:nowrap; transition:all .15s; position:relative; font-weight:500; }
.editor-tab:hover { background:var(--hover-bg); color:var(--text-heading); }
.editor-tab.active { background:var(--card-bg,#fff); color:var(--text-heading); font-weight:600; }
.editor-tab.active::after { content:''; position:absolute; bottom:0; left:0; right:0; height:2px; background:var(--c-secondary); }
.editor-tab i { font-size:14px; color:var(--text-faint); }
.editor-tab.active i { color:var(--c-secondary); }
.editor-tab .close-tab { width:20px; height:20px; display:flex; align-items:center; justify-content:center; border-radius:4px; opacity:0; color:var(--text-muted); font-size:14px; }
.editor-tab:hover .close-tab { opacity:1; }
.editor-tab .close-tab:hover { background:var(--c-danger-light); color:var(--c-danger); }
.editor-tab .tab-modified { width:8px; height:8px; background:var(--c-warning); border-radius:50%; display:none; }
.editor-tab .tab-modified.show { display:block; }

/* ── Tab Content ────────────────────────────── */
.tab-content-wrapper { display:none; height:calc(100vh - 210px); min-height:500px; background:var(--card-bg,#fff); }
.tab-content-wrapper.active { display:block; }

/* ── Hide package's default toolbar (we use our own) ── */
#fm-main-block .fm-navbar { display:none !important; }

/* ── FM Package — Full Override ─────────────── */
#fm-main-block { height:100% !important; }
#fm-main-block > div, #fm-main-block .fm { height:100% !important; background:var(--card-bg,#fff) !important; }

/* Card header from package */
#fm-main-block .card-header { background:var(--hover-bg) !important; border-bottom:1px solid var(--border-light) !important; padding:8px 16px !important; }
#fm-main-block .card-header .btn-group .btn { background:var(--card-bg,#fff) !important; border:1px solid var(--border-color) !important; color:var(--text-body) !important; border-radius:6px !important; font-size:13px !important; padding:6px 12px !important; display:inline-flex !important; align-items:center !important; }
#fm-main-block .card-header .btn-group .btn:hover,
#fm-main-block .card-header .btn-group .btn.active { background:var(--c-secondary) !important; color:#fff !important; border-color:var(--c-secondary) !important; }

/* Body */
#fm-main-block .fm-body, #fm-main-block .card-body { height:calc(100% - 50px) !important; background:var(--card-bg,#fff) !important; padding:0 !important; }
#fm-main-block .fm-body > .row { height:100% !important; margin:0 !important; }

/* ── Tree Panel (left sidebar) ──────────────── */
#fm-main-block .fm-tree, #fm-main-block .col-auto { background:var(--card-bg,#fff) !important; border-right:1px solid var(--border-light) !important; overflow-y:auto !important; padding:8px 0 !important; width:260px !important; min-width:260px !important; }
#fm-main-block .fm-tree-branch, #fm-main-block .fm-tree a, #fm-main-block .fm-tree span { color:var(--text-body) !important; font-size:13px !important; font-weight:500 !important; }
#fm-main-block .fm-tree-item, #fm-main-block .fm-tree li { padding:8px 16px !important; color:var(--text-body) !important; border-radius:0 !important; transition:all .1s !important; }
#fm-main-block .fm-tree-item:hover, #fm-main-block .fm-tree li:hover { background:var(--hover-bg) !important; }
#fm-main-block .fm-tree .active, #fm-main-block .fm-tree li.active { background:var(--c-secondary-light) !important; color:var(--c-secondary) !important; font-weight:600 !important; border-left:3px solid var(--c-secondary) !important; }
#fm-main-block .fm-tree [class*="fa-folder"] { color:var(--c-warning) !important; margin-right:6px !important; }

/* ── Content Panel (right) ──────────────────── */
#fm-main-block .fm-content, #fm-main-block .col { background:var(--card-bg,#fff) !important; overflow-y:auto !important; }

/* ── Breadcrumb / Disk ──────────────────────── */
#fm-main-block .fm-disk-list, #fm-main-block .fm-breadcrumb, #fm-main-block .fm-info-block { background:var(--card-bg,#fff) !important; border-bottom:1px solid var(--border-light) !important; padding:10px 16px !important; }
#fm-main-block .fm-disk-list .btn { background:var(--card-bg,#fff) !important; border:1px solid var(--border-color) !important; color:var(--text-body) !important; font-size:13px !important; padding:6px 16px !important; border-radius:var(--btn-radius,8px) !important; }
#fm-main-block .fm-disk-list .btn.active, #fm-main-block .fm-disk-list .btn:hover { background:var(--c-secondary) !important; border-color:var(--c-secondary) !important; color:#fff !important; }
#fm-main-block .fm-breadcrumb { background:var(--card-bg,#fff) !important; border-bottom:1px solid var(--border-light) !important; padding:8px 16px !important; }
#fm-main-block .fm-breadcrumb a { color:var(--text-body) !important; font-weight:500 !important; font-size:13px !important; }
#fm-main-block .fm-breadcrumb a:hover { color:var(--c-secondary) !important; }

/* ── File Table ─────────────────────────────── */
#fm-main-block table { background:var(--card-bg,#fff) !important; color:var(--text-body) !important; margin:0 !important; width:100% !important; }
#fm-main-block table thead { background:var(--card-bg,#fff) !important; }
#fm-main-block table th { background:var(--card-bg,#fff) !important; color:var(--text-muted) !important; font-weight:600 !important; font-size:11px !important; text-transform:uppercase !important; letter-spacing:.5px !important; padding:12px 16px !important; border-bottom:1px solid var(--border-light) !important; }
#fm-main-block table td { padding:10px 16px !important; border-bottom:1px solid var(--hover-bg) !important; font-size:14px !important; color:var(--text-body) !important; background:var(--card-bg,#fff) !important; transition:background .1s !important; }
#fm-main-block table tbody tr:hover td { background:var(--hover-bg) !important; }

/* Selected row — subtle highlight instead of garish blue */
#fm-main-block table tbody tr.table-primary td,
#fm-main-block .table-primary { background:var(--c-secondary-light) !important; color:var(--text-heading) !important; }
#fm-main-block table tbody tr.table-primary td:first-child { border-left:3px solid var(--c-secondary) !important; }

/* ── File Icons ─────────────────────────────── */
#fm-main-block [class*="fa-folder"] { color:var(--c-warning) !important; font-size:16px !important; }
#fm-main-block .far.fa-file { color:var(--text-faint) !important; }
#fm-main-block .far.fa-file-code, #fm-main-block .far.fa-file-alt { color:var(--c-secondary) !important; }
#fm-main-block .far.fa-file-image, #fm-main-block .far.fa-file-video { color:var(--c-purple,#7c3aed) !important; }
#fm-main-block .far.fa-file-archive { color:var(--c-success) !important; }
#fm-main-block .far.fa-file-pdf { color:var(--c-danger) !important; }

/* ── Disk badge ─────────────────────────────── */
#fm-main-block .badge, #fm-main-block .fm-disk-list .badge { background:var(--c-secondary) !important; color:#fff !important; border-radius:6px !important; font-size:12px !important; font-weight:600 !important; padding:4px 10px !important; }

/* ── Package toolbar icons (small row) ──────── */
#fm-main-block .fm-toolbar .btn, #fm-main-block .fm-navbar .btn { background:transparent !important; border:1px solid transparent !important; color:var(--text-muted) !important; border-radius:6px !important; padding:6px 8px !important; font-size:14px !important; }
#fm-main-block .fm-toolbar .btn:hover, #fm-main-block .fm-navbar .btn:hover { background:var(--hover-bg) !important; border-color:var(--border-color) !important; color:var(--text-heading) !important; }
#fm-main-block .fm-toolbar .btn.active { background:var(--c-secondary) !important; color:#fff !important; border-color:var(--c-secondary) !important; }

/* ── Grid/thumbnail view ────────────────────── */
#fm-main-block .fm-grid-item, #fm-main-block .fm-content .card { border:1px solid var(--border-light) !important; border-radius:var(--card-radius,10px) !important; background:var(--card-bg,#fff) !important; transition:all .15s !important; }
#fm-main-block .fm-grid-item:hover, #fm-main-block .fm-content .card:hover { border-color:var(--c-secondary) !important; box-shadow:0 2px 8px rgba(0,0,0,.06) !important; }
#fm-main-block .fm-grid-item.active, #fm-main-block .fm-content .card.active { border-color:var(--c-secondary) !important; background:var(--c-secondary-light) !important; }

/* ── Footer ─────────────────────────────────── */
#fm-main-block .fm-info-block.fm-footer, #fm-main-block .card-footer { background:var(--card-bg,#fff) !important; color:var(--text-muted) !important; padding:10px 16px !important; font-size:13px !important; border-top:1px solid var(--border-light) !important; border-bottom:none !important; }

/* ── Context Menu ───────────────────────────── */
#fm-main-block .fm-context-menu, .fm-context-menu, .dropdown-menu { background:var(--card-bg,#fff) !important; border:1px solid var(--border-light) !important; border-radius:var(--card-radius,10px) !important; box-shadow:0 8px 30px rgba(0,0,0,.12) !important; padding:6px !important; }
#fm-main-block .fm-context-menu .list-group-item, .dropdown-item { background:transparent !important; color:var(--text-body) !important; padding:10px 16px !important; font-size:13px !important; border-radius:6px !important; border:none !important; }
#fm-main-block .fm-context-menu .list-group-item:hover, .dropdown-item:hover { background:var(--hover-bg) !important; color:var(--text-heading) !important; }

/* ── Modals (Bootstrap from package) ────────── */
.modal-content { background:var(--card-bg,#fff) !important; border:1px solid var(--border-light) !important; color:var(--text-body) !important; border-radius:var(--card-radius,12px) !important; }
.modal-header { border-bottom:1px solid var(--border-light) !important; padding:18px 22px !important; }
.modal-footer { border-top:1px solid var(--border-light) !important; padding:14px 22px !important; }
.modal-title { color:var(--text-heading) !important; font-weight:600 !important; font-size:16px !important; }
.modal .form-control { background:var(--card-bg,#fff) !important; border:1.5px solid var(--input-border) !important; color:var(--text-body) !important; border-radius:var(--input-radius,8px) !important; padding:10px 14px !important; font-size:14px !important; }
.modal .form-control:focus { border-color:var(--c-secondary) !important; box-shadow:0 0 0 3px var(--focus-ring) !important; }
.modal .btn-primary { background:var(--c-secondary) !important; border:none !important; border-radius:var(--btn-radius,8px) !important; padding:10px 22px !important; font-weight:500 !important; }
.modal .btn-primary:hover { background:var(--c-primary) !important; }
.modal .btn-secondary { background:var(--hover-bg) !important; border:1px solid var(--border-color) !important; color:var(--text-body) !important; border-radius:var(--btn-radius,8px) !important; }

/* ── Scrollbar ──────────────────────────────── */
#fm-main-block ::-webkit-scrollbar { width:6px; height:6px; }
#fm-main-block ::-webkit-scrollbar-track { background:transparent; }
#fm-main-block ::-webkit-scrollbar-thumb { background:var(--border-color); border-radius:3px; }
#fm-main-block ::-webkit-scrollbar-thumb:hover { background:var(--text-faint); }

/* ── Monaco Editor Area ─────────────────────── */
.editor-container { height:100%; display:flex; flex-direction:column; background:var(--card-bg,#fff); }
.editor-toolbar { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; background:var(--hover-bg); border-bottom:1px solid var(--border-light); flex-wrap:wrap; gap:10px; }
.editor-toolbar .file-path { font-size:13px; color:var(--text-muted); display:flex; align-items:center; gap:8px; }
.editor-toolbar .file-path i { color:var(--c-secondary); }
.editor-toolbar .toolbar-actions { display:flex; gap:10px; align-items:center; }
.editor-toolbar .btn-group { display:flex; gap:2px; }
.editor-toolbar .btn { padding:8px 16px; font-size:13px; border-radius:var(--btn-radius,8px); border:none; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all .15s; }
.editor-toolbar .btn-save { background:var(--c-secondary); color:#fff; }
.editor-toolbar .btn-save:hover { background:var(--c-primary); }
.editor-toolbar .btn-secondary { background:var(--card-bg,#fff); color:var(--text-body); border:1px solid var(--border-color); }
.editor-toolbar .btn-secondary:hover { background:var(--hover-bg); color:var(--text-heading); }
.editor-toolbar select { background:var(--card-bg,#fff); color:var(--text-body); border:1px solid var(--border-color); padding:8px 12px; border-radius:var(--btn-radius,8px); font-size:13px; }
.monaco-wrapper { flex:1; min-height:0; }

/* ── Create Modal ───────────────────────────── */
.fm-modal-overlay { position:fixed; inset:0; background:var(--modal-backdrop,rgba(0,0,0,.5)); z-index:9998; display:none; justify-content:center; align-items:center; backdrop-filter:blur(3px); }
.fm-modal-overlay.show { display:flex; }
.fm-modal { background:var(--card-bg,#fff); border-radius:var(--card-radius,12px); width:100%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,.15); }
.fm-modal-head { padding:18px 24px; border-bottom:1px solid var(--border-light); display:flex; justify-content:space-between; align-items:center; }
.fm-modal-head h3 { font-size:16px; font-weight:600; color:var(--text-heading); margin:0; display:flex; align-items:center; gap:8px; }
.fm-modal-head h3 i { color:var(--c-secondary); }
.fm-modal-close { background:none; border:none; font-size:18px; color:var(--text-faint); cursor:pointer; width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; }
.fm-modal-close:hover { background:var(--c-danger-light); color:var(--c-danger); }
.fm-modal-body { padding:24px; }
.fm-modal-body label { display:block; font-size:13px; font-weight:600; color:var(--text-body); margin-bottom:6px; }
.fm-modal-body input { width:100%; padding:10px 14px; border:1.5px solid var(--input-border); border-radius:var(--input-radius,8px); font-size:14px; outline:none; transition:all .2s; }
.fm-modal-body input:focus { border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); }
.fm-modal-body .hint { font-size:12px; color:var(--text-faint); margin-top:6px; }
.fm-modal-foot { padding:14px 24px; border-top:1px solid var(--border-light); display:flex; justify-content:flex-end; gap:8px; }
.fm-modal-foot .btn-cancel { background:var(--hover-bg); color:var(--text-body); border:1px solid var(--border-color); padding:9px 18px; border-radius:var(--btn-radius,8px); font-size:13px; font-weight:500; cursor:pointer; }
.fm-modal-foot .btn-cancel:hover { background:var(--border-light); }
.fm-modal-foot .btn-submit { background:var(--c-secondary); color:#fff; border:none; padding:9px 18px; border-radius:var(--btn-radius,8px); font-size:13px; font-weight:500; cursor:pointer; }
.fm-modal-foot .btn-submit:hover { background:var(--c-primary); }

/* ── Toast ───────────────────────────────────── */
.fm-toast { position:fixed; top:20px; right:20px; padding:14px 20px; border-radius:var(--card-radius,10px); color:#fff; font-size:14px; z-index:9999; animation:slideIn .3s ease; display:flex; align-items:center; gap:10px; box-shadow:0 4px 16px rgba(0,0,0,.15); }
.fm-toast.success { background:var(--c-success); }
.fm-toast.error { background:var(--c-danger); }
.fm-toast.info { background:var(--c-secondary); }
@keyframes slideIn { from { transform:translateX(100%); opacity:0; } to { transform:translateX(0); opacity:1; } }

/* ── Status Bar ─────────────────────────────── */
.fm-statusbar { height:36px; background:var(--hover-bg); display:flex; align-items:center; padding:0 20px; gap:16px; font-size:13px; color:var(--text-muted); border-top:1px solid var(--border-light); }
.fm-statusbar .status-dot { width:7px; height:7px; border-radius:50%; background:var(--c-success); display:inline-block; margin-right:4px; }

/* ── Keyboard Shortcuts ─────────────────────── */
.shortcut-hint { position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:var(--text-heading); color:#fff; padding:12px 24px; border-radius:var(--card-radius,10px); font-size:14px; z-index:9999; display:none; }
.shortcut-hint.show { display:block; }
.shortcuts-help { position:fixed; bottom:10px; right:10px; background:var(--card-bg,#fff); border:1px solid var(--border-light); border-radius:var(--card-radius,8px); padding:8px 14px; font-size:12px; color:var(--text-muted); z-index:50; box-shadow:0 1px 3px rgba(0,0,0,.06); }
.shortcuts-help kbd { background:var(--hover-bg); border:1px solid var(--border-color); border-radius:4px; padding:2px 6px; font-size:11px; color:var(--text-heading); font-weight:600; }

/* ── AGGRESSIVE PACKAGE OVERRIDES ───────────── */
/* Hide the duplicate package toolbar completely */
#fm-main-block .fm-navbar,
#fm-main-block .card-header,
#fm-main-block .fm-header-block,
#fm .fm-navbar,
#fm .card-header,
.fm-body-block > .fm-navbar,
.fm > .card > .card-header { display:none !important; }

/* Hide the blue "home" disk badge bar — redundant with our path display */
#fm-main-block .fm-disk-list,
#fm .fm-disk-list { padding:4px 16px !important; background:var(--card-bg,#fff) !important; border-bottom:1px solid var(--border-light) !important; }
#fm-main-block .fm-disk-list .btn { font-size:12px !important; padding:4px 12px !important; border-radius:6px !important; }

/* Fix the garish blue selected row — the package uses .table-info or .table-primary */
#fm-main-block .table-info,
#fm-main-block .table-info td,
#fm-main-block tr.table-info td,
#fm-main-block .table-primary,
#fm-main-block .table-primary td,
#fm-main-block tr.table-primary td,
.fm-table .table-info td,
.fm-table .table-primary td,
table .table-info td,
table .table-primary td { background:var(--c-secondary-light) !important; color:var(--text-heading) !important; }

/* Blue bar at the top of file list — this is the "up one level" row */
#fm-main-block .fm-content table tbody tr:first-child td,
.fm-table tbody tr:first-child.table-info td { background:var(--hover-bg) !important; }

/* Make tree panel wider and more spacious */
#fm-main-block .fm-tree,
#fm-main-block .col-auto,
.fm-left-col,
.fm > .card > .card-body > .row > .col-auto { width:280px !important; min-width:280px !important; max-width:280px !important; }

/* Tree items — more padding, better spacing */
#fm-main-block .fm-tree ul { padding-left:16px !important; }
#fm-main-block .fm-tree li,
#fm-main-block .fm-tree-item { padding:9px 16px !important; margin:1px 8px !important; border-radius:6px !important; font-size:14px !important; }
#fm-main-block .fm-tree li:hover { background:var(--hover-bg) !important; }
#fm-main-block .fm-tree li.active,
#fm-main-block .fm-tree .active { background:var(--c-secondary-light) !important; color:var(--c-secondary) !important; font-weight:600 !important; border-left:none !important; }

/* Table rows — more padding, better font size */
#fm-main-block table td { padding:12px 18px !important; font-size:14px !important; }
#fm-main-block table th { padding:14px 18px !important; font-size:12px !important; }

/* Folder icons bigger */
#fm-main-block [class*="fa-folder"] { font-size:18px !important; margin-right:8px !important; }
#fm-main-block .far.fa-file,
#fm-main-block [class*="fa-file"] { font-size:16px !important; margin-right:8px !important; }

/* Row hover — subtle */
#fm-main-block table tbody tr { transition:background .1s !important; }
#fm-main-block table tbody tr:hover td { background:var(--hover-bg) !important; }

/* Remove Bootstrap border-radius on card */
#fm-main-block .card { border:none !important; border-radius:0 !important; box-shadow:none !important; }

/* Fix info panel */
#fm-main-block .fm-info-block,
#fm-main-block .fm-detail-info { background:var(--card-bg,#fff) !important; border-left:1px solid var(--border-light) !important; border-bottom:none !important; }

/* Fix the top bar area that shows current path */
#fm-main-block .fm-breadcrumb { padding:10px 18px !important; font-size:14px !important; background:var(--card-bg,#fff) !important; border-bottom:1px solid var(--border-light) !important; }
</style>
@endpush

@section('content')
<div class="fm-wrapper">
    {{-- Action Toolbar --}}
    <div class="fm-action-bar">
        <div class="bar-title"><i class="bi bi-folder2-open"></i> File Manager</div>
        <div class="sep"></div>
        <button class="fm-action-btn" onclick="showCreateModal('file')" title="Create a new file"><i class="bi bi-file-earmark-plus"></i> New File</button>
        <button class="fm-action-btn" onclick="showCreateModal('folder')" title="Create a new folder"><i class="bi bi-folder-plus"></i> New Folder</button>
        <div class="sep"></div>
        <button class="fm-action-btn" onclick="triggerUpload()" title="Upload files"><i class="bi bi-cloud-upload"></i> Upload</button>
        <button class="fm-action-btn" onclick="triggerDownload()" title="Download selected"><i class="bi bi-download"></i> Download</button>
        <div class="sep"></div>
        <button class="fm-action-btn" onclick="triggerDelete()" title="Delete selected"><i class="bi bi-trash3"></i> Delete</button>
        <button class="fm-action-btn" onclick="triggerRename()" title="Rename selected"><i class="bi bi-pencil-square"></i> Rename</button>
        <button class="fm-action-btn" onclick="triggerRefresh()" title="Refresh"><i class="bi bi-arrow-clockwise"></i></button>
        <div class="fm-path-display" id="currentPath"><i class="bi bi-geo-alt"></i> <span>/</span></div>
    </div>

    {{-- Editor Tabs --}}
    <div class="editor-tabs" id="editorTabs">
        <div class="editor-tab active" data-tab="filemanager" onclick="switchTab('filemanager')">
            <i class="bi bi-folder2"></i>
            <span>Explorer</span>
        </div>
    </div>

    {{-- Tab Contents --}}
    <div id="tabContents">
        <div class="tab-content-wrapper active" data-content="filemanager">
            <div id="fm-main-block">
                <div id="fm"></div>
            </div>
        </div>
    </div>

    {{-- Status Bar --}}
    <div class="fm-statusbar">
        <span><span class="status-dot"></span> Ready</span>
        <span id="statusSelection"></span>
        <span style="margin-left:auto"><i class="bi bi-hdd"></i> home</span>
    </div>
</div>

{{-- Create File/Folder Modal --}}
<div class="fm-modal-overlay" id="createModal">
    <div class="fm-modal">
        <div class="fm-modal-head">
            <h3 id="createModalTitle"><i class="bi bi-file-earmark-plus"></i> New File</h3>
            <button class="fm-modal-close" onclick="hideCreateModal()">&times;</button>
        </div>
        <div class="fm-modal-body">
            <label id="createModalLabel">File Name</label>
            <input type="text" id="createInput" placeholder="example.php" autocomplete="off">
            <div class="hint" id="createHint">Will be created in the current directory</div>
        </div>
        <div class="fm-modal-foot">
            <button class="btn-cancel" onclick="hideCreateModal()">Cancel</button>
            <button class="btn-submit" id="createSubmitBtn" onclick="submitCreate()"><i class="bi bi-check2"></i> Create</button>
        </div>
    </div>
</div>

<div class="shortcut-hint" id="shortcutHint"></div>
<div class="shortcuts-help">
    <kbd>Ctrl</kbd>+<kbd>`</kbd> Next Tab |
    <kbd>Ctrl</kbd>+<kbd>S</kbd> Save |
    <kbd>Ctrl</kbd>+<kbd>W</kbd> Close |
    <kbd>Ctrl</kbd>+<kbd>N</kbd> New File
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js"></script>
<script src="{{ url('vendor-asset/file-manager/js/file-manager.js') }}"></script>
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
let openTabs = { filemanager: { type: 'filemanager' } };
let activeTab = 'filemanager';
let editors = {};
let monacoReady = false;
let createType = 'file'; // 'file' or 'folder'

require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs' } });
require(['vs/editor/editor.main'], () => { monacoReady = true; });

// ─── Tab Management ──────────────────────────────
function switchTab(tabId) {
    activeTab = tabId;
    document.querySelectorAll('.editor-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tabId));
    document.querySelectorAll('.tab-content-wrapper').forEach(c => c.classList.toggle('active', c.dataset.content === tabId));
    if (editors[tabId]) setTimeout(() => editors[tabId].layout(), 50);
}

function nextTab() {
    const tabs = Object.keys(openTabs);
    const idx = tabs.indexOf(activeTab);
    switchTab(tabs[(idx + 1) % tabs.length]);
}

function prevTab() {
    const tabs = Object.keys(openTabs);
    const idx = tabs.indexOf(activeTab);
    switchTab(tabs[(idx - 1 + tabs.length) % tabs.length]);
}

function showShortcutHint(msg) {
    const h = document.getElementById('shortcutHint');
    h.textContent = msg;
    h.classList.add('show');
    setTimeout(() => h.classList.remove('show'), 1200);
}

// ─── Language Detection ──────────────────────────
function getLanguageFromPath(p) {
    const ext = p.split('.').pop().toLowerCase();
    const m = {js:'javascript',ts:'typescript',php:'php',html:'html',css:'css',json:'json',xml:'xml',md:'markdown',sql:'sql',py:'python',yaml:'yaml',yml:'yaml',sh:'shell',env:'ini',ini:'ini',txt:'plaintext',vue:'html',jsx:'javascript',tsx:'typescript',scss:'scss',less:'less'};
    if (p.includes('.blade.php')) return 'php';
    if (p.includes('.env')) return 'ini';
    if (p.endsWith('.htaccess') || p.endsWith('.gitignore')) return 'ini';
    return m[ext] || 'plaintext';
}

function generateTabId(d, p) {
    return 'ed-' + Math.abs((d+':'+p).split('').reduce((a,b)=>(((a<<5)-a)+b.charCodeAt(0))|0,0)).toString(36);
}

// ─── Editor Tab Creation ─────────────────────────
function createEditorTab(disk, filePath, fileName, content) {
    const tabId = generateTabId(disk, filePath);
    if (openTabs[tabId]) { switchTab(tabId); return; }

    const lang = getLanguageFromPath(filePath);
    openTabs[tabId] = { type:'editor', title:fileName, path:filePath, disk:disk, modified:false };

    document.getElementById('editorTabs').insertAdjacentHTML('beforeend', `
        <div class="editor-tab" data-tab="${tabId}" onclick="switchTab('${tabId}')">
            <i class="bi bi-file-code"></i>
            <span title="${filePath}">${fileName}</span>
            <span class="tab-modified" id="mod-${tabId}"></span>
            <span class="close-tab" onclick="event.stopPropagation();closeTab('${tabId}')"><i class="bi bi-x"></i></span>
        </div>
    `);

    document.getElementById('tabContents').insertAdjacentHTML('beforeend', `
        <div class="tab-content-wrapper" data-content="${tabId}">
            <div class="editor-container">
                <div class="editor-toolbar">
                    <div class="file-path"><i class="bi bi-file-code"></i> <span>${disk}:/${filePath}</span></div>
                    <div class="toolbar-actions">
                        <select id="lang-${tabId}" onchange="changeLanguage('${tabId}',this.value)">
                            <option value="javascript" ${lang==='javascript'?'selected':''}>JavaScript</option>
                            <option value="typescript" ${lang==='typescript'?'selected':''}>TypeScript</option>
                            <option value="php" ${lang==='php'?'selected':''}>PHP</option>
                            <option value="html" ${lang==='html'?'selected':''}>HTML</option>
                            <option value="css" ${lang==='css'?'selected':''}>CSS</option>
                            <option value="json" ${lang==='json'?'selected':''}>JSON</option>
                            <option value="sql" ${lang==='sql'?'selected':''}>SQL</option>
                            <option value="yaml" ${lang==='yaml'?'selected':''}>YAML</option>
                            <option value="markdown" ${lang==='markdown'?'selected':''}>Markdown</option>
                            <option value="ini" ${lang==='ini'?'selected':''}>INI/Env</option>
                            <option value="plaintext" ${lang==='plaintext'?'selected':''}>Plain Text</option>
                        </select>
                        <div class="btn-group">
                            <button class="btn btn-secondary" onclick="foldAll('${tabId}')" title="Collapse All"><i class="bi bi-arrows-collapse"></i></button>
                            <button class="btn btn-secondary" onclick="unfoldAll('${tabId}')" title="Expand All"><i class="bi bi-arrows-expand"></i></button>
                            <button class="btn btn-secondary" onclick="formatCode('${tabId}')" title="Format"><i class="bi bi-braces"></i></button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-secondary" onclick="reloadFile('${tabId}')" title="Reload"><i class="bi bi-arrow-clockwise"></i></button>
                            <button class="btn btn-save" onclick="saveFile('${tabId}')"><i class="bi bi-check2"></i> Save</button>
                        </div>
                    </div>
                </div>
                <div class="monaco-wrapper" id="monaco-${tabId}"></div>
            </div>
        </div>
    `);

    const init = () => {
        editors[tabId] = monaco.editor.create(document.getElementById('monaco-'+tabId), {
            value: content, language: lang, theme: 'vs-dark',
            automaticLayout: true, fontSize: 15,
            fontFamily: "'Cascadia Code','JetBrains Mono',Consolas,monospace",
            folding: true, showFoldingControls: 'always',
            minimap: { enabled: true, renderCharacters: false },
            scrollBeyondLastLine: false, wordWrap: 'on',
            bracketPairColorization: { enabled: true },
            guides: { bracketPairs: true, indentation: true },
            autoClosingBrackets: 'always', autoClosingQuotes: 'always',
            mouseWheelZoom: true, smoothScrolling: true, padding: { top: 10 }
        });
        editors[tabId].onDidChangeModelContent(() => {
            if (!openTabs[tabId].modified) {
                openTabs[tabId].modified = true;
                document.getElementById('mod-'+tabId)?.classList.add('show');
            }
        });
        editors[tabId].addCommand(monaco.KeyMod.CtrlCmd|monaco.KeyCode.KeyS, ()=>saveFile(tabId));
    };
    monacoReady ? init() : require(['vs/editor/editor.main'], init);
    switchTab(tabId);
}

function changeLanguage(t, l) { if(editors[t]) monaco.editor.setModelLanguage(editors[t].getModel(), l); }
function foldAll(t) { editors[t]?.trigger('fold','editor.foldAll'); }
function unfoldAll(t) { editors[t]?.trigger('unfold','editor.unfoldAll'); }
function formatCode(t) { editors[t]?.trigger('format','editor.action.formatDocument'); }

function closeTab(tabId) {
    if (tabId === 'filemanager') return;
    if (openTabs[tabId]?.modified && !confirm('Unsaved changes. Close anyway?')) return;
    document.querySelector(`.editor-tab[data-tab="${tabId}"]`)?.remove();
    document.querySelector(`.tab-content-wrapper[data-content="${tabId}"]`)?.remove();
    if (editors[tabId]) { editors[tabId].dispose(); delete editors[tabId]; }
    delete openTabs[tabId];
    if (activeTab === tabId) switchTab(Object.keys(openTabs).pop() || 'filemanager');
}

// ─── File Operations ─────────────────────────────
function saveFile(tabId) {
    const tab = openTabs[tabId];
    if (!tab || tab.type !== 'editor') return;
    showShortcutHint('Saving...');
    fetch('/file-manager/update-file', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN, 'X-Requested-With':'XMLHttpRequest' },
        body: JSON.stringify({ disk: tab.disk, path: tab.path, file: editors[tabId].getValue() })
    })
    .then(r => r.json())
    .then(data => {
        if (data.result?.status === 'success' || !data.error) {
            openTabs[tabId].modified = false;
            document.getElementById('mod-'+tabId)?.classList.remove('show');
            toast('File saved!', 'success');
        } else toast('Error: '+(data.error||'Failed'), 'error');
    })
    .catch(e => toast('Error: '+e.message, 'error'));
}

function reloadFile(tabId) {
    const tab = openTabs[tabId];
    if (!tab) return;
    if (tab.modified && !confirm('Unsaved changes. Reload?')) return;
    loadFile(tab.disk, tab.path, tab.title, tabId);
}

function loadFile(disk, path, name, existingTabId = null) {
    fetch(`/file-manager/get-content?disk=${encodeURIComponent(disk)}&path=${encodeURIComponent(path)}`, {
        headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) throw new Error(data.error);
        if (existingTabId && editors[existingTabId]) {
            editors[existingTabId].setValue(data.content);
            openTabs[existingTabId].modified = false;
            document.getElementById('mod-'+existingTabId)?.classList.remove('show');
            toast('Reloaded', 'info');
        } else createEditorTab(disk, path, name, data.content);
    })
    .catch(e => toast('Error: '+e.message, 'error'));
}

// ─── Create File/Folder ──────────────────────────
function showCreateModal(type) {
    createType = type;
    const modal = document.getElementById('createModal');
    const input = document.getElementById('createInput');
    document.getElementById('createModalTitle').innerHTML = type === 'file'
        ? '<i class="bi bi-file-earmark-plus"></i> New File'
        : '<i class="bi bi-folder-plus"></i> New Folder';
    document.getElementById('createModalLabel').textContent = type === 'file' ? 'File Name' : 'Folder Name';
    input.placeholder = type === 'file' ? 'example.php' : 'new-folder';
    input.value = '';
    const dir = getCurrentDirectory();
    document.getElementById('createHint').textContent = 'Will be created in: /' + (dir || '(root)');
    modal.classList.add('show');
    setTimeout(() => input.focus(), 100);
}

function hideCreateModal() { document.getElementById('createModal').classList.remove('show'); }

function submitCreate() {
    const name = document.getElementById('createInput').value.trim();
    if (!name) { toast('Please enter a name', 'error'); return; }

    const store = window.fm?.$store;
    if (!store) { toast('File manager not ready', 'error'); return; }

    const state = store.state.fm;
    const mgr = state[state.activeManager || 'left'];
    const disk = mgr?.selectedDisk || 'home';
    const dir = mgr?.selectedDirectory || '';

    if (createType === 'folder') {
        store.dispatch('fm/createDirectory', { disk, path: dir ? dir + '/' + name : name })
            .then(() => { toast('Folder created: ' + name, 'success'); hideCreateModal(); })
            .catch(e => toast('Error: ' + (e.message || 'Failed'), 'error'));
    } else {
        // Create file via API
        fetch('/file-manager/create-file', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN, 'X-Requested-With':'XMLHttpRequest' },
            body: JSON.stringify({ disk, path: dir ? dir + '/' + name : name })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toast('File created: ' + name, 'success');
                hideCreateModal();
                triggerRefresh();
                // Auto-open in editor
                setTimeout(() => loadFile(disk, dir ? dir + '/' + name : name, name), 500);
            } else toast('Error: ' + (data.message || 'Failed'), 'error');
        })
        .catch(e => toast('Error: ' + e.message, 'error'));
    }
}

// ─── FM Store Helpers ────────────────────────────
function getStore() { return window.fm?.$store; }

function getCurrentDirectory() {
    const store = getStore();
    if (!store) return '';
    const state = store.state.fm;
    const mgr = state[state.activeManager || 'left'];
    return mgr?.selectedDirectory || '';
}

function triggerUpload() {
    const store = getStore();
    if (store) store.commit('fm/modal/setModalState', { modalName: 'UploadModal', show: true });
}

function triggerDelete() {
    const store = getStore();
    if (!store) return;
    const state = store.state.fm;
    const mgr = state[state.activeManager || 'left'];
    if (!mgr?.selected?.files?.length && !mgr?.selected?.directories?.length) {
        toast('Select a file or folder first', 'info');
        return;
    }
    store.dispatch('fm/delete');
}

function triggerRename() {
    const store = getStore();
    if (!store) return;
    const state = store.state.fm;
    const mgr = state[state.activeManager || 'left'];
    if (!mgr?.selected?.files?.length && !mgr?.selected?.directories?.length) {
        toast('Select a file or folder first', 'info');
        return;
    }
    store.commit('fm/modal/setModalState', { modalName: 'RenameModal', show: true });
}

function triggerDownload() {
    const store = getStore();
    if (!store) return;
    store.dispatch('fm/download');
}

function triggerRefresh() {
    const store = getStore();
    if (store) {
        store.dispatch('fm/refreshAll');
        toast('Refreshed', 'info');
    }
}

// ─── Toasts ──────────────────────────────────────
function toast(msg, type = 'info') {
    const t = document.createElement('div');
    t.className = `fm-toast ${type}`;
    t.innerHTML = `<i class="bi bi-${type==='success'?'check-circle':type==='error'?'x-circle':'info-circle'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
}

// ─── Keyboard Shortcuts ──────────────────────────
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && (e.key === '`' || e.code === 'Backquote')) {
        e.preventDefault(); e.shiftKey ? prevTab() : nextTab(); return;
    }
    if (e.ctrlKey && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 's') {
        e.preventDefault();
        if (activeTab !== 'filemanager' && editors[activeTab]) saveFile(activeTab);
        return;
    }
    if (e.ctrlKey && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 'w') {
        e.preventDefault();
        if (activeTab !== 'filemanager') closeTab(activeTab);
        return;
    }
    if (e.ctrlKey && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 'n') {
        e.preventDefault(); showCreateModal('file'); return;
    }
    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'n') {
        e.preventDefault(); showCreateModal('folder'); return;
    }
    if (e.ctrlKey && !e.shiftKey && !e.altKey && e.key >= '1' && e.key <= '9') {
        e.preventDefault();
        const tabs = Object.keys(openTabs); const idx = parseInt(e.key) - 1;
        if (tabs[idx]) switchTab(tabs[idx]);
        return;
    }
    if (e.key === 'Enter' && document.getElementById('createModal').classList.contains('show')) {
        e.preventDefault(); submitCreate();
    }
}, true);

// ─── FM Store Interception ───────────────────────
document.addEventListener('DOMContentLoaded', function() {
    let ready = setInterval(function() {
        if (window.fm?.$store) {
            clearInterval(ready);
            const store = window.fm.$store;
            const origCommit = store.commit.bind(store);

            function getSelectedFilePath(mgr) {
                const sel = mgr?.selected;
                if (!sel?.files?.length) return null;
                const idx = sel.files[0];
                const dir = mgr.selectedDirectory || '';
                if (typeof idx === 'number' && mgr.files?.[idx]) {
                    const fileObj = mgr.files[idx];
                    const name = fileObj.basename || fileObj.filename || fileObj.name || '';
                    return { path: fileObj.path || (dir ? dir + '/' + name : name), name: name };
                }
                const pathStr = String(idx);
                return { path: pathStr, name: pathStr.split('/').pop() };
            }

            function getFileNameFromRow(row) {
                if (!row) return null;
                const cells = row.querySelectorAll('td');
                for (const cell of cells) {
                    const text = cell.textContent.trim();
                    if (text && !text.match(/^\d+(\.\d+)?\s*(B|KB|MB|GB|bytes?)/i) && !text.match(/^\d{4}-\d{2}-\d{2}/)) return text;
                }
                return null;
            }

            let lastOpenedAt = 0;
            function openFileInEditor(disk, filePath, fileName) {
                const now = Date.now();
                if (now - lastOpenedAt < 500) return;
                lastOpenedAt = now;
                loadFile(disk, filePath, fileName);
            }

            // Intercept TextEditModal
            store.commit = function(type, payload, options) {
                if (type === 'fm/modal/setModalState' && payload?.modalName === 'TextEditModal') {
                    const state = store.state.fm;
                    const mgr = state[state.activeManager || 'left'];
                    const disk = mgr?.selectedDisk || 'home';
                    const file = getSelectedFilePath(mgr);
                    if (file) openFileInEditor(disk, file.path, file.name);
                    return;
                }
                return origCommit(type, payload, options);
            };

            // Watch for directory changes to update path display
            store.watch(
                (state) => state.fm?.left?.selectedDirectory,
                (dir) => { document.querySelector('#currentPath span').textContent = '/' + (dir || ''); }
            );

            // Watch selection to update status bar
            store.watch(
                (state) => {
                    const mgr = state.fm?.[state.fm?.activeManager || 'left'];
                    return { files: mgr?.selected?.files?.length || 0, dirs: mgr?.selected?.directories?.length || 0 };
                },
                (sel) => {
                    const total = sel.files + sel.dirs;
                    document.getElementById('statusSelection').textContent = total > 0 ? total + ' selected' : '';
                }
            );

            // Double-click handler
            const textExts = ['php','js','ts','html','htm','css','scss','less','json','xml','md','txt','sql','py','yaml','yml','sh','env','ini','conf','htaccess','gitignore','vue','jsx','tsx','lock','log','blade.php'];
            document.addEventListener('dblclick', function(e) {
                const row = e.target.closest('tr');
                if (!row || row.querySelector('[class*="folder"]')) return;
                const fileName = getFileNameFromRow(row);
                if (!fileName) return;
                if (!textExts.some(ext => fileName.toLowerCase().endsWith('.'+ext) || fileName.toLowerCase().includes('.'+ext+'.'))) return;
                const state = store.state.fm;
                const mgr = state[state.activeManager || 'left'];
                const disk = mgr?.selectedDisk || 'home';
                const dir = mgr?.selectedDirectory || '';
                openFileInEditor(disk, dir ? dir + '/' + fileName : fileName, fileName);
            });
        }
    }, 100);
    setTimeout(() => clearInterval(ready), 10000);
});
</script>
@endpush
