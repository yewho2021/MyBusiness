@extends('admin.layouts.app')
@section('title', 'Database Manager')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════
   DATABASE MANAGER — Enhanced UI
   ═══════════════════════════════════════════════ */

/* Layout */
.db-container { display:flex; height:calc(100vh - 120px); gap:0; background:#fff; border-radius:10px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04); }

/* ── Sidebar ────────────────────────────────── */
.db-sidebar { width:270px; min-width:270px; background:#fafbfd; border-right:1px solid #e2e8f0; display:flex; flex-direction:column; }
.db-sidebar-header { padding:14px; border-bottom:1px solid #e2e8f0; }
.db-sidebar-title { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.6px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; }
.db-sidebar-title .db-badge { font-size:10px; background:#e2e8f0; color:#64748b; padding:2px 7px; border-radius:10px; font-weight:600; }

.db-tools { display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:14px; }
.tool-btn { background:#fff; color:#475569; border:1px solid #e2e8f0; padding:8px 10px; border-radius:7px; font-size:12px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:7px; transition:all .15s; }
.tool-btn:hover { background:#f1f5f9; color:#4f46e5; border-color:#c7d2fe; }
.tool-btn i { font-size:11px; color:#94a3b8; }
.tool-btn:hover i { color:#4f46e5; }

.table-search { width:100%; padding:8px 10px 8px 32px; border:1px solid #e2e8f0; border-radius:7px; font-size:12px; outline:none; background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.44.856a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") 10px center/13px no-repeat; transition:all .2s; }
.table-search:focus { border-color:#818cf8; box-shadow:0 0 0 3px rgba(99,102,241,.1); }

.table-list { flex:1; overflow-y:auto; padding:4px 0; }
.table-list::-webkit-scrollbar { width:5px; }
.table-list::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:3px; }

.tbl-item { padding:7px 14px; font-size:12px; color:#475569; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all .15s; border-left:3px solid transparent; }
.tbl-item:hover { background:#f1f5f9; color:#4f46e5; border-left-color:#c7d2fe; }
.tbl-item.active { background:#eef2ff; color:#4f46e5; border-left-color:#4f46e5; font-weight:500; }
.tbl-item i.fa-table { font-size:10px; color:#c7d2fe; }
.tbl-item:hover i.fa-table { color:#818cf8; }
.tbl-item .tbl-meta { margin-left:auto; font-size:10px; color:#cbd5e1; font-weight:400; white-space:nowrap; }
.tbl-item:hover .tbl-meta { color:#a5b4fc; }

/* ── Main workspace ─────────────────────────── */
.db-main { flex:1; display:flex; flex-direction:column; overflow:hidden; }

/* Tab bar */
.db-tabs { display:flex; background:#f8fafc; border-bottom:1px solid #e2e8f0; overflow-x:auto; min-height:40px; }
.db-tabs::-webkit-scrollbar { height:2px; }
.db-tabs::-webkit-scrollbar-thumb { background:#c7d2fe; }

.db-tab { padding:10px 14px; font-size:12px; font-weight:500; color:#64748b; cursor:pointer; display:flex; align-items:center; gap:7px; white-space:nowrap; border-bottom:2px solid transparent; transition:all .15s; position:relative; }
.db-tab:hover { background:#fff; color:#334155; }
.db-tab.active { background:#fff; color:#4f46e5; border-bottom-color:#4f46e5; }
.db-tab i.tab-icon { font-size:11px; }
.db-tab .tab-close { font-size:10px; padding:3px; border-radius:4px; opacity:0; margin-left:4px; transition:all .15s; }
.db-tab:hover .tab-close { opacity:.6; }
.db-tab .tab-close:hover { opacity:1; background:#fee2e2; color:#ef4444; }

/* Content area */
.db-content { flex:1; overflow:hidden; position:relative; }
.db-pane { display:none; height:100%; overflow:auto; }
.db-pane.active { display:flex; flex-direction:column; }

/* ── Query Editor ───────────────────────────── */
.sql-editor-wrap { position:relative; }
.monaco-sql-wrap { background:#1e293b; }

.toolbar { padding:8px 14px; background:#fff; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
.tb-btn { background:#f8fafc; color:#475569; border:1px solid #e2e8f0; padding:5px 10px; border-radius:5px; font-size:11px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all .15s; }
.tb-btn:hover { background:#eef2ff; color:#4f46e5; border-color:#c7d2fe; }
.shortcut-hints { font-size:10px; color:#cbd5e1; }
.shortcut-hints kbd { background:#f1f5f9; border:1px solid #e2e8f0; padding:1px 5px; border-radius:3px; font-size:10px; font-family:'JetBrains Mono',monospace; }

.btn-run { background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; border:none; padding:7px 16px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all .2s; box-shadow:0 2px 6px rgba(34,197,94,.25); }
.btn-run:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(34,197,94,.35); }
.btn-run:active { transform:translateY(0); }
.btn-bookmark { background:transparent; border:none; cursor:pointer; }

/* ── Results ─────────────────────────────────── */
.result-msg { padding:10px 16px; font-size:13px; display:flex; align-items:center; gap:8px; }
.result-msg.error { background:#fef2f2; color:#991b1b; border-bottom:1px solid #fecaca; }
.result-msg.success { background:#f0fdf4; color:#166534; border-bottom:1px solid #bbf7d0; }
.result-time { font-size:11px; opacity:.7; margin-left:4px; }
.result-header { padding:8px 14px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
.result-badge { background:#eef2ff; color:#4338ca; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
.result-stats { display:flex; align-items:center; gap:10px; }
.result-wrap { overflow:auto; flex:1; }

.result-table { width:100%; border-collapse:collapse; white-space:nowrap; font-size:12px; }
.result-table thead { position:sticky; top:0; z-index:2; }
.result-table th { background:#f8fafc; padding:8px 12px; font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.4px; border-bottom:2px solid #e2e8f0; text-align:left; }
.result-table td { padding:7px 12px; color:#334155; border-bottom:1px solid #f1f5f9; font-family:'JetBrains Mono',monospace; font-size:11px; max-width:300px; overflow:hidden; text-overflow:ellipsis; }
.result-table tbody tr:hover td { background:#fafbfe; }
.result-table .row-num { color:#cbd5e1; font-size:10px; width:40px; text-align:center; }
.result-table .null-val { color:#d1d5db; font-style:italic; }
.result-table .null-val i { font-style:italic; }
.result-table .num-val { color:#0369a1; text-align:right; }

.empty-results { text-align:center; padding:60px 20px; color:#94a3b8; }
.empty-results i { font-size:36px; display:block; margin-bottom:12px; color:#cbd5e1; }
.empty-results p { font-size:14px; margin-bottom:6px; }
.empty-results span { font-size:12px; }

/* ── Status bar ──────────────────────────────── */
.db-statusbar { height:28px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; align-items:center; padding:0 14px; gap:16px; font-size:11px; color:#94a3b8; }
.db-statusbar .status-item { display:flex; align-items:center; gap:5px; }
.db-statusbar .status-dot { width:6px; height:6px; border-radius:50%; background:#22c55e; }

/* ── Summary tab ─────────────────────────────── */
.summary-tab-content { padding:20px; overflow:auto; }
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
.stat-card { background:#fff; border-radius:10px; padding:16px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:12px; transition:all .2s; }
.stat-card:hover { border-color:#c7d2fe; box-shadow:0 2px 8px rgba(99,102,241,.08); }
.stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; }
.stat-icon.blue { background:#eff6ff; color:#3b82f6; }
.stat-icon.green { background:#f0fdf4; color:#22c55e; }
.stat-icon.purple { background:#faf5ff; color:#a855f7; }
.stat-icon.amber { background:#fffbeb; color:#f59e0b; }
.stat-val { font-size:18px; font-weight:700; color:#1e293b; }
.stat-label { font-size:11px; color:#64748b; }

.card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; }
.card-header { padding:12px 16px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#fafbfd; }
.card-title { font-size:13px; font-weight:600; color:#1e293b; }
.search-box { padding:6px 10px; border:1px solid #e2e8f0; border-radius:6px; font-size:12px; outline:none; }
.search-box:focus { border-color:#818cf8; }

.summary-table { width:100%; border-collapse:collapse; }
.summary-table th { text-align:left; background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:8px 14px; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; }
.summary-table td { border-bottom:1px solid #f1f5f9; padding:8px 14px; font-size:12px; color:#475569; }
.summary-row:hover td { background:#fafbfe; }
.tname { font-weight:600; color:#4f46e5; text-decoration:none; font-size:12px; }
.tname:hover { text-decoration:underline; }
.btn-xs { padding:4px 10px; font-size:11px; border-radius:5px; cursor:pointer; border:none; font-weight:500; display:inline-flex; align-items:center; gap:4px; }
.btn-blue { background:#eef2ff; color:#4f46e5; }
.btn-blue:hover { background:#e0e7ff; }
.btn-red { background:#fef2f2; color:#ef4444; }
.btn-red:hover { background:#fee2e2; }

/* ── Table view ──────────────────────────────── */
.data-table-wrap { overflow:auto; flex:1; }
.data-table { width:100%; border-collapse:collapse; white-space:nowrap; }
.data-table th { position:sticky; top:0; background:#f8fafc; z-index:1; padding:8px 12px; font-size:10px; font-weight:700; color:#64748b; border-bottom:2px solid #e2e8f0; text-transform:uppercase; text-align:left; }
.data-table td { padding:7px 12px; font-size:12px; color:#334155; border-bottom:1px solid #f1f5f9; max-width:250px; overflow:hidden; text-overflow:ellipsis; font-family:'JetBrains Mono',monospace; font-size:11px; }
.data-table tbody tr:hover td { background:#fafbfe; }
span.null-val,.data-table .null-val { color:#d1d5db; font-style:italic; font-size:11px; }
.editable { cursor:pointer; }
.editable:hover { background:#eef2ff !important; outline:1px dashed #c7d2fe; outline-offset:-1px; }
.editing { padding:0 !important; }
.edit-input { width:100%; padding:7px 10px; border:2px solid #4f46e5; font-size:12px; font-family:'JetBrains Mono',monospace; outline:none; border-radius:0; }
.edit-success { animation:editFlash .6s ease; }
.edit-error { animation:editError .6s ease; }
@keyframes editFlash { 0%{background:#d1fae5} 100%{background:transparent} }
@keyframes editError { 0%{background:#fee2e2} 100%{background:transparent} }

/* Pagination */
.pager { padding:10px 16px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; font-size:12px; color:#64748b; flex-wrap:wrap; gap:8px; }
.pager a,.pager .current,.pager .pager-btn { display:inline-flex; align-items:center; justify-content:center; min-width:30px; height:30px; padding:0 8px; border-radius:5px; font-size:12px; text-decoration:none; }
.pager a { background:#fff; color:#4f46e5; border:1px solid #e2e8f0; }
.pager a:hover { background:#eef2ff; border-color:#c7d2fe; }
.pager .current { background:#4f46e5; color:#fff; font-weight:600; }
.pager .disabled { color:#d1d5db; }
.per-page-select { padding:4px 8px; border:1px solid #e2e8f0; border-radius:5px; font-size:12px; outline:none; }

/* ── Loading ─────────────────────────────────── */
.loading-overlay { position:absolute; inset:0; background:rgba(255,255,255,.85); display:flex; justify-content:center; align-items:center; z-index:10; flex-direction:column; gap:8px; }
.loading-overlay i { color:#4f46e5; }
.loading-overlay span { font-size:12px; color:#64748b; }

/* ── History/Import/Export ────────────────────── */
.history-tab-content,.import-tab-content,.export-tab-content { padding:20px; overflow:auto; }
.upload-zone { border:2px dashed #d1d5db; border-radius:10px; padding:40px; text-align:center; cursor:pointer; transition:all .2s; }
.upload-zone:hover { border-color:#818cf8; background:#fafbfe; }
.warning-box { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px 16px; font-size:13px; color:#92400e; margin-bottom:20px; }
.success-box { background:#f0fdf4; border:1px solid #bbf7d0; padding:12px; border-radius:8px; color:#166534; }
.error-box { background:#fef2f2; border:1px solid #fecaca; padding:12px; border-radius:8px; color:#991b1b; }

/* ── Context menu ────────────────────────────── */
.ctx-menu { position:fixed; background:#fff; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:4px; z-index:9999; min-width:180px; }
.ctx-menu-item { padding:8px 12px; font-size:12px; cursor:pointer; border-radius:5px; display:flex; align-items:center; gap:8px; color:#334155; }
.ctx-menu-item:hover { background:#f1f5f9; color:#4f46e5; }
.ctx-menu-item i { width:14px; color:#94a3b8; font-size:11px; }
.ctx-menu-item:hover i { color:#4f46e5; }
.ctx-menu-sep { height:1px; background:#e2e8f0; margin:4px 0; }
</style>
@endpush

@section('content')
<div class="db-container">
    {{-- Sidebar --}}
    <div class="db-sidebar">
        <div class="db-sidebar-header">
            <div class="db-sidebar-title">
                <span><i class="fas fa-database" style="margin-right:4px"></i> {{ $dbName }}</span>
                <span class="db-badge">{{ count($tableList) }} tables</span>
            </div>
            <div class="db-tools">
                <button class="tool-btn" onclick="DatabaseManager.openQueryTab()"><i class="fas fa-plus"></i> New SQL</button>
                <button class="tool-btn" onclick="DatabaseManager.openHistoryTab()"><i class="fas fa-history"></i> History</button>
                <button class="tool-btn" onclick="DatabaseManager.openImportTab()"><i class="fas fa-upload"></i> Import</button>
                <button class="tool-btn" onclick="DatabaseManager.openExportTab()"><i class="fas fa-download"></i> Export</button>
            </div>
            <input type="text" class="table-search" placeholder="Filter tables..." onkeyup="DatabaseManager.filterTables(this.value)">
        </div>
        <div class="table-list" id="tableList">
            @foreach($tableList as $t)
            <div class="tbl-item" ondblclick="DatabaseManager.openTable('{{ $t['name'] }}')" oncontextmenu="DatabaseManager.showCtxMenu(event,'{{ $t['name'] }}')" title="{{ $t['name'] }} — {{ number_format($t['rows']) }} rows">
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
            <div class="status-item"><span class="status-dot"></span> Connected</div>
            <div class="status-item"><i class="fas fa-server"></i> {{ config('database.connections.mysql.host') }}:{{ config('database.connections.mysql.port', '3306') }}</div>
            <div class="status-item"><i class="fas fa-database"></i> {{ $dbName }}</div>
            <div class="status-item" style="margin-left:auto"><i class="fas fa-hdd"></i> {{ $totalSize >= 1048576 ? number_format($totalSize/1048576,1).' MB' : number_format($totalSize/1024,1).' KB' }}</div>
        </div>
    </div>
</div>

{{-- Context menu (hidden) --}}
<div id="ctxMenu" class="ctx-menu" style="display:none"></div>

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

// Monaco setup
let monacoReady = false;
require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs' } });
require(['vs/editor/editor.main'], () => {
    monacoReady = true;
    // Register SQL completions with table names
    monaco.languages.registerCompletionItemProvider('sql', {
        provideCompletionItems: (model, position) => {
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
        const stored = localStorage.getItem('db_tabs');
        if (stored) { try { this.tabs = JSON.parse(stored); } catch(e) {} }
        this.tabs = this.tabs.filter(t => t.type !== 'summary');
        this.tabs.unshift({ type:'summary', name:'Overview', id:'tab_summary_static' });
        this.renderTabs();
        const active = localStorage.getItem('db_active_tab');
        this.switchTab((active && this.tabs.find(t => t.id === active)) ? active : this.tabs[0].id);
    },

    saveState() {
        const meta = this.tabs.map(t => ({
            id:t.id, type:t.type, name:t.name, table:t.table, page:t.page, perPage:t.perPage,
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
        const icons = { summary:'database', query:'terminal', table:'table', import:'upload', export:'download', history:'history' };
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
                        fontSize: 14,
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
    },

    async loadTableContent(tab, pane) {
        const url = `{{ route('admin.database.table', ':table') }}`.replace(':table', tab.table);
        try {
            const res = await fetch(`${url}?page=${tab.page||1}&perPage=${tab.perPage||50}`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
            pane.innerHTML = await res.text();
        } catch(e) { pane.innerHTML = `<div class="error-box" style="margin:20px">Failed: ${e.message}</div>`; }
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

        resultsDiv.innerHTML = '<div style="text-align:center;padding:30px"><i class="fas fa-circle-notch fa-spin fa-lg" style="color:#4f46e5"></i><div style="margin-top:8px;font-size:12px;color:#94a3b8">Executing query...</div></div>';

        try {
            const res = await fetch("{{ route('admin.database.query') }}", {
                method:"POST", headers:{"Content-Type":"application/json","X-CSRF-TOKEN":CSRF_TOKEN,"X-Requested-With":"XMLHttpRequest"},
                body:JSON.stringify({sql})
            });
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

    // ─── Context menu ────────────────────────────
    showCtxMenu(e, table) {
        e.preventDefault();
        const menu = document.getElementById('ctxMenu');
        menu.innerHTML = `
            <div class="ctx-menu-item" onclick="DatabaseManager.openTable('${table}');DatabaseManager.hideCtxMenu()"><i class="fas fa-eye"></i> View Data</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('SELECT * FROM \`${table}\` LIMIT 100');DatabaseManager.hideCtxMenu()"><i class="fas fa-terminal"></i> SELECT * LIMIT 100</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('DESCRIBE \`${table}\`');DatabaseManager.hideCtxMenu()"><i class="fas fa-info-circle"></i> DESCRIBE</div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('SHOW CREATE TABLE \`${table}\`');DatabaseManager.hideCtxMenu()"><i class="fas fa-code"></i> Show CREATE</div>
            <div class="ctx-menu-sep"></div>
            <div class="ctx-menu-item" onclick="DatabaseManager.useQuery('SELECT COUNT(*) as total FROM \`${table}\`');DatabaseManager.hideCtxMenu()"><i class="fas fa-hashtag"></i> Count Rows</div>
        `;
        menu.style.display = 'block';
        menu.style.left = Math.min(e.clientX, window.innerWidth - 200) + 'px';
        menu.style.top = Math.min(e.clientY, window.innerHeight - 250) + 'px';
    },
    hideCtxMenu() { document.getElementById('ctxMenu').style.display = 'none'; },

    // ─── Table pagination ────────────────────────
    loadTable(table, page, perPage) {
        const tab = this.tabs.find(t => t.type === 'table' && t.table === table);
        if (tab) { tab.page = page; tab.perPage = perPage; this.saveState();
            const pane = document.getElementById('pane-' + tab.id);
            if (pane) { pane.innerHTML = '<div class="loading-overlay"><i class="fas fa-circle-notch fa-spin fa-2x"></i><span>Loading...</span></div>'; this.loadTableContent(tab, pane); }
        }
    },
    changePerPage(table, val) { this.loadTable(table, 1, val); },
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
        cell.innerHTML = '<i class="fas fa-spinner fa-spin" style="color:#94a3b8"></i>';
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
        const tab = this.tabs.find(t => t.id === this.activeTabId);
        try {
            const res = await fetch(tableEl.dataset.deleteUrl, {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
                body:JSON.stringify({ where:btn.dataset.where, page:tab?.page||1 })
            });
            const data = await res.json();
            if (data.success) this.loadTable(tab.table, tab.page||1, tab.perPage||50);
        } catch(e) { alert('Delete failed: '+e.message); }
    },

    // ─── Import / Export handlers ────────────────
    showImportFileName(input) {
        if (input.files.length > 0) {
            input.closest('.upload-zone')?.querySelector('.file-name') && (input.closest('div').querySelector('.file-name').textContent = 'Selected: ' + input.files[0].name);
            const btn = input.closest('form')?.querySelector('.btn-run');
            if (btn) btn.disabled = false;
        }
    },
    async handleImport(e, form) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const results = form.nextElementSibling;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
        results.innerHTML = '';
        try {
            const res = await fetch("{{ route('admin.database.import') }}", {
                method:"POST", headers:{"X-CSRF-TOKEN":CSRF_TOKEN,"X-Requested-With":"XMLHttpRequest"}, body:new FormData(form)
            });
            results.innerHTML = await res.text();
        } catch(e) { results.innerHTML = `<div class="error-box">Import failed: ${e.message}</div>`; }
        finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload"></i> Import Now'; }
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
        t.style.cssText = `position:fixed;top:20px;right:20px;padding:10px 18px;border-radius:8px;color:#fff;font-size:13px;z-index:9999;display:flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:slideIn .3s ease`;
        t.style.background = type==='success'?'#16a34a':type==='error'?'#dc2626':'#4f46e5';
        t.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'times-circle':'info-circle'}"></i> ${msg}`;
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
    }
};

// ─── Event delegation ────────────────────────────
document.addEventListener('click', e => {
    DatabaseManager.hideCtxMenu();
    const cell = e.target.closest('.editable');
    if (cell) { DatabaseManager.handleEdit(cell); return; }
    const del = e.target.closest('.btn-delete-row');
    if (del) { e.preventDefault(); e.stopPropagation(); DatabaseManager.deleteRow(del); }
});

document.addEventListener('DOMContentLoaded', () => DatabaseManager.init());
</script>
<style>@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}</style>
@endpush
