@extends('admin.layouts.app')
@section('title', 'Database Manager')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════
   DATABASE MANAGER — Enhanced UI
   ═══════════════════════════════════════════════ */

/* Layout */
.db-container { display:flex; height:calc(100vh - 120px); gap:0; background:var(--card-bg,#fff); border-radius:var(--card-radius,10px); border:1px solid var(--border-color,var(--border-color)); overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04); }

/* ── Sidebar ────────────────────────────────── */
.db-sidebar { width:270px; min-width:270px; background:var(--hover-bg); border-right:1px solid var(--border-color); display:flex; flex-direction:column; }
.db-sidebar-header { padding:14px; border-bottom:1px solid var(--border-color,var(--border-color)); }
.db-sidebar-title { font-size:13px; font-weight:700; color:var(--text-faint); text-transform:uppercase; letter-spacing:0.6px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; }
.db-sidebar-title .db-badge { font-size:12px; background:var(--border-color); color:var(--text-muted); padding:2px 7px; border-radius:var(--card-radius,10px); font-weight:600; }

.db-tools { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px; }
.tool-btn { background:var(--card-bg,#fff); color:var(--header-text,var(--text-heading)); border:1.5px solid var(--border-color,var(--border-color)); padding:14px 12px; border-radius:var(--card-radius,10px); font-size:13px; font-weight:700; cursor:pointer; display:flex; flex-direction:column; align-items:center; gap:8px; transition:all .2s; text-align:center; position:relative; overflow:hidden; }
.tool-btn::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:var(--card-radius,10px) 10px 0 0; transition:height .2s; }
.tool-btn:hover { transform:translateY(-2px); box-shadow:0 4px 14px rgba(0,0,0,.08); }
.tool-btn .tool-icon { width:36px; height:36px; border-radius:var(--card-radius,10px); display:flex; align-items:center; justify-content:center; font-size:15px; transition:transform .2s; }
.tool-btn:hover .tool-icon { transform:scale(1.1); }
.tool-btn.t-sql .tool-icon { background:var(--c-secondary-light); color:var(--c-secondary,var(--c-secondary)); }
.tool-btn.t-sql::before { background:var(--c-secondary,var(--c-secondary)); }
.tool-btn.t-sql:hover { border-color:var(--c-secondary-border); background:var(--c-secondary-light); }
.tool-btn.t-history .tool-icon { background:var(--c-purple-light); color:var(--c-info,var(--c-purple)); }
.tool-btn.t-history::before { background:var(--c-purple); }
.tool-btn.t-history:hover { border-color:var(--c-purple-light); background:var(--c-purple-light); }
.tool-btn.t-import .tool-icon { background:var(--c-success-light); color:var(--c-success,var(--c-success)); }
.tool-btn.t-import::before { background:var(--c-success); }
.tool-btn.t-import:hover { border-color:var(--c-success-border); background:var(--c-success-light); }
.tool-btn.t-export .tool-icon { background:var(--c-warning-light); color:var(--c-warning); }
.tool-btn.t-export::before { background:var(--c-warning); }
.tool-btn.t-export:hover { border-color:var(--c-warning-border); background:var(--c-warning-light); }
.tool-btn.t-er { grid-column:1/-1; flex-direction:row; padding:10px 14px; gap:10px; }
.tool-btn.t-er .tool-icon { background:var(--c-danger-light); color:var(--c-danger); }
.tool-btn.t-er::before { background:var(--c-danger); }
.tool-btn.t-er:hover { border-color:var(--c-danger-border); background:var(--c-danger-light); }

.table-search { width:100%; padding:8px 10px 8px 32px; border:1px solid var(--border-color,var(--border-color)); border-radius:7px; font-size:14px; outline:none; background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.44.856a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") 10px center/13px no-repeat; transition:all .2s; }
.table-search:focus { border-color:var(--c-danger); box-shadow:0 0 0 3px rgba(59,130,246,.1); }

.table-list { flex:1; overflow-y:auto; padding:4px 0; }
.table-list::-webkit-scrollbar { width:5px; }
.table-list::-webkit-scrollbar-thumb { background:var(--input-border); border-radius:3px; }

.tbl-item { padding:7px 14px; font-size:14px; color:var(--text-secondary); cursor:pointer; display:flex; align-items:center; gap:8px; transition:all .15s; border-left:3px solid transparent; }
.tbl-item:hover { background:var(--border-light,var(--border-light)); color:var(--c-secondary,var(--c-secondary)); border-left-color:var(--c-secondary-border); }
.tbl-item.active { background:var(--c-secondary-light); color:var(--c-secondary,var(--c-secondary)); border-left-color:var(--c-secondary,var(--c-secondary)); font-weight:500; }
.tbl-item i.fa-table { font-size:12px; color:var(--c-secondary-border); }
.tbl-item:hover i.fa-table { color:var(--c-danger); }
.tbl-item .tbl-meta { margin-left:auto; font-size:12px; color:var(--hover-border); font-weight:400; white-space:nowrap; }
.tbl-item:hover .tbl-meta { color:var(--c-danger-border); }

/* ── Main workspace ─────────────────────────── */
.db-main { flex:1; display:flex; flex-direction:column; overflow:hidden; }

/* Tab bar */
.db-tabs { display:flex; background:var(--table-header-bg,var(--table-header-bg)); border-bottom:1px solid var(--border-color,var(--border-color)); overflow-x:auto; min-height:40px; }
.db-tabs::-webkit-scrollbar { height:2px; }
.db-tabs::-webkit-scrollbar-thumb { background:var(--hover-border); }

.db-tab { padding:10px 14px; font-size:14px; font-weight:500; color:var(--text-muted); cursor:pointer; display:flex; align-items:center; gap:7px; white-space:nowrap; border-bottom:2px solid transparent; transition:all .15s; position:relative; }
.db-tab:hover { background:var(--card-bg,#fff); color:var(--text-body); }
.db-tab.active { background:var(--card-bg,#fff); color:var(--c-secondary,var(--c-secondary)); border-bottom-color:var(--c-secondary,var(--c-secondary)); }
.db-tab i.tab-icon { font-size:13px; }
.db-tab .tab-close { font-size:12px; padding:3px; border-radius:4px; opacity:0; margin-left:4px; transition:all .15s; }
.db-tab:hover .tab-close { opacity:.6; }
.db-tab .tab-close:hover { opacity:1; background:var(--c-secondary-light); color:var(--c-danger); }

/* Content area */
.db-content { flex:1; overflow:hidden; position:relative; }
.db-pane { display:none; height:100%; overflow:auto; }
.db-pane.active { display:flex; flex-direction:column; }

/* ── Query Editor ───────────────────────────── */
.sql-editor-wrap { position:relative; }
.monaco-sql-wrap { background:var(--text-heading); }

.toolbar { padding:8px 14px; background:var(--card-bg,#fff); border-bottom:1px solid var(--border-color,var(--border-color)); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
.tb-btn { background:var(--table-header-bg,var(--table-header-bg)); color:var(--text-secondary); border:1px solid var(--border-color,var(--border-color)); padding:5px 10px; border-radius:5px; font-size:13px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all .15s; }
.tb-btn:hover { background:var(--border-light,var(--border-light)); color:var(--c-secondary,var(--c-secondary)); border-color:var(--c-secondary-border); }
.shortcut-hints { font-size:12px; color:var(--hover-border); }
.shortcut-hints kbd { background:var(--border-light,var(--border-light)); border:1px solid var(--border-color,var(--border-color)); padding:1px 5px; border-radius:3px; font-size:12px; font-family:'JetBrains Mono',monospace; }

.btn-run { background:linear-gradient(135deg,var(--c-success),var(--c-success)); color:#fff; border:none; padding:7px 16px; border-radius:7px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all .2s; box-shadow:0 2px 6px rgba(34,197,94,.25); }
.btn-run:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(34,197,94,.35); }
.btn-run:active { transform:translateY(0); }
.btn-bookmark { background:transparent; border:none; cursor:pointer; }

/* ── Results ─────────────────────────────────── */
.result-msg { padding:10px 16px; font-size:15px; display:flex; align-items:center; gap:8px; }
.result-msg.error { background:var(--c-danger-light); color:var(--c-danger); border-bottom:1px solid var(--c-danger-border); }
.result-msg.success { background:var(--c-success-light); color:var(--c-success); border-bottom:1px solid var(--c-success-border); }
.result-time { font-size:13px; opacity:.7; margin-left:4px; }
.result-header { padding:8px 14px; background:var(--table-header-bg,var(--table-header-bg)); border-bottom:1px solid var(--border-color,var(--border-color)); display:flex; justify-content:space-between; align-items:center; }
.result-badge { background:var(--c-secondary-light); color:var(--c-secondary); font-size:13px; font-weight:600; padding:3px 10px; border-radius:20px; }
.result-stats { display:flex; align-items:center; gap:10px; }
.result-wrap { overflow:auto; flex:1; }

.result-table { width:100%; border-collapse:collapse; white-space:nowrap; font-size:14px; }
.result-table thead { position:sticky; top:0; z-index:2; }
.result-table th { background:var(--table-header-bg,var(--table-header-bg)); padding:8px 12px; font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.4px; border-bottom:2px solid var(--border-color); text-align:left; }
.result-table td { padding:7px 12px; color:var(--text-body); border-bottom:1px solid var(--border-light,var(--border-light)); font-family:'JetBrains Mono',monospace; font-size:13px; max-width:300px; overflow:hidden; text-overflow:ellipsis; }
.result-table tbody tr:hover td { background:var(--hover-bg); }
.result-table .row-num { color:var(--hover-border); font-size:12px; width:40px; text-align:center; }
.result-table .null-val { color:var(--input-border); font-style:italic; }
.result-table .null-val i { font-style:italic; }
.result-table .num-val { color:var(--c-info); text-align:right; }

.empty-results { text-align:center; padding:60px 20px; color:var(--text-faint); }
.empty-results i { font-size:36px; display:block; margin-bottom:12px; color:var(--hover-border); }
.empty-results p { font-size:16px; margin-bottom:6px; }
.empty-results span { font-size:14px; }

/* ── Status bar ──────────────────────────────── */
.db-statusbar { height:28px; background:var(--table-header-bg,var(--table-header-bg)); border-top:1px solid var(--border-color,var(--border-color)); display:flex; align-items:center; padding:0 14px; gap:16px; font-size:13px; color:var(--text-faint); }
.db-statusbar .status-item { display:flex; align-items:center; gap:5px; }
.db-statusbar .status-dot { width:6px; height:6px; border-radius:50%; background:var(--c-success); }

/* ── Summary tab ─────────────────────────────── */
.summary-tab-content { padding:20px; overflow:auto; }
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
.stat-card { background:var(--card-bg,#fff); border-radius:var(--card-radius,10px); padding:16px; border:1px solid var(--border-color,var(--border-color)); display:flex; align-items:center; gap:12px; transition:all .2s; }
.stat-card:hover { border-color:var(--c-secondary-border); box-shadow:0 2px 8px rgba(59,130,246,.08); }
.stat-icon { width:40px; height:40px; border-radius:var(--card-radius,10px); display:flex; align-items:center; justify-content:center; font-size:16px; }
.stat-icon.blue { background:var(--c-secondary-light); color:var(--c-secondary); }
.stat-icon.green { background:var(--c-success-light); color:var(--c-success); }
.stat-icon.purple { background:var(--c-secondary-light); color:var(--c-purple); }
.stat-icon.amber { background:var(--c-warning-light); color:var(--c-warning); }
.stat-val { font-size:18px; font-weight:700; color:var(--header-text,var(--text-heading)); }
.stat-label { font-size:13px; color:var(--text-muted); }

.card { background:var(--card-bg,#fff); border:1px solid var(--border-color,var(--border-color)); border-radius:var(--card-radius,10px); overflow:hidden; }
.card-header { padding:12px 16px; border-bottom:1px solid var(--border-color,var(--border-color)); display:flex; justify-content:space-between; align-items:center; background:var(--hover-bg); }
.card-title { font-size:15px; font-weight:600; color:var(--header-text,var(--text-heading)); }
.search-box { padding:6px 10px; border:1px solid var(--border-color,var(--border-color)); border-radius:6px; font-size:14px; outline:none; }
.search-box:focus { border-color:var(--c-danger); }

.summary-table { width:100%; border-collapse:collapse; }
.summary-table th { text-align:left; background:var(--table-header-bg,var(--table-header-bg)); border-bottom:1px solid var(--border-color,var(--border-color)); padding:8px 14px; font-size:13px; font-weight:600; color:var(--text-muted); text-transform:uppercase; }
.summary-table td { border-bottom:1px solid var(--border-light,var(--border-light)); padding:8px 14px; font-size:14px; color:var(--text-secondary); }
.summary-row:hover td { background:var(--hover-bg); }
.tname { font-weight:600; color:var(--c-secondary,var(--c-secondary)); text-decoration:none; font-size:14px; }
.tname:hover { text-decoration:underline; }
.btn-xs { padding:4px 10px; font-size:13px; border-radius:5px; cursor:pointer; border:none; font-weight:500; display:inline-flex; align-items:center; gap:4px; }
.btn-blue { background:var(--c-secondary-light); color:var(--c-secondary,var(--c-secondary)); }
.btn-blue:hover { background:var(--c-secondary-light); }
.btn-red { background:var(--c-danger-light); color:var(--c-danger); }
.btn-red:hover { background:var(--c-danger-light); }

/* ── Table view ──────────────────────────────── */
.data-table-wrap { overflow:auto; flex:1; }
.data-table { width:100%; border-collapse:collapse; white-space:nowrap; }
.data-table th { position:sticky; top:0; background:var(--table-header-bg,var(--table-header-bg)); z-index:1; padding:8px 12px; font-size:12px; font-weight:700; color:var(--text-muted); border-bottom:2px solid var(--border-color); text-transform:uppercase; text-align:left; }
.data-table th.sortable-th { cursor:pointer; transition:all .15s; }
.data-table th.sortable-th:hover { background:var(--c-secondary-light); color:var(--c-secondary,var(--c-secondary)); }
.data-table td { padding:7px 12px; font-size:14px; color:var(--text-body); border-bottom:1px solid var(--border-light,var(--border-light)); max-width:250px; overflow:hidden; text-overflow:ellipsis; font-family:'JetBrains Mono',monospace; }
.data-table tbody tr:hover td { background:var(--hover-bg); }
span.null-val,.data-table .null-val { color:var(--input-border); font-style:italic; font-size:13px; }
.editable { cursor:pointer; }
.editable:hover { background:var(--c-secondary-light) !important; outline:1px dashed var(--c-secondary-border); outline-offset:-1px; }
.editing { padding:0 !important; }
.edit-input { width:100%; padding:7px 10px; border:2px solid var(--c-danger); font-size:14px; font-family:'JetBrains Mono',monospace; outline:none; border-radius:0; }
.edit-success { animation:editFlash .6s ease; }
.edit-error { animation:editError .6s ease; }
@keyframes editFlash { 0%{background:var(--c-success-light)} 100%{background:transparent} }
@keyframes editError { 0%{background:var(--c-secondary-light)} 100%{background:transparent} }

/* Pagination */
.pager { padding:10px 16px; border-top:1px solid var(--border-color,var(--border-color)); display:flex; justify-content:space-between; align-items:center; background:var(--table-header-bg,var(--table-header-bg)); font-size:14px; color:var(--text-muted); flex-wrap:wrap; gap:8px; }
.pager a,.pager .current,.pager .pager-btn { display:inline-flex; align-items:center; justify-content:center; min-width:30px; height:30px; padding:0 8px; border-radius:5px; font-size:14px; text-decoration:none; }
.pager a { background:var(--card-bg,#fff); color:var(--c-secondary,var(--c-secondary)); border:1px solid var(--border-color,var(--border-color)); }
.pager a:hover { background:var(--c-secondary-light); border-color:var(--c-secondary-border); }
.pager .current { background:var(--c-secondary,var(--c-secondary)); color:#fff; font-weight:600; }
.pager .disabled { color:var(--input-border); }
.per-page-select { padding:4px 8px; border:1px solid var(--border-color,var(--border-color)); border-radius:5px; font-size:14px; outline:none; }

/* ── Loading ─────────────────────────────────── */
.loading-overlay { position:absolute; inset:0; background:rgba(255,255,255,.85); display:flex; justify-content:center; align-items:center; z-index:10; flex-direction:column; gap:8px; }
.loading-overlay i { color:var(--c-secondary,var(--c-secondary)); }
.loading-overlay span { font-size:14px; color:var(--text-muted); }

/* ── History/Import/Export ────────────────────── */
.history-tab-content,.import-tab-content,.export-tab-content { padding:20px; overflow:auto; }
.upload-zone { border:2px dashed var(--input-border); border-radius:var(--card-radius,10px); padding:40px; text-align:center; cursor:pointer; transition:all .2s; }
.upload-zone:hover { border-color:var(--c-danger); background:var(--hover-bg); }
.warning-box { background:var(--c-warning-light); border:1px solid var(--c-warning-border); border-radius:8px; padding:12px 16px; font-size:15px; color:var(--c-warning); margin-bottom:20px; }
.success-box { background:var(--c-success-light); border:1px solid var(--c-success-border); padding:12px; border-radius:8px; color:var(--c-success); }
.error-box { background:var(--c-secondary-light); border:1px solid var(--c-secondary-border); padding:12px; border-radius:8px; color:var(--c-danger); }

/* ── Context menu ────────────────────────────── */
.ctx-menu { position:fixed; background:var(--card-bg,#fff); border:1px solid var(--border-color,var(--border-color)); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:4px; z-index:9999; min-width:180px; }
.ctx-menu-item { padding:8px 12px; font-size:14px; cursor:pointer; border-radius:5px; display:flex; align-items:center; gap:8px; color:var(--text-body); }
.ctx-menu-item:hover { background:var(--border-light,var(--border-light)); color:var(--c-secondary,var(--c-secondary)); }
.ctx-menu-item i { width:14px; color:var(--text-faint); font-size:13px; }
.ctx-menu-item:hover i { color:var(--c-secondary,var(--c-secondary)); }
.ctx-menu-sep { height:1px; background:var(--border-color); margin:4px 0; }
.ctx-menu-group { padding:5px 12px 3px; font-size:10px; font-weight:700; color:var(--text-faint); text-transform:uppercase; letter-spacing:.6px; }
.ctx-menu-item.danger { color:var(--c-primary,var(--c-danger)); }
.ctx-menu-item.danger:hover { background:var(--c-danger-light); color:var(--c-danger); }
.ctx-menu-item.danger:hover i { color:var(--c-primary,var(--c-danger)); }

/* Sidebar sort */
.sidebar-sort { display:flex; gap:4px; margin-top:8px; }
.sort-btn { flex:1; padding:5px 8px; font-size:11px; font-weight:500; border:1px solid var(--border-color,var(--border-color)); border-radius:5px; background:var(--card-bg,#fff); color:var(--text-muted); cursor:pointer; text-align:center; transition:all .15s; }
.sort-btn:hover { background:var(--table-header-bg,var(--table-header-bg)); border-color:var(--hover-border); }
.sort-btn.active { background:var(--c-secondary-light); color:var(--c-secondary,var(--c-secondary)); border-color:var(--c-secondary-border); font-weight:600; }

/* Template dropdown */
.tpl-dropdown { position:fixed; background:var(--card-bg,#fff); border:1px solid var(--border-color,var(--border-color)); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:4px; z-index:9999; min-width:220px; display:none; }
.tpl-dropdown-item { padding:8px 12px; font-size:13px; cursor:pointer; border-radius:5px; display:flex; align-items:center; gap:8px; color:var(--text-body); }
.tpl-dropdown-item:hover { background:var(--border-light,var(--border-light)); color:var(--c-secondary,var(--c-secondary)); }
.tpl-dropdown-item i { width:14px; color:var(--text-faint); font-size:12px; }
.tpl-dropdown-item:hover i { color:var(--c-secondary,var(--c-secondary)); }
.tpl-dropdown-group { padding:5px 12px 3px; font-size:10px; font-weight:700; color:var(--text-faint); text-transform:uppercase; letter-spacing:.6px; }
.tpl-dropdown-sep { height:1px; background:var(--border-color); margin:4px 0; }
</style>
@endpush

@section('content')
<div style="background:var(--table-header-bg,var(--table-header-bg));border:1px solid var(--border-color,var(--border-color));border-radius:var(--card-radius,10px);padding:12px 18px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;gap:12px">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="width:38px;height:38px;background:var(--c-secondary-light);border-radius:8px;display:flex;align-items:center;justify-content:center">
            <i class="fas fa-database" style="color:var(--c-secondary,var(--c-secondary));font-size:16px"></i>
        </div>
        <div>
            <div style="font-size:15px;color:var(--header-text,var(--text-heading));font-weight:600">
                @if($activeConnection)
                    {{ $activeConnection->name }}
                    <span style="font-size:11px;background:var(--c-secondary-light);color:var(--c-secondary);padding:2px 8px;border-radius:4px;font-weight:600;margin-left:6px">EXTERNAL</span>
                @else
                    {{ $dbName }}
                    <span style="font-size:11px;background:var(--c-success-light);color:var(--c-success,var(--c-success));padding:2px 8px;border-radius:4px;font-weight:600;margin-left:6px">DEFAULT</span>
                @endif
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                <i class="fas fa-server" style="margin-right:4px"></i>
                {{ $activeConnection ? $activeConnection->dbhost.':'.$activeConnection->dbport : config('database.connections.mysql.host').':'.config('database.connections.mysql.port', '3306') }}
                <span style="margin:0 6px;color:var(--input-border)">|</span>
                <i class="fas fa-hdd" style="margin-right:4px"></i>
                {{ $dbName }}
                <span style="margin:0 6px;color:var(--input-border)">|</span>
                <i class="fas fa-table" style="margin-right:4px"></i>
                {{ count($tableList) }} tables
                <span style="margin:0 6px;color:var(--input-border)">|</span>
                <i class="fas fa-weight" style="margin-right:4px"></i>
                {{ $totalSize >= 1048576 ? number_format($totalSize/1048576,1).' MB' : number_format($totalSize/1024,1).' KB' }}
            </div>
        </div>
    </div>
    <a href="{{ $activeConnection ? route('admin.database.connections.clear') : route('admin.database.connections.index') }}" style="font-size:13px;color:var(--c-secondary,var(--c-secondary));text-decoration:none;font-weight:500;padding:6px 14px;background:var(--card-bg,#fff);border:1px solid var(--c-secondary-border);border-radius:6px;display:inline-flex;align-items:center;gap:5px"><i class="fas fa-arrow-left"></i> Back to Connections</a>
</div>
<div class="db-container">
    {{-- Sidebar --}}
    <div class="db-sidebar">
        <div class="db-sidebar-header">
            <div class="db-sidebar-title">
                <span><i class="fas fa-database" style="margin-right:4px"></i> {{ $dbName }}</span>
                <span class="db-badge">{{ count($tableList) }} tables</span>
            </div>
            @if($activeConnection)
            <div style="background:var(--c-info-light);border:1px solid var(--c-info-border);border-radius:6px;padding:5px 8px;margin-bottom:8px;font-size:12px;color:var(--c-info);display:flex;align-items:center;gap:4px">
                <i class="fas fa-plug"></i> External DB — <strong>{{ $activeConnection->name }}</strong>
            </div>
            @endif
            <div class="db-tools">
                <button class="tool-btn t-sql" onclick="DatabaseManager.openQueryTab()"><span class="tool-icon"><i class="fas fa-plus"></i></span> New SQL</button>
                <button class="tool-btn t-history" onclick="DatabaseManager.openHistoryTab()"><span class="tool-icon"><i class="fas fa-history"></i></span> History</button>
                <button class="tool-btn t-import" onclick="DatabaseManager.openImportTab()"><span class="tool-icon"><i class="fas fa-upload"></i></span> Import</button>
                <button class="tool-btn t-export" onclick="DatabaseManager.openExportTab()"><span class="tool-icon"><i class="fas fa-download"></i></span> Export</button>
                <button class="tool-btn t-er" onclick="DatabaseManager.openErDiagram()"><span class="tool-icon"><i class="fas fa-project-diagram"></i></span> ER Diagram</button>
            </div>
            @if($savedConnections->count() > 0)
            <select class="table-search" style="padding-left:10px;margin-bottom:8px;background-image:none;cursor:pointer" onchange="if(this.value==='clear') window.location='{{ route('admin.database.connections.clear') }}'; else if(this.value) window.location=this.value;">
                <option value="" {{ !$activeConnection ? 'selected' : '' }}>{{ config('database.connections.mysql.database') }} (default)</option>
                @foreach($savedConnections as $sc)
                <option value="{{ route('admin.database.connections.browse', $sc->id) }}" {{ $activeConnection && $activeConnection->id == $sc->id ? 'selected' : '' }}>{{ $sc->name }} — {{ $sc->dbname }}</option>
                @endforeach
                @if($activeConnection)
                <option value="clear">⬅ Back to default</option>
                @endif
            </select>
            @endif
            <input type="text" class="table-search" placeholder="Filter tables..." onkeyup="DatabaseManager.filterTables(this.value)">
            <div class="sidebar-sort">
                <button class="sort-btn active" onclick="DatabaseManager.sortSidebar('name',this)" title="Sort A-Z"><i class="fas fa-sort-alpha-down"></i></button>
                <button class="sort-btn" onclick="DatabaseManager.sortSidebar('rows',this)" title="Sort by rows"><i class="fas fa-sort-numeric-down"></i></button>
                <button class="sort-btn" onclick="DatabaseManager.sortSidebar('size',this)" title="Sort by size"><i class="fas fa-sort-amount-down"></i></button>
            </div>
        </div>
        <div class="table-list" id="tableList">
            @foreach($tableList as $t)
            <div class="tbl-item" data-name="{{ $t['name'] }}" data-rows="{{ $t['rows'] }}" data-size="{{ $t['size'] }}" ondblclick="DatabaseManager.openTable('{{ $t['name'] }}')" oncontextmenu="DatabaseManager.showCtxMenu(event,'{{ $t['name'] }}')" title="{{ $t['name'] }} — {{ number_format($t['rows']) }} rows">
                <i class="fas fa-table"></i>
                <span style="flex:1;overflow:hidden;text-overflow:ellipsis">{{ $t['name'] }}</span>
                <span class="tbl-meta">{{ $t['rows'] > 0 ? number_format($t['rows']) : '—' }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Main Workspace --}}
    <div class="db-main">
        <div class="db-tabs" id="tabBar"></div>
        <div class="db-content" id="tabContent"></div>
        <div class="db-statusbar">
            <div class="status-item"><span class="status-dot" style="{{ $activeConnection ? 'background:var(--c-warning)' : '' }}"></span> {{ $activeConnection ? 'External' : 'Connected' }}</div>
            <div class="status-item"><i class="fas fa-server"></i> {{ $activeConnection ? $activeConnection->dbhost.':'.$activeConnection->dbport : config('database.connections.mysql.host').':'.config('database.connections.mysql.port', '3306') }}</div>
            <div class="status-item"><i class="fas fa-database"></i> {{ $dbName }}</div>
            @if($activeConnection)
            <div class="status-item"><i class="fas fa-plug"></i> {{ $activeConnection->name }}</div>
            @endif
            <div class="status-item" style="margin-left:auto"><i class="fas fa-hdd"></i> {{ $totalSize >= 1048576 ? number_format($totalSize/1048576,1).' MB' : number_format($totalSize/1024,1).' KB' }}</div>
        </div>
    </div>
</div>

{{-- Context menu (hidden) --}}
<div id="ctxMenu" class="ctx-menu" style="display:none"></div>

{{-- Template dropdown (hidden) --}}
<div id="templateDropdown" class="tpl-dropdown">
    <div class="tpl-dropdown-group">Select</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('select')"><i class="fas fa-search"></i> SELECT basic</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('select-join')"><i class="fas fa-link"></i> SELECT with JOIN</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('count-group')"><i class="fas fa-chart-bar"></i> COUNT + GROUP BY</div>
    <div class="tpl-dropdown-sep"></div>
    <div class="tpl-dropdown-group">Modify</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('insert')"><i class="fas fa-plus-circle"></i> INSERT INTO</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('update')"><i class="fas fa-edit"></i> UPDATE WHERE</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('delete')"><i class="fas fa-minus-circle"></i> DELETE WHERE</div>
    <div class="tpl-dropdown-sep"></div>
    <div class="tpl-dropdown-group">Structure</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('create')"><i class="fas fa-plus-square"></i> CREATE TABLE</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('alter-add')"><i class="fas fa-columns"></i> ALTER ADD COLUMN</div>
    <div class="tpl-dropdown-item" onclick="DatabaseManager.insertTemplate('alter-index')"><i class="fas fa-key"></i> ALTER ADD INDEX</div>
</div>

{{-- Hidden Templates --}}
<template id="tpl-summary-tab">@include('admin.pages.database.partials.database_summary')</template>
<template id="tpl-query-tab">@include('admin.pages.database.partials.query_tab')</template>
<template id="tpl-import-tab">@include('admin.pages.database.partials.import_tab')</template>
<template id="tpl-export-tab">@include('admin.pages.database.partials.export_tab')</template>
<template id="tpl-history-tab">@include('admin.pages.database.partials.history_tab', ['bookmarks' => $bookmarks, 'recentHistory' => $recentHistory])</template>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
const TABLE_LIST = @json(array_column($tableList, 'name'));
const CURRENT_CONNECTION_ID = '{{ $activeConnection ? $activeConnection->id : "default" }}_{{ $dbName }}';

// Monaco setup
let monacoReady = false;
const columnCache = {}; // Cache: table → [col1, col2, ...]

require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs' } });
require(['vs/editor/editor.main'], () => {
    monacoReady = true;

    // Trigger auto-complete on dot (for table.column)
    monaco.languages.setLanguageConfiguration('sql', {
        autoClosingPairs: [
            { open: '(', close: ')' },
            { open: "'", close: "'" },
            { open: '`', close: '`' },
        ],
    });

    // Register SQL completions with table names + column auto-complete
    monaco.languages.registerCompletionItemProvider('sql', {
        triggerCharacters: ['.'],
        provideCompletionItems: (model, position) => {
            const textUntilPos = model.getValueInRange({
                startLineNumber: position.lineNumber,
                startColumn: 1,
                endLineNumber: position.lineNumber,
                endColumn: position.column
            });

            // Check if user typed "tablename." — suggest columns
            const dotMatch = textUntilPos.match(/[`]?(\w+)[`]?\.$/);
            if (dotMatch) {
                const tableName = dotMatch[1];
                if (columnCache[tableName]) {
                    return { suggestions: columnCache[tableName].map(c => ({
                        label: c,
                        kind: monaco.languages.CompletionItemKind.Property,
                        insertText: c,
                        detail: tableName + ' column'
                    })) };
                }
                // Fetch columns if not cached (async — results appear on next trigger)
                if (TABLE_LIST.includes(tableName)) {
                    DatabaseManager.fetchColumns(tableName);
                }
                return { suggestions: [] };
            }

            // Default: table names + SQL keywords
            const suggestions = TABLE_LIST.map(t => ({
                label: t,
                kind: monaco.languages.CompletionItemKind.Field,
                insertText: t,
                detail: 'Table'
            }));
            // Add SQL keywords
            ['SELECT','FROM','WHERE','JOIN','LEFT JOIN','RIGHT JOIN','INNER JOIN','ON','AND','OR','INSERT INTO','VALUES','UPDATE','SET','DELETE','CREATE TABLE','ALTER TABLE','DROP TABLE','ORDER BY','GROUP BY','HAVING','LIMIT','OFFSET','AS','DISTINCT','COUNT','SUM','AVG','MAX','MIN','LIKE','IN','NOT','NULL','IS NULL','IS NOT NULL','BETWEEN','EXISTS','UNION','CASE','WHEN','THEN','ELSE','END'].forEach(kw => {
                suggestions.push({ label: kw, kind: monaco.languages.CompletionItemKind.Keyword, insertText: kw, detail: 'Keyword' });
            });
            return { suggestions };
        }
    });
});

const DatabaseManager = {
    tabs: [],
    activeTabId: null,
    editors: {}, // Monaco instances per query tab

    init() {
        const storedConnId = localStorage.getItem('db_connection_id');
        const connectionChanged = storedConnId !== CURRENT_CONNECTION_ID;

        // If same connection, restore tabs; if different, start fresh
        if (!connectionChanged) {
            const stored = localStorage.getItem('db_tabs');
            if (stored) { try { this.tabs = JSON.parse(stored); } catch(e) {} }
        }

        // Save current connection ID
        localStorage.setItem('db_connection_id', CURRENT_CONNECTION_ID);

        // Always ensure Overview is the first tab
        this.tabs = this.tabs.filter(t => t.type !== 'summary');
        this.tabs.unshift({ type:'summary', name:'Overview', id:'tab_summary_static' });
        this.renderTabs();

        if (connectionChanged) {
            // Connection changed — reset to Overview, clear old state
            this.saveState();
            this.switchTab(this.tabs[0].id);
        } else {
            const active = localStorage.getItem('db_active_tab');
            this.switchTab((active && this.tabs.find(t => t.id === active)) ? active : this.tabs[0].id);
        }
    },

    saveState() {
        const meta = this.tabs.map(t => ({
            id:t.id, type:t.type, name:t.name, table:t.table, page:t.page, perPage:t.perPage,
            sort:t.sort||'', sortDir:t.sortDir||'',
            sql: t.type === 'query' ? this.getEditorValue(t.id) : null
        }));
        localStorage.setItem('db_tabs', JSON.stringify(meta));
        localStorage.setItem('db_active_tab', this.activeTabId);
    },

    getEditorValue(tabId) {
        if (this.editors[tabId]) return this.editors[tabId].getValue();
        const pane = document.getElementById('pane-' + tabId);
        const ta = pane?.querySelector('.sql-editor');
        return ta ? ta.value : '';
    },

    filterTables(q) {
        q = q.toLowerCase();
        document.querySelectorAll('.tbl-item').forEach(el => {
            el.style.display = el.textContent.toLowerCase().includes(q) ? 'flex' : 'none';
        });
    },

    addTab(data) { this.tabs.push(data); this.renderTabs(); this.switchTab(data.id); this.saveState(); },

    closeTab(e, id) {
        e.stopPropagation();
        const idx = this.tabs.findIndex(t => t.id === id);
        if (idx === -1) return;
        this.tabs.splice(idx, 1);
        const pane = document.getElementById('pane-' + id);
        if (pane) pane.remove();
        if (this.editors[id]) { this.editors[id].dispose(); delete this.editors[id]; }
        if (this.activeTabId === id) {
            const next = this.tabs[Math.max(0, idx-1)];
            if (next) this.switchTab(next.id);
        }
        this.renderTabs(); this.saveState();
    },

    renderTabs() {
        const bar = document.getElementById('tabBar');
        bar.innerHTML = '';
        const icons = { summary:'database', query:'terminal', table:'table', import:'upload', export:'download', history:'history', pinned:'thumbtack', er:'project-diagram' };
        this.tabs.forEach(t => {
            const el = document.createElement('div');
            el.className = `db-tab ${t.id === this.activeTabId ? 'active' : ''}`;
            el.onclick = () => this.switchTab(t.id);
            const closable = t.type !== 'summary';
            el.innerHTML = `<i class="fas fa-${icons[t.type]||'file'} tab-icon"></i><span style="max-width:120px;overflow:hidden;text-overflow:ellipsis">${t.name}</span>${closable ? `<i class="fas fa-times tab-close" onclick="DatabaseManager.closeTab(event,'${t.id}')"></i>` : ''}`;
            bar.appendChild(el);
        });
    },

    switchTab(id) {
        this.activeTabId = id;
        this.renderTabs(); this.saveState();
        document.querySelectorAll('.db-pane').forEach(p => { p.style.display = 'none'; p.classList.remove('active'); });
        let pane = document.getElementById('pane-' + id);
        const tab = this.tabs.find(t => t.id === id);
        if (!pane) {
            pane = document.createElement('div');
            pane.id = 'pane-' + id;
            pane.className = 'db-pane';
            document.getElementById('tabContent').appendChild(pane);
            setTimeout(() => this.loadTabContent(tab, pane), 10);
        }
        pane.style.display = 'flex'; pane.classList.add('active');
        // Re-layout Monaco if it's a query tab
        if (this.editors[id]) setTimeout(() => this.editors[id].layout(), 50);

        // Highlight sidebar item
        document.querySelectorAll('.tbl-item').forEach(el => el.classList.remove('active'));
        if (tab?.type === 'table') {
            document.querySelectorAll('.tbl-item').forEach(el => {
                if (el.textContent.trim().startsWith(tab.table)) el.classList.add('active');
            });
        }
    },

    loadTabContent(tab, pane) {
        pane.innerHTML = '<div class="loading-overlay"><i class="fas fa-circle-notch fa-spin fa-2x"></i><span>Loading...</span></div>';
        pane.style.display = 'flex';

        if (tab.type === 'table') { this.loadTableContent(tab, pane); return; }
        if (tab.type === 'pinned') { pane.innerHTML = ''; return; } // Content set by pinResults()
        if (tab.type === 'er') { this.loadErDiagram(pane); return; }

        const tplMap = { summary:'tpl-summary-tab', query:'tpl-query-tab', import:'tpl-import-tab', export:'tpl-export-tab', history:'tpl-history-tab' };
        const tpl = document.getElementById(tplMap[tab.type]);
        if (tpl) {
            pane.innerHTML = '';
            pane.appendChild(tpl.content.cloneNode(true));
        }

        // Init Monaco for query tabs
        if (tab.type === 'query') {
            const wrap = pane.querySelector('.monaco-sql-wrap');
            if (wrap) {
                const initEditor = () => {
                    this.editors[tab.id] = monaco.editor.create(wrap, {
                        value: tab.sql || '',
                        language: 'sql',
                        theme: 'vs-dark',
                        automaticLayout: true,
                        fontSize: 15,
                        fontFamily: "'JetBrains Mono', Consolas, monospace",
                        minimap: { enabled: false },
                        scrollBeyondLastLine: false,
                        lineNumbers: 'on',
                        folding: false,
                        wordWrap: 'on',
                        padding: { top: 12, bottom: 12 },
                        suggestOnTriggerCharacters: true,
                        quickSuggestions: true,
                        tabSize: 2,
                        renderLineHighlight: 'line',
                        scrollbar: { verticalScrollbarSize: 6, horizontalScrollbarSize: 6 }
                    });
                    // Ctrl+Enter to execute
                    this.editors[tab.id].addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.Enter, () => {
                        const btn = pane.querySelector('.btn-run');
                        if (btn) this.runQuery(btn);
                    });
                    // Ctrl+Shift+F to format
                    this.editors[tab.id].addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyMod.Shift | monaco.KeyCode.KeyF, () => {
                        this.formatSql(pane.querySelector('.tb-btn'));
                    });
                };
                monacoReady ? initEditor() : require(['vs/editor/editor.main'], initEditor);
            }
            pane.querySelector('.btn-run').dataset.tabId = tab.id;
        }

        // Init import tab event listeners
        if (tab.type === 'import') {
            this.initImportTab(pane);
        }
    },

    async loadTableContent(tab, pane) {
        const url = `{{ route('admin.database.table', ':table') }}`.replace(':table', encodeURIComponent(tab.table));
        let params = `page=${tab.page||1}&perPage=${tab.perPage||50}`;
        if (tab.sort) params += `&sort=${encodeURIComponent(tab.sort)}&dir=${tab.sortDir||'asc'}`;
        try {
            const res = await fetch(`${url}?${params}`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
            if (!res.ok) {
                pane.innerHTML = `<div style="padding:30px;text-align:center"><div style="font-size:36px;color:var(--hover-border);margin-bottom:12px"><i class="fas fa-exclamation-triangle"></i></div><div style="font-size:16px;color:var(--text-muted);margin-bottom:6px">Failed to load table <strong>${tab.table}</strong></div><div style="font-size:14px;color:var(--text-faint)">Server returned status ${res.status}</div></div>`;
                return;
            }
            pane.innerHTML = await res.text();
        } catch(e) { pane.innerHTML = `<div style="padding:30px;text-align:center"><div style="font-size:36px;color:var(--hover-border);margin-bottom:12px"><i class="fas fa-exclamation-triangle"></i></div><div style="font-size:16px;color:var(--text-muted)">Failed: ${e.message}</div></div>`; }
    },

    // ─── Table operations ────────────────────────
    openTable(name) {
        const existing = this.tabs.find(t => t.type === 'table' && t.table === name);
        if (existing) { this.switchTab(existing.id); return; }
        this.addTab({ id:'t_'+Date.now(), type:'table', name:name, table:name, page:1, perPage:50 });
    },

    openQueryTab() { this.addTab({ id:'q_'+Date.now(), type:'query', name:'SQL Query' }); },

    openImportTab() {
        const ex = this.tabs.find(t => t.type === 'import');
        if (ex) { this.switchTab(ex.id); return; }
        this.addTab({ id:'tab_import', type:'import', name:'Import' });
    },
    openExportTab() {
        const ex = this.tabs.find(t => t.type === 'export');
        if (ex) { this.switchTab(ex.id); return; }
        this.addTab({ id:'tab_export', type:'export', name:'Export' });
    },
    openHistoryTab() {
        const ex = this.tabs.find(t => t.type === 'history');
        if (ex) { this.switchTab(ex.id); return; }
        this.addTab({ id:'tab_history', type:'history', name:'History' });
    },
    openErDiagram() {
        const ex = this.tabs.find(t => t.type === 'er');
        if (ex) { this.switchTab(ex.id); return; }
        this.addTab({ id:'tab_er', type:'er', name:'ER Diagram' });
    },

    // ─── Query operations ────────────────────────
    async runQuery(btn) {
        const pane = btn.closest('.db-pane');
        const tabId = btn.dataset.tabId || this.activeTabId;
        const sql = this.getEditorValue(tabId).trim();
        const resultsDiv = pane.querySelector('.query-results');
        if (!sql) return;

        const tab = this.tabs.find(t => t.id === tabId);
        if (tab) tab.sql = sql;
        this.saveState();

        resultsDiv.innerHTML = '<div style="text-align:center;padding:30px"><i class="fas fa-circle-notch fa-spin fa-lg" style="color:var(--c-secondary,var(--c-secondary))"></i><div style="margin-top:8px;font-size:14px;color:var(--text-faint)">Executing query...</div></div>';

        try {
            const res = await fetch("{{ route('admin.database.query') }}", {
                method:"POST", headers:{"Content-Type":"application/json","X-CSRF-TOKEN":CSRF_TOKEN,"X-Requested-With":"XMLHttpRequest"},
                body:JSON.stringify({sql_b64: btoa(unescape(encodeURIComponent(sql)))})
            });
            if (!res.ok && res.status === 403) {
                resultsDiv.innerHTML = '<div class="result-msg error"><i class="fas fa-times-circle"></i> 403 Forbidden — cPanel mod_security may be blocking this request. Try a simpler query first.</div>';
                return;
            }
            resultsDiv.innerHTML = await res.text();
        } catch(e) {
            resultsDiv.innerHTML = `<div class="result-msg error"><i class="fas fa-times-circle"></i> ${e.message}</div>`;
        }
    },

    formatSql(btn) {
        const pane = btn?.closest('.db-pane');
        const tabId = this.activeTabId;
        const editor = this.editors[tabId];
        if (!editor) return;
        let sql = editor.getValue();
        // Basic SQL formatting
        const kws = ['SELECT','FROM','WHERE','AND','OR','LEFT JOIN','RIGHT JOIN','INNER JOIN','JOIN','ON','ORDER BY','GROUP BY','HAVING','LIMIT','OFFSET','INSERT INTO','VALUES','UPDATE','SET','DELETE','CREATE TABLE','ALTER TABLE','DROP TABLE','UNION','CASE','WHEN','THEN','ELSE','END'];
        kws.forEach(kw => { sql = sql.replace(new RegExp('\\b'+kw.replace(/ /g,'\\s+')+'\\b','gi'), '\n'+kw); });
        sql = sql.replace(/,\s*/g, ',\n    ');
        sql = sql.trim();
        editor.setValue(sql);
    },

    clearEditor(btn) {
        const tabId = this.activeTabId;
        if (this.editors[tabId]) this.editors[tabId].setValue('');
    },

    // ─── Column auto-complete cache ──────────────
    async fetchColumns(table) {
        if (columnCache[table]) return columnCache[table];
        try {
            const descSql = 'DESCRIBE `' + table + '`';
            const res = await fetch("{{ route('admin.database.query') }}", {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,'X-Requested-With':'XMLHttpRequest'},
                body:JSON.stringify({sql_b64: btoa(descSql)})
            });
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const rows = doc.querySelectorAll('.result-table tbody tr');
            const cols = [];
            rows.forEach(r => {
                const tds = r.querySelectorAll('td');
                if (tds.length >= 2) {
                    const col = tds[1]?.textContent?.trim();
                    if (col) cols.push(col);
                }
            });
            if (cols.length > 0) columnCache[table] = cols;
            return cols;
        } catch(e) { return []; }
    },

    // ─── Explain Plan ────────────────────────────
    runExplain(btn) {
        const pane = btn.closest('.db-pane');
        const tabId = this.activeTabId;
        const sql = this.getEditorValue(tabId).trim();
        if (!sql) { this.toast('Write a query first', 'error'); return; }

        // Prepend EXPLAIN if not already there
        const explainSql = sql.match(/^\s*EXPLAIN\s/i) ? sql : 'EXPLAIN ' + sql;

        // Run in a new query tab
        this.addTab({ id:'q_'+Date.now(), type:'query', name:'Explain', sql: explainSql });
        // Auto-execute after tab loads
        setTimeout(() => {
            const newPane = document.getElementById('pane-' + this.activeTabId);
            if (newPane) {
                const runBtn = newPane.querySelector('.btn-run');
                if (runBtn) this.runQuery(runBtn);
            }
        }, 500);
    },

    // ─── Query Templates ─────────────────────────
    insertTemplate(type) {
        const templates = {
            'select':       'SELECT *\nFROM `table_name`\nWHERE 1\nORDER BY id DESC\nLIMIT 100;',
            'select-join':  'SELECT a.*, b.*\nFROM `table_a` a\nLEFT JOIN `table_b` b ON a.id = b.table_a_id\nWHERE 1\nORDER BY a.id DESC\nLIMIT 100;',
            'insert':       'INSERT INTO `table_name` (`column1`, `column2`, `column3`)\nVALUES (\'value1\', \'value2\', \'value3\');',
            'update':       'UPDATE `table_name`\nSET `column1` = \'new_value\'\nWHERE id = 1;',
            'delete':       'DELETE FROM `table_name`\nWHERE id = 1;',
            'create':       'CREATE TABLE `new_table` (\n    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n    `name` VARCHAR(255) NOT NULL,\n    `status` TINYINT(1) DEFAULT 1,\n    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            'alter-add':    'ALTER TABLE `table_name`\n    ADD COLUMN `new_column` VARCHAR(255) NULL AFTER `existing_column`;',
            'alter-index':  'ALTER TABLE `table_name`\n    ADD INDEX `idx_column` (`column_name`);',
            'count-group':  'SELECT `column`, COUNT(*) AS cnt\nFROM `table_name`\nGROUP BY `column`\nORDER BY cnt DESC;',
        };

        const sql = templates[type];
        if (!sql) return;

        const tabId = this.activeTabId;
        const tab = this.tabs.find(t => t.id === tabId);

        if (tab && tab.type === 'query' && this.editors[tabId]) {
            // Insert at cursor or replace empty editor
            const editor = this.editors[tabId];
            const current = editor.getValue().trim();
            if (!current) {
                editor.setValue(sql);
            } else {
                // Insert at cursor position
                const pos = editor.getPosition();
                editor.executeEdits('template', [{
                    range: new monaco.Range(pos.lineNumber, pos.column, pos.lineNumber, pos.column),
                    text: '\n\n' + sql
                }]);
            }
            editor.focus();
        } else {
            // Open new query tab with template
            this.addTab({ id:'q_'+Date.now(), type:'query', name:'SQL Query', sql: sql });
        }

        // Close template dropdown
        const dd = document.getElementById('templateDropdown');
        if (dd) dd.style.display = 'none';
    },

    toggleTemplateDropdown(btn) {
        const dd = document.getElementById('templateDropdown');
        if (!dd) return;
        const isOpen = dd.style.display === 'block';
        dd.style.display = isOpen ? 'none' : 'block';
        if (!isOpen) {
            const rect = btn.getBoundingClientRect();
            dd.style.left = rect.left + 'px';
            dd.style.top = (rect.bottom + 4) + 'px';
        }
    },

    // ─── Export results as SQL INSERT ─────────────
    exportResultsSQL(btn) {
        const pane = btn.closest('.db-pane');
        const table = pane?.querySelector('.result-table');
        if (!table) return;
        const headers = [...table.querySelectorAll('th')].slice(1).map(h => h.textContent.trim());
        const rows = [...table.querySelectorAll('tbody tr')];
        if (!rows.length) { this.toast('No results to export', 'error'); return; }

        const tableName = 'table_name'; // placeholder
        let sql = '-- SQL INSERT export\n-- Generated: ' + new Date().toLocaleString() + '\n-- Rows: ' + rows.length + '\n\n';

        const colList = headers.map(h => '`' + h + '`').join(', ');

        rows.forEach(tr => {
            const tds = [...tr.querySelectorAll('td')].slice(1);
            const vals = tds.map(td => {
                const text = td.textContent.trim();
                if (td.classList.contains('null-val') || text === 'NULL') return 'NULL';
                if (td.classList.contains('num-val') && !isNaN(text)) return text;
                return "'" + text.replace(/'/g, "\\'") + "'";
            });
            sql += 'INSERT INTO `' + tableName + '` (' + colList + ') VALUES (' + vals.join(', ') + ');\n';
        });

        const blob = new Blob([sql], {type:'application/sql'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'query_results_' + new Date().toISOString().slice(0,19).replace(/:/g,'') + '.sql';
        a.click();
        this.toast('SQL exported!', 'success');
    },

    // ─── Pin results ─────────────────────────────
    pinResults(btn) {
        const pane = btn.closest('.db-pane');
        const resultsDiv = pane?.querySelector('.query-results');
        if (!resultsDiv || !resultsDiv.innerHTML.trim()) { this.toast('No results to pin', 'error'); return; }

        const pinnedHtml = resultsDiv.innerHTML;
        const tabId = 'pin_' + Date.now();
        const tab = { id: tabId, type: 'pinned', name: 'Pinned' };
        this.tabs.push(tab);
        this.renderTabs();
        this.switchTab(tabId);

        // Set content after tab is created
        setTimeout(() => {
            const pinnedPane = document.getElementById('pane-' + tabId);
            if (pinnedPane) {
                pinnedPane.innerHTML = '<div style="padding:8px 14px;background:var(--c-warning-light);border-bottom:1px solid var(--c-warning-border);font-size:13px;color:var(--c-warning);display:flex;align-items:center;gap:8px"><i class="fas fa-thumbtack"></i> Pinned results — this tab will persist while you run other queries</div>' + pinnedHtml;
            }
        }, 50);

        this.toast('Results pinned!', 'success');
    },

    bookmarkQuery(btn) {
        const tabId = this.activeTabId;
        const sql = this.getEditorValue(tabId).trim();
        if (!sql) return;
        const title = prompt("Bookmark title:", "My Query");
        if (!title) return;
        fetch("{{ route('admin.database.bookmark.add') }}", {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
            body:JSON.stringify({title,sql_query:sql})
        }).then(r=>r.json()).then(d=> { if(d.success) this.toast('Bookmark saved!','success'); });
    },

    useQuery(sql) {
        let tab = this.tabs.find(t => t.type === 'query' && t.id === this.activeTabId);
        if (!tab) tab = this.tabs.find(t => t.type === 'query');
        if (tab) {
            this.switchTab(tab.id);
            setTimeout(() => { if (this.editors[tab.id]) this.editors[tab.id].setValue(sql); }, 100);
        } else {
            this.addTab({ id:'q_'+Date.now(), type:'query', name:'SQL Query', sql:sql });
        }
    },

    // ─── Copy/Export results ─────────────────────
    copyResultsJSON(btn) {
        const table = btn.closest('.db-pane')?.querySelector('.result-table');
        if (!table) return;
        const headers = [...table.querySelectorAll('th')].slice(1).map(h => h.textContent.trim());
        const rows = [...table.querySelectorAll('tbody tr')].map(tr =>
            Object.fromEntries([...tr.querySelectorAll('td')].slice(1).map((td, i) => [headers[i], td.textContent.trim()]))
        );
        navigator.clipboard.writeText(JSON.stringify(rows, null, 2)).then(() => this.toast('Copied JSON!','success'));
    },

    exportResultsCSV(btn) {
        const table = btn.closest('.db-pane')?.querySelector('.result-table');
        if (!table) return;
        const headers = [...table.querySelectorAll('th')].slice(1).map(h => h.textContent.trim());
        const rows = [...table.querySelectorAll('tbody tr')].map(tr =>
            [...tr.querySelectorAll('td')].slice(1).map(td => `"${td.textContent.trim().replace(/"/g,'""')}"`)
        );
        const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
        const blob = new Blob([csv], {type:'text/csv'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'query_results_' + new Date().toISOString().slice(0,19).replace(/:/g,'') + '.csv';
        a.click();
        this.toast('CSV exported!','success');
    },

    // ─── Context menu (enhanced) ────────────────
    showCtxMenu(e, table) {
        e.preventDefault();
        const menu = document.getElementById('ctxMenu');
        const esc = (s) => s.replace(/'/g, "\\'").replace(/`/g, "\\`");
        menu.innerHTML = `
            <div class="ctx-menu-group">Browse</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.openTable('${esc(table)}');DatabaseManager.hideCtxMenu()"><i class="fas fa-eye"></i> View Data</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('SELECT * FROM \`${table}\` LIMIT 100');DatabaseManager.hideCtxMenu()"><i class="fas fa-terminal"></i> SELECT TOP 100</div>
            <div class="ctx-menu-sep"></div>
            <div class="ctx-menu-group">Inspect</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('DESCRIBE \`${table}\`');DatabaseManager.hideCtxMenu()"><i class="fas fa-info-circle"></i> DESCRIBE</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('SHOW CREATE TABLE \`${table}\`');DatabaseManager.hideCtxMenu()"><i class="fas fa-code"></i> Show CREATE TABLE</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('SHOW INDEX FROM \`${table}\`');DatabaseManager.hideCtxMenu()"><i class="fas fa-key"></i> Show Indexes</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('SHOW TABLE STATUS LIKE \\'${esc(table)}\\'');DatabaseManager.hideCtxMenu()"><i class="fas fa-chart-bar"></i> Table Status</div>
            <div class="ctx-menu-sep"></div>
            <div class="ctx-menu-group">Quick Stats</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('SELECT COUNT(*) AS total FROM \`${table}\`');DatabaseManager.hideCtxMenu()"><i class="fas fa-hashtag"></i> Count Rows</div>
            <div class="ctx-menu-sep"></div>
            <div class="ctx-menu-group">Operations</div>
            <div class="ctx-menu-item danger" onclick="DatabaseManager.truncateTable('${esc(table)}');DatabaseManager.hideCtxMenu()"><i class="fas fa-eraser"></i> Truncate Table...</div>
            <div class="ctx-menu-item danger" onclick="DatabaseManager.dropTable('${esc(table)}');DatabaseManager.hideCtxMenu()"><i class="fas fa-trash"></i> Drop Table...</div>
            <div class="ctx-menu-sep"></div>
            <div class="ctx-menu-group">Copy</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.copyText('${esc(table)}');DatabaseManager.hideCtxMenu()"><i class="fas fa-copy"></i> Copy Table Name</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.copyText('SELECT * FROM \`${table}\`');DatabaseManager.hideCtxMenu()"><i class="fas fa-copy"></i> Copy SELECT *</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.copyInsertTemplate('${esc(table)}');DatabaseManager.hideCtxMenu()"><i class="fas fa-copy"></i> Copy INSERT Template</div>
            <div class="ctx-menu-sep"></div>
            <div class="ctx-menu-group">Export</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.exportSingleTable('${esc(table)}');DatabaseManager.hideCtxMenu()"><i class="fas fa-download"></i> Export This Table</div>
        `;
        menu.style.display = 'block';
        menu.style.left = Math.min(e.clientX, window.innerWidth - 220) + 'px';
        menu.style.top = Math.min(e.clientY, window.innerHeight - 520) + 'px';
    },
    hideCtxMenu() { document.getElementById('ctxMenu').style.display = 'none'; },

    // ─── Context menu actions ────────────────────
    copyText(text) {
        navigator.clipboard.writeText(text).then(() => this.toast('Copied!', 'success')).catch(() => {
            // Fallback
            const ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
            this.toast('Copied!', 'success');
        });
    },

    async copyInsertTemplate(table) {
        try {
            const descSql = 'DESCRIBE `' + table + '`';
            const res = await fetch("{{ route('admin.database.query') }}", {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,'X-Requested-With':'XMLHttpRequest'},
                body:JSON.stringify({sql_b64: btoa(descSql)})
            });
            const html = await res.text();
            // Parse column names from response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const rows = doc.querySelectorAll('.result-table tbody tr');
            const cols = [];
            rows.forEach(r => {
                const tds = r.querySelectorAll('td');
                if (tds.length >= 2) cols.push(tds[1]?.textContent?.trim());
            });
            if (cols.length > 0) {
                const colList = cols.map(c => '`' + c + '`').join(', ');
                const valList = cols.map(() => '?').join(', ');
                this.copyText('INSERT INTO `' + table + '` (' + colList + ') VALUES (' + valList + ');');
            } else {
                this.copyText('INSERT INTO `' + table + '` (...) VALUES (...);');
            }
        } catch(e) {
            this.copyText('INSERT INTO `' + table + '` (...) VALUES (...);');
        }
    },

    truncateTable(table) {
        if (!confirm('TRUNCATE table "' + table + '"?\n\nThis will delete ALL rows permanently. This cannot be undone!')) return;
        fetch("{{ url('database/table') }}/" + encodeURIComponent(table) + "/truncate", {
            method:'POST', headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
        }).then(r => r.json()).then(d => {
            if (d.success) { this.toast('Table truncated!', 'success'); setTimeout(() => location.reload(), 500); }
            else this.toast(d.message || 'Truncate failed', 'error');
        }).catch(e => this.toast('Error: ' + e.message, 'error'));
    },

    dropTable(table) {
        const input = prompt('DROP table "' + table + '"?\n\nType the table name to confirm:');
        if (input !== table) { if (input !== null) this.toast('Table name does not match. Drop cancelled.', 'error'); return; }
        fetch("{{ url('database/table') }}/" + encodeURIComponent(table) + "/drop", {
            method:'POST', headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body:JSON.stringify({_method:'DELETE'})
        }).then(r => r.json()).then(d => {
            if (d.success) { this.toast('Table dropped!', 'success'); setTimeout(() => location.reload(), 500); }
            else this.toast(d.message || 'Drop failed', 'error');
        }).catch(e => this.toast('Error: ' + e.message, 'error'));
    },

    exportSingleTable(table) {
        this.openExportTab();
        setTimeout(() => {
            // Uncheck all, then check only this table
            document.querySelectorAll('.exp-check').forEach(c => c.checked = false);
            document.querySelectorAll('.exp-check').forEach(c => { if (c.value === table) c.checked = true; });
            const selAll = document.getElementById('expSelectAll');
            if (selAll) { selAll.checked = false; selAll.indeterminate = true; }
            if (typeof expUpdateEstimate === 'function') expUpdateEstimate();
        }, 200);
    },

    // ─── ER Diagram ─────────────────────────────
    async loadErDiagram(pane) {
        pane.innerHTML = '<div class="loading-overlay"><i class="fas fa-circle-notch fa-spin fa-2x"></i><span>Loading schema...</span></div>';

        try {
            const res = await fetch("{{ route('admin.database.er-diagram') }}", {
                headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
            });
            if (!res.ok) throw new Error('Server returned ' + res.status);
            const data = await res.json();
            this.renderErDiagram(pane, data);
        } catch(e) {
            pane.innerHTML = '<div style="padding:40px;text-align:center"><i class="fas fa-exclamation-triangle" style="font-size:36px;color:var(--c-warning);margin-bottom:12px;display:block"></i><p style="font-size:16px;color:var(--text-muted)">Failed to load ER diagram</p><p style="font-size:14px;color:var(--text-faint)">' + e.message + '</p></div>';
        }
    },

    renderErDiagram(pane, data) {
        const tables = data.tables || [];
        const relations = data.relations || [];
        if (!tables.length) {
            pane.innerHTML = '<div style="padding:40px;text-align:center;color:var(--text-faint)"><i class="fas fa-database" style="font-size:36px;margin-bottom:12px;display:block"></i>No tables found</div>';
            return;
        }

        // ── Layout calculation ──
        // Count connections per table
        const connCount = {};
        tables.forEach(t => connCount[t.name] = 0);
        relations.forEach(r => {
            connCount[r.from_table] = (connCount[r.from_table] || 0) + 1;
            connCount[r.to_table] = (connCount[r.to_table] || 0) + 1;
        });

        // Sort: most connected first
        const sorted = [...tables].sort((a, b) => (connCount[b.name] || 0) - (connCount[a.name] || 0));

        // Grid layout
        const BOX_W = 230, COL_H = 20, HEADER_H = 36, PAD = 16;
        const GAP_X = 120, GAP_Y = 60;
        const cols = Math.max(2, Math.min(6, Math.ceil(Math.sqrt(sorted.length))));

        const positions = {};
        sorted.forEach((t, i) => {
            const row = Math.floor(i / cols);
            const col = i % cols;
            const boxH = HEADER_H + t.columns.length * COL_H + PAD;
            positions[t.name] = {
                x: 60 + col * (BOX_W + GAP_X),
                y: 60 + row * (280 + GAP_Y),
                w: BOX_W,
                h: boxH
            };
        });

        // Calculate canvas size
        const maxX = Math.max(...Object.values(positions).map(p => p.x + p.w)) + 100;
        const maxY = Math.max(...Object.values(positions).map(p => p.y + p.h)) + 100;

        // ── Build HTML ──
        let html = '';

        // Toolbar
        html += '<div style="padding:8px 14px;background:var(--card-bg,#fff);border-bottom:1px solid var(--border-color,var(--border-color));display:flex;justify-content:space-between;align-items:center;flex-shrink:0">';
        html += '<div style="display:flex;align-items:center;gap:10px">';
        html += '<span style="font-size:14px;font-weight:600;color:var(--header-text,var(--text-heading))"><i class="fas fa-project-diagram" style="color:var(--c-danger);margin-right:6px"></i>ER Diagram</span>';
        html += '<span style="font-size:12px;color:var(--text-faint)">' + tables.length + ' tables · ' + relations.length + ' relationships</span>';
        html += '</div>';
        html += '<div style="display:flex;gap:6px;align-items:center">';
        html += '<button class="tb-btn" onclick="erZoom(1.2)" title="Zoom in"><i class="fas fa-search-plus"></i></button>';
        html += '<button class="tb-btn" onclick="erZoom(0.8)" title="Zoom out"><i class="fas fa-search-minus"></i></button>';
        html += '<button class="tb-btn" onclick="erResetView()" title="Reset view"><i class="fas fa-expand"></i> Fit</button>';
        html += '<span style="width:1px;height:20px;background:var(--border-color)"></span>';
        html += '<span style="font-size:11px;color:var(--text-faint)">Scroll to zoom · Drag to pan</span>';
        html += '</div></div>';

        // Legend
        html += '<div style="padding:6px 14px;background:var(--hover-bg);border-bottom:1px solid var(--border-light,var(--border-light));display:flex;gap:16px;align-items:center;font-size:11px;color:var(--text-muted);flex-shrink:0">';
        html += '<span><span style="display:inline-block;width:8px;height:8px;background:var(--c-primary,var(--c-danger));border-radius:2px;margin-right:4px"></span>Primary Key</span>';
        html += '<span><span style="display:inline-block;width:8px;height:8px;background:var(--c-secondary,var(--c-secondary));border-radius:2px;margin-right:4px"></span>Foreign/Index Key</span>';
        html += '<span><svg width="24" height="10" style="vertical-align:middle"><line x1="0" y1="5" x2="24" y2="5" stroke="var(--c-danger)" stroke-width="2"/></svg> FK (explicit)</span>';
        html += '<span><svg width="24" height="10" style="vertical-align:middle"><line x1="0" y1="5" x2="24" y2="5" stroke="var(--text-faint)" stroke-width="1.5" stroke-dasharray="4,3"/></svg> Inferred</span>';
        html += '</div>';

        // Canvas container
        html += '<div id="erViewport" style="flex:1;overflow:hidden;position:relative;cursor:grab;background:var(--table-header-bg,var(--table-header-bg));background-image:radial-gradient(circle,var(--border-color) 1px,transparent 1px);background-size:20px 20px">';
        html += '<div id="erCanvas" style="position:absolute;top:0;left:0;transform-origin:0 0;width:' + maxX + 'px;height:' + maxY + 'px">';

        // SVG layer for connections
        html += '<svg id="erSvg" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none" xmlns="http://www.w3.org/2000/svg">';
        html += '<defs>';
        html += '<marker id="arrowFK" viewBox="0 0 10 6" refX="10" refY="3" markerWidth="8" markerHeight="6" orient="auto"><path d="M0,0 L10,3 L0,6" fill="var(--c-danger)"/></marker>';
        html += '<marker id="arrowInf" viewBox="0 0 10 6" refX="10" refY="3" markerWidth="8" markerHeight="6" orient="auto"><path d="M0,0 L10,3 L0,6" fill="var(--text-faint)"/></marker>';
        html += '</defs>';

        // Draw connection lines
        relations.forEach((rel, idx) => {
            const from = positions[rel.from_table];
            const to = positions[rel.to_table];
            if (!from || !to) return;

            // Find column index for positioning
            const fromTbl = sorted.find(t => t.name === rel.from_table);
            const toTbl = sorted.find(t => t.name === rel.to_table);
            const fromColIdx = fromTbl ? fromTbl.columns.findIndex(c => c.name === rel.from_column) : 0;
            const toColIdx = toTbl ? toTbl.columns.findIndex(c => c.name === rel.to_column) : 0;

            const fromY = from.y + HEADER_H + (Math.max(0, fromColIdx) * COL_H) + COL_H / 2;
            const toY = to.y + HEADER_H + (Math.max(0, toColIdx) * COL_H) + COL_H / 2;

            // Determine which side to connect from
            let x1, x2;
            const fromCenterX = from.x + from.w / 2;
            const toCenterX = to.x + to.w / 2;

            if (fromCenterX < toCenterX) {
                x1 = from.x + from.w; // right side
                x2 = to.x;            // left side
            } else {
                x1 = from.x;          // left side
                x2 = to.x + to.w;     // right side
            }

            const isFK = rel.type === 'FK';
            const color = isFK ? 'var(--c-danger)' : 'var(--text-faint)';
            const dash = isFK ? '' : 'stroke-dasharray="5,4"';
            const marker = isFK ? 'url(#arrowFK)' : 'url(#arrowInf)';

            // Bezier curve
            const midX = (x1 + x2) / 2;
            const cp = Math.max(40, Math.abs(x2 - x1) * 0.4);
            const cx1 = x1 < x2 ? x1 + cp : x1 - cp;
            const cx2 = x1 < x2 ? x2 - cp : x2 + cp;

            html += '<path d="M' + x1 + ',' + fromY + ' C' + cx1 + ',' + fromY + ' ' + cx2 + ',' + toY + ' ' + x2 + ',' + toY + '" fill="none" stroke="' + color + '" stroke-width="' + (isFK ? '2' : '1.5') + '" ' + dash + ' marker-end="' + marker + '" opacity="0.7"/>';
        });
        html += '</svg>';

        // Table boxes
        sorted.forEach(t => {
            const pos = positions[t.name];
            const conns = connCount[t.name] || 0;
            const borderColor = conns > 0 ? 'var(--c-secondary-border)' : 'var(--border-color)';

            html += '<div style="position:absolute;left:' + pos.x + 'px;top:' + pos.y + 'px;width:' + pos.w + 'px;background:var(--card-bg,#fff);border:1.5px solid ' + borderColor + ';border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.04);overflow:hidden;font-size:12px;cursor:pointer" ondblclick="DatabaseManager.openTable(\'' + t.name.replace(/'/g, "\\'") + '\')">';

            // Header
            html += '<div style="padding:8px 12px;background:' + (conns > 0 ? 'var(--c-secondary-light)' : 'var(--table-header-bg)') + ';border-bottom:1.5px solid ' + borderColor + ';display:flex;align-items:center;gap:6px">';
            html += '<i class="fas fa-table" style="font-size:10px;color:' + (conns > 0 ? 'var(--c-secondary)' : 'var(--text-faint)') + '"></i>';
            html += '<span style="font-weight:700;color:var(--header-text,var(--text-heading));font-size:12px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + t.name + '">' + t.name + '</span>';
            if (conns > 0) html += '<span style="font-size:10px;background:var(--c-secondary-light);color:var(--c-secondary);padding:1px 5px;border-radius:3px">' + conns + '</span>';
            html += '</div>';

            // Columns
            t.columns.forEach(col => {
                const isPK = col.key === 'PRI';
                const isFK = col.key === 'MUL' || col.key === 'UNI';
                const keyColor = isPK ? 'var(--c-danger)' : isFK ? 'var(--c-secondary)' : 'transparent';
                const keyIcon = isPK ? 'fa-key' : isFK ? 'fa-link' : '';

                html += '<div style="padding:3px 12px;display:flex;align-items:center;gap:6px;border-bottom:1px solid var(--table-header-bg)">';
                html += '<span style="width:4px;height:4px;border-radius:1px;background:' + keyColor + ';flex-shrink:0"></span>';
                html += '<span style="flex:1;color:var(--text-body);font-family:\'JetBrains Mono\',monospace;font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap' + (isPK ? ';font-weight:600' : '') + '">' + col.name + '</span>';
                html += '<span style="font-size:10px;color:var(--text-faint);white-space:nowrap">' + col.type.replace(/\(.+\)/, '').substring(0, 12) + '</span>';
                if (col.nullable) html += '<span style="font-size:9px;color:var(--hover-border)">?</span>';
                html += '</div>';
            });
            html += '</div>';
        });

        html += '</div></div>'; // erCanvas, erViewport

        pane.innerHTML = html;

        // ── Pan & Zoom ──
        const viewport = pane.querySelector('#erViewport');
        const canvas = pane.querySelector('#erCanvas');
        if (!viewport || !canvas) return;

        let scale = 1, panX = 0, panY = 0, dragging = false, startX = 0, startY = 0;

        // Auto-fit on load
        setTimeout(() => {
            const vw = viewport.clientWidth, vh = viewport.clientHeight;
            if (vw > 0 && maxX > 0) {
                scale = Math.min(vw / maxX, vh / maxY, 1);
                scale = Math.max(0.15, scale);
                panX = Math.max(0, (vw - maxX * scale) / 2);
                panY = 10;
                canvas.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
            }
        }, 50);

        viewport.addEventListener('mousedown', e => {
            if (e.target.closest('[ondblclick]')) return;
            dragging = true; startX = e.clientX - panX; startY = e.clientY - panY;
            viewport.style.cursor = 'grabbing';
        });
        window.addEventListener('mousemove', e => {
            if (!dragging) return;
            panX = e.clientX - startX; panY = e.clientY - startY;
            canvas.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
        });
        window.addEventListener('mouseup', () => { dragging = false; viewport.style.cursor = 'grab'; });
        viewport.addEventListener('wheel', e => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            scale = Math.min(3, Math.max(0.1, scale * delta));
            canvas.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
        }, {passive: false});

        // Global zoom/reset functions
        window.erZoom = (factor) => {
            scale = Math.min(3, Math.max(0.1, scale * factor));
            canvas.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
        };
        window.erResetView = () => {
            const vw = viewport.clientWidth, vh = viewport.clientHeight;
            scale = Math.min(vw / maxX, vh / maxY, 1);
            scale = Math.max(0.15, scale);
            panX = Math.max(0, (vw - maxX * scale) / 2);
            panY = 10;
            canvas.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
        };
    },

    // ─── Sidebar sort ────────────────────────────
    sortSidebar(by, btn) {
        document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const list = document.getElementById('tableList');
        const items = [...list.querySelectorAll('.tbl-item')];

        items.sort((a, b) => {
            if (by === 'name') return a.dataset.name.localeCompare(b.dataset.name);
            if (by === 'rows') return parseInt(b.dataset.rows) - parseInt(a.dataset.rows);
            if (by === 'size') return parseInt(b.dataset.size) - parseInt(a.dataset.size);
            return 0;
        });

        items.forEach(item => list.appendChild(item));
    },

    // ─── Table pagination & sorting ─────────────
    loadTable(table, page, perPage) {
        const tab = this.tabs.find(t => t.type === 'table' && t.table === table);
        if (tab) { tab.page = page; tab.perPage = perPage; this.saveState();
            const pane = document.getElementById('pane-' + tab.id);
            if (pane) { pane.innerHTML = '<div class="loading-overlay"><i class="fas fa-circle-notch fa-spin fa-2x"></i><span>Loading...</span></div>'; this.loadTableContent(tab, pane); }
        }
    },
    changePerPage(table, val) { this.loadTable(table, 1, val); },

    sortTable(table, col, dir) {
        const tab = this.tabs.find(t => t.type === 'table' && t.table === table);
        if (tab) {
            tab.sort = col || '';
            tab.sortDir = dir || 'asc';
            tab.page = 1; // reset to page 1 on sort
            this.saveState();
            const pane = document.getElementById('pane-' + tab.id);
            if (pane) { pane.innerHTML = '<div class="loading-overlay"><i class="fas fa-circle-notch fa-spin fa-2x"></i><span>Sorting...</span></div>'; this.loadTableContent(tab, pane); }
        }
    },
    filterSummaryTables(q) { q=q.toLowerCase(); document.querySelectorAll('.summary-row').forEach(r => r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none'); },

    // ─── Inline editing ──────────────────────────
    currentEditCell: null,
    handleEdit(cell) {
        if (this.currentEditCell === cell) return;
        if (this.currentEditCell) this.cancelEdit();
        this.currentEditCell = cell;
        const original = cell.dataset.original;
        cell.classList.add('editing');
        const useTA = original.length > 80;
        const safeVal = original.replace(/"/g, '&quot;');
        cell.innerHTML = useTA ? `<textarea class="edit-input">${safeVal}</textarea>` : `<input type="text" class="edit-input" value="${safeVal}">`;
        const input = cell.querySelector('.edit-input');
        input.focus();
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); this.saveEdit(cell, input.value); }
            else if (e.key === 'Escape') this.cancelEdit();
        });
        input.addEventListener('blur', () => setTimeout(() => { if (this.currentEditCell === cell) this.saveEdit(cell, input.value); }, 200));
    },
    async saveEdit(cell, newValue) {
        const original = cell.dataset.original;
        if (newValue === original) { this.cancelEdit(); return; }
        cell.classList.remove('editing');
        cell.innerHTML = '<i class="fas fa-spinner fa-spin" style="color:var(--text-faint)"></i>';
        const tableEl = cell.closest('table');
        try {
            const res = await fetch(tableEl.dataset.updateUrl, {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json'},
                body:JSON.stringify({ column:cell.dataset.column, value:newValue, pk_column:cell.closest('tr').dataset.pkCol, pk_value:cell.closest('tr').dataset.pkVal })
            });
            const data = await res.json();
            if (data.success) {
                cell.dataset.original = newValue;
                cell.dataset.isNull = (!newValue) ? '1' : '0';
                cell.textContent = newValue || 'NULL';
                if (!newValue) cell.classList.add('null-val'); else cell.classList.remove('null-val');
                cell.classList.add('edit-success'); setTimeout(() => cell.classList.remove('edit-success'), 800);
            } else throw new Error(data.message);
        } catch(e) {
            cell.textContent = original || 'NULL';
            cell.classList.add('edit-error'); setTimeout(() => cell.classList.remove('edit-error'), 800);
        }
        this.currentEditCell = null;
    },
    cancelEdit() {
        if (!this.currentEditCell) return;
        const cell = this.currentEditCell, original = cell.dataset.original, isNull = cell.dataset.isNull === '1';
        cell.classList.remove('editing');
        cell.innerHTML = isNull ? '<span class="null-val">NULL</span>' : (original.length > 80 ? original.substring(0,80)+'...' : original);
        this.currentEditCell = null;
    },

    async deleteRow(btn) {
        if (!confirm('Delete this row?')) return;
        const tableEl = btn.closest('table');
        const row = btn.closest('tr');
        const tab = this.tabs.find(t => t.id === this.activeTabId);
        try {
            const res = await fetch(tableEl.dataset.deleteUrl, {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                body:JSON.stringify({ pk_column:row.dataset.pkCol, pk_value:row.dataset.pkVal, page:tab?.page||1 })
            });
            const data = await res.json();
            if (data.success) this.loadTable(tab.table, tab.page||1, tab.perPage||50);
        } catch(e) { alert('Delete failed: '+e.message); }
    },

    // ─── Import / Export handlers ────────────────
    initImportTab(pane) {
        const zone = pane.querySelector('.imp-drop-zone');
        const fileInput = pane.querySelector('.import-file-input');
        const btn = pane.querySelector('.imp-btn-run');
        const fileInfo = pane.querySelector('.imp-file-info');
        const fileName = pane.querySelector('.imp-file-name');
        const fileMeta = pane.querySelector('.imp-file-meta');
        const removeBtn = pane.querySelector('.imp-file-remove');

        if (!zone || !fileInput) return;

        const formatSize = (b) => b >= 1048576 ? (b/1048576).toFixed(2)+' MB' : b >= 1024 ? (b/1024).toFixed(1)+' KB' : b+' B';

        const selectFile = (file) => {
            if (!file) return;
            // Validate type
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['sql','txt'].includes(ext)) {
                alert('Only .sql and .txt files are supported.'); return;
            }
            // Create a DataTransfer to set the file input
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            // Update UI
            if (fileName) fileName.textContent = file.name;
            if (fileMeta) fileMeta.textContent = formatSize(file.size) + ' · ' + ext.toUpperCase() + ' file';
            if (fileInfo) fileInfo.style.display = 'block';
            zone.classList.add('has-file');
            if (btn) btn.disabled = false;
        };

        const clearFile = () => {
            fileInput.value = '';
            if (fileInfo) fileInfo.style.display = 'none';
            zone.classList.remove('has-file');
            if (btn) btn.disabled = true;
        };

        // Click to browse
        zone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', () => { if (fileInput.files[0]) selectFile(fileInput.files[0]); });

        // Drag & drop
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault(); zone.classList.remove('dragover');
            if (e.dataTransfer.files[0]) selectFile(e.dataTransfer.files[0]);
        });

        // Remove file
        if (removeBtn) removeBtn.addEventListener('click', (e) => { e.stopPropagation(); clearFile(); });
    },

    async handleImport(e, form) {
        e.preventDefault();
        const btn = form.querySelector('.imp-btn-run') || form.querySelector('button[type="submit"]');
        const pane = form.closest('.import-tab-content');
        const results = pane ? pane.querySelector('.import-results') : form.nextElementSibling;
        if (!results) { alert('Import results container not found'); return; }

        const formData = new FormData(form);
        const file = formData.get('sql_file');
        if (!file || !file.name || file.size === 0) {
            results.innerHTML = '<div class="imp-result-header err"><i class="fas fa-exclamation-circle"></i> No file selected. Please choose a .sql file first.</div>';
            return;
        }

        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
        results.innerHTML = '<div class="imp-result-header running"><i class="fas fa-circle-notch fa-spin"></i> Importing ' + file.name + '...</div>';

        try {
            const res = await fetch("{{ route('admin.database.import') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": CSRF_TOKEN, "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
                body: formData
            });

            let data;
            const ct = res.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
                data = await res.json();
            } else {
                // Validation error (422) returns HTML/text
                const text = await res.text();
                let msg = 'Import failed (HTTP ' + res.status + ')';
                try {
                    const json = JSON.parse(text);
                    msg = json.message || Object.values(json.errors || {}).flat().join(', ') || msg;
                } catch(pe) { msg = text.substring(0, 300) || msg; }
                data = { success: false, summary: msg, log: [], ok: 0, errors: 1 };
            }

            results.innerHTML = this.renderImportResults(data);

        } catch(err) {
            results.innerHTML = '<div class="imp-result-header err"><i class="fas fa-times-circle"></i> Network error: ' + err.message + '</div>';
        } finally {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload"></i> Import Now';
        }
    },

    renderImportResults(data) {
        const icons = { ok:'check-circle', err:'times-circle', info:'info-circle', warn:'exclamation-triangle' };
        let html = '';

        // Header
        const hdrClass = data.success ? 'ok' : 'err';
        const hdrIcon = data.success ? 'check-circle' : 'times-circle';
        html += `<div class="imp-result-header ${hdrClass}"><i class="fas fa-${hdrIcon}"></i> ${data.summary || 'Import completed'}</div>`;

        // Stats
        html += '<div class="imp-stats">';
        html += `<div class="imp-stat ok"><i class="fas fa-check"></i> Succeeded: <strong>${data.ok || 0}</strong></div>`;
        if (data.errors > 0) html += `<div class="imp-stat err"><i class="fas fa-times"></i> Failed: <strong>${data.errors}</strong></div>`;
        if (data.skipped > 0) html += `<div class="imp-stat"><i class="fas fa-forward"></i> Skipped: <strong>${data.skipped}</strong></div>`;
        html += `<div class="imp-stat"><i class="fas fa-clock"></i> Time: <strong>${data.elapsed_ms || 0}ms</strong></div>`;
        if (data.file_name) html += `<div class="imp-stat"><i class="fas fa-file"></i> ${data.file_name} (${data.file_size || ''})</div>`;
        html += '</div>';

        // Log
        if (data.log && data.log.length > 0) {
            const hasErrors = data.log.some(l => l.type === 'err');
            html += '<div class="imp-log-wrap">';
            html += '<div class="imp-log-header">';
            html += '<span class="imp-log-title"><i class="fas fa-list"></i> Execution Log (' + data.log.length + ' entries)</span>';
            if (hasErrors) {
                html += '<div class="imp-log-filter">';
                html += '<button class="active" onclick="impFilterLog(this,\'all\')">All</button>';
                html += '<button onclick="impFilterLog(this,\'err\')">Errors Only</button>';
                html += '<button onclick="impFilterLog(this,\'ok\')">Success Only</button>';
                html += '</div>';
            }
            html += '</div>';
            html += '<div class="imp-log">';
            data.log.forEach(entry => {
                const icon = icons[entry.type] || 'chevron-right';
                const numHtml = entry.num ? `<span class="imp-log-num">#${entry.num}</span>` : `<span class="imp-log-num"></span>`;
                let msgHtml = `<span class="imp-log-msg">${this.escHtml(entry.msg)}`;
                if (entry.error) {
                    msgHtml += `<span class="imp-log-err">${this.escHtml(entry.error)}</span>`;
                }
                msgHtml += '</span>';
                html += `<div class="imp-log-row type-${entry.type}" data-type="${entry.type}"><i class="fas fa-${icon}"></i>${numHtml}${msgHtml}</div>`;
            });
            html += '</div></div>';
        }

        return html;
    },

    escHtml(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    },
    handleExport(e, form) {
        e.preventDefault();
        const tempForm = document.createElement('form');
        tempForm.method = 'POST'; tempForm.action = "{{ route('admin.database.export') }}";
        tempForm.innerHTML = `@csrf`;
        for (const [k,v] of new FormData(form).entries()) { const i = document.createElement('input'); i.type='hidden'; i.name=k; i.value=v; tempForm.appendChild(i); }
        document.body.appendChild(tempForm); tempForm.submit(); document.body.removeChild(tempForm);
    },

    // ─── Toast ───────────────────────────────────
    toast(msg, type='info') {
        const t = document.createElement('div');
        t.style.cssText = `position:fixed;top:20px;right:20px;padding:10px 18px;border-radius:8px;color:#fff;font-size:15px;z-index:9999;display:flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:slideIn .3s ease`;
        t.style.background = type==='success'?'var(--c-success)':type==='error'?'var(--c-danger)':'var(--c-danger)';
        t.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'times-circle':'info-circle'}"></i> ${msg}`;
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
    }
};

// ─── Event delegation ────────────────────────────
document.addEventListener('click', e => {
    DatabaseManager.hideCtxMenu();
    // Hide template dropdown if clicking outside
    const dd = document.getElementById('templateDropdown');
    if (dd && dd.style.display === 'block' && !e.target.closest('.tpl-dropdown') && !e.target.closest('.tb-btn-tpl')) dd.style.display = 'none';
    const cell = e.target.closest('.editable');
    if (cell) { DatabaseManager.handleEdit(cell); return; }
    const del = e.target.closest('.btn-delete-row');
    if (del) { e.preventDefault(); e.stopPropagation(); DatabaseManager.deleteRow(del); }
});

document.addEventListener('DOMContentLoaded', () => DatabaseManager.init());

// ── Import log filter (global — called from onclick) ──
function impFilterLog(btn, type) {
    const wrap = btn.closest('.imp-log-wrap');
    if (!wrap) return;
    // Toggle active button
    wrap.querySelectorAll('.imp-log-filter button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    // Show/hide rows
    wrap.querySelectorAll('.imp-log-row').forEach(row => {
        if (type === 'all') { row.style.display = ''; }
        else { row.style.display = row.dataset.type === type ? '' : 'none'; }
    });
}
</script>
<style>@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}</style>
@endpush
