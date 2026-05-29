@extends('admin.layouts.app')

@section('title', 'File Manager')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ url('vendor-asset/file-manager/css/file-manager.css') }}">
<style>
/* ═══════════════════════════════════════════════
   FILE MANAGER — Enhanced Red & Black Theme
   ═══════════════════════════════════════════════ */

.fm-wrapper { background:#fff; border-radius:10px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06); }

/* ── Action Toolbar ─────────────────────────── */
.fm-action-bar { display:flex; align-items:center; gap:8px; padding:10px 16px; background:#111; border-bottom:1px solid #222; flex-wrap:wrap; }
.fm-action-bar .bar-title { font-size:14px; font-weight:600; color:#fff; display:flex; align-items:center; gap:8px; margin-right:8px; }
.fm-action-bar .bar-title i { color:#2563eb; font-size:16px; }
.fm-action-bar .sep { width:1px; height:24px; background:#333; margin:0 4px; }
.fm-action-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:6px; border:1px solid #333; background:#1a1a1a; color:#d1d5db; font-size:13px; font-weight:500; cursor:pointer; transition:all .15s; white-space:nowrap; }
.fm-action-btn:hover { background:#dc2626; color:#fff; border-color:#dc2626; }
.fm-action-btn.danger:hover { background:#991b1b; border-color:#991b1b; }
.fm-action-btn i { font-size:13px; }
.fm-action-btn.primary { background:#dc2626; color:#fff; border-color:#dc2626; }
.fm-action-btn.primary:hover { background:#b91c1c; border-color:#b91c1c; }
.fm-path-display { flex:1; min-width:200px; display:flex; align-items:center; gap:6px; padding:6px 12px; background:#1a1a1a; border:1px solid #333; border-radius:6px; color:#9ca3af; font-size:12px; font-family:'JetBrains Mono',monospace; overflow:hidden; }
.fm-path-display i { color:#2563eb; flex-shrink:0; }
.fm-path-display span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* ── Editor Tabs ────────────────────────────── */
.editor-tabs { display:flex; background:#1a1a1a; border-bottom:1px solid #333; padding:0; overflow-x:auto; min-height:40px; }
.editor-tabs::-webkit-scrollbar { height:3px; }
.editor-tabs::-webkit-scrollbar-thumb { background:#444; }
.editor-tab { display:flex; align-items:center; gap:8px; padding:10px 16px; background:transparent; border:none; border-right:1px solid #333; cursor:pointer; font-size:13px; color:#9ca3af; white-space:nowrap; transition:all .15s; position:relative; }
.editor-tab:hover { background:#222; color:#e5e7eb; }
.editor-tab.active { background:#111; color:#fff; font-weight:500; }
.editor-tab.active::after { content:''; position:absolute; bottom:0; left:0; right:0; height:2px; background:#dc2626; }
.editor-tab i { font-size:13px; }
.editor-tab .close-tab { width:20px; height:20px; display:flex; align-items:center; justify-content:center; border-radius:4px; opacity:0; color:#6b7280; font-size:14px; }
.editor-tab:hover .close-tab { opacity:1; }
.editor-tab .close-tab:hover { background:#991b1b; color:#fff; }
.editor-tab .tab-modified { width:9px; height:9px; background:#dc2626; border-radius:50%; display:none; }
.editor-tab .tab-modified.show { display:block; }

/* ── Tab Content ────────────────────────────── */
.tab-content-wrapper { display:none; height:calc(100vh - 210px); min-height:500px; background:#fff; }
.tab-content-wrapper.active { display:block; }

/* ── FM Package Overrides ───────────────────── */
#fm-main-block { height:100% !important; }
#fm-main-block > div, #fm-main-block .fm { height:100% !important; background:#fff !important; }

/* Navbar */
#fm-main-block .fm-navbar, #fm-main-block .card-header { background:#fafafa !important; border-bottom:1px solid #e5e7eb !important; padding:10px 16px !important; }
#fm-main-block .fm-navbar .btn, #fm-main-block .btn-secondary, #fm-main-block .btn-light { background:#fff !important; border:1px solid #d1d5db !important; color:#374151 !important; border-radius:6px !important; font-size:13px !important; }
#fm-main-block .fm-navbar .btn:hover { background:#f3f4f6 !important; border-color:#dc2626 !important; color:#2563eb !important; }

/* Body */
#fm-main-block .fm-body, #fm-main-block .card-body { height:calc(100% - 90px) !important; background:#fff !important; padding:0 !important; }
#fm-main-block .fm-body > .row { height:100% !important; margin:0 !important; }

/* Tree Panel */
#fm-main-block .fm-tree, #fm-main-block .col-auto { background:#fafafa !important; border-right:1px solid #e5e7eb !important; overflow-y:auto !important; padding:8px 0 !important; }
#fm-main-block .fm-tree-branch, #fm-main-block .fm-tree a, #fm-main-block .fm-tree span { color:#374151 !important; font-size:13px !important; }
#fm-main-block .fm-tree-item, #fm-main-block .fm-tree li { padding:6px 14px !important; color:#374151 !important; }
#fm-main-block .fm-tree-item:hover, #fm-main-block .fm-tree li:hover { background:#f1f5f9 !important; }
#fm-main-block .fm-tree .active { background:#eff6ff !important; color:#2563eb !important; font-weight:500 !important; border-left:3px solid #2563eb !important; }

/* Content Panel */
#fm-main-block .fm-content, #fm-main-block .col { background:#fff !important; overflow-y:auto !important; }

/* Breadcrumb & Disk */
#fm-main-block .fm-disk-list, #fm-main-block .fm-breadcrumb, #fm-main-block .fm-info-block { background:#fafafa !important; border-bottom:1px solid #e5e7eb !important; padding:10px 16px !important; }
#fm-main-block .fm-disk-list .btn { background:#fff !important; border:1px solid #d1d5db !important; color:#374151 !important; font-size:13px !important; padding:6px 16px !important; border-radius:6px !important; }
#fm-main-block .fm-disk-list .btn.active, #fm-main-block .fm-disk-list .btn:hover { background:#dc2626 !important; border-color:#dc2626 !important; color:#fff !important; }
#fm-main-block .fm-path-block, #fm-main-block .fm-breadcrumb a { color:#2563eb !important; font-size:13px !important; font-weight:500 !important; }

/* Table */
#fm-main-block table { background:#fff !important; color:#374151 !important; margin:0 !important; width:100% !important; }
#fm-main-block table thead { background:#fafafa !important; }
#fm-main-block table th { background:#fafafa !important; color:#6b7280 !important; font-weight:700 !important; font-size:11px !important; text-transform:uppercase !important; letter-spacing:.4px !important; padding:12px 16px !important; border-bottom:2px solid #e5e7eb !important; }
#fm-main-block table td { padding:10px 16px !important; border-bottom:1px solid #f3f4f6 !important; font-size:14px !important; color:#374151 !important; background:#fff !important; }
#fm-main-block table tbody tr:hover td { background:#f1f5f9 !important; }
#fm-main-block table tbody tr.table-primary td, #fm-main-block .table-primary { background:#dbeafe !important; color:#111 !important; }

/* Folder icons */
#fm-main-block [class*="folder"] { color:#f59e0b !important; }

/* Footer */
#fm-main-block .fm-info-block.fm-footer, #fm-main-block .card-footer { background:#fafafa !important; color:#6b7280 !important; padding:8px 16px !important; font-size:13px !important; border-top:1px solid #e5e7eb !important; border-bottom:none !important; }

/* Context Menu */
#fm-main-block .fm-context-menu, .fm-context-menu, .dropdown-menu { background:#fff !important; border:1px solid #e5e7eb !important; border-radius:8px !important; box-shadow:0 8px 24px rgba(0,0,0,.12) !important; padding:6px !important; }
#fm-main-block .fm-context-menu .list-group-item, .dropdown-item { background:transparent !important; color:#374151 !important; padding:10px 16px !important; font-size:13px !important; border-radius:6px !important; border:none !important; }
#fm-main-block .fm-context-menu .list-group-item:hover, .dropdown-item:hover { background:#eff6ff !important; color:#2563eb !important; }

/* Modals */
.modal-content { background:#fff !important; border:1px solid #e5e7eb !important; color:#374151 !important; border-radius:12px !important; }
.modal-header { border-bottom:1px solid #e5e7eb !important; padding:16px 20px !important; }
.modal-footer { border-top:1px solid #e5e7eb !important; padding:12px 20px !important; }
.modal-title { color:#111 !important; font-weight:600 !important; font-size:16px !important; }
.modal .form-control { background:#fff !important; border:1px solid #d1d5db !important; color:#374151 !important; border-radius:6px !important; padding:10px 12px !important; font-size:14px !important; }
.modal .form-control:focus { border-color:#2563eb !important; box-shadow:0 0 0 3px rgba(37,99,235,.1) !important; }
.modal .btn-primary { background:#dc2626 !important; border:none !important; border-radius:6px !important; padding:8px 20px !important; font-weight:500 !important; }
.modal .btn-primary:hover { background:#b91c1c !important; }
.modal .btn-secondary { background:#f3f4f6 !important; border:1px solid #d1d5db !important; color:#374151 !important; border-radius:6px !important; }

/* Scrollbar */
#fm-main-block ::-webkit-scrollbar { width:8px; height:8px; }
#fm-main-block ::-webkit-scrollbar-track { background:#fafafa; }
#fm-main-block ::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:4px; }
#fm-main-block ::-webkit-scrollbar-thumb:hover { background:#9ca3af; }

/* ── Monaco Editor ──────────────────────────── */
.editor-container { height:100%; display:flex; flex-direction:column; background:#1e1e1e; }
.editor-toolbar { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; background:#111; border-bottom:1px solid #333; flex-wrap:wrap; gap:10px; }
.editor-toolbar .file-path { font-size:13px; color:#9ca3af; display:flex; align-items:center; gap:8px; }
.editor-toolbar .file-path i { color:#2563eb; }
.editor-toolbar .toolbar-actions { display:flex; gap:10px; align-items:center; }
.editor-toolbar .btn-group { display:flex; gap:2px; }
.editor-toolbar .btn { padding:7px 14px; font-size:13px; border-radius:6px; border:none; cursor:pointer; display:flex; align-items:center; gap:6px; }
.editor-toolbar .btn-save { background:#dc2626; color:#fff; }
.editor-toolbar .btn-save:hover { background:#b91c1c; }
.editor-toolbar .btn-secondary { background:#1a1a1a; color:#d1d5db; border:1px solid #333; }
.editor-toolbar .btn-secondary:hover { background:#333; color:#fff; }
.editor-toolbar select { background:#1a1a1a; color:#d1d5db; border:1px solid #333; padding:7px 12px; border-radius:6px; font-size:13px; }
.monaco-wrapper { flex:1; min-height:0; }

/* ── Custom Create Modal ────────────────────── */
.fm-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9998; display:none; justify-content:center; align-items:center; backdrop-filter:blur(3px); }
.fm-modal-overlay.show { display:flex; }
.fm-modal { background:#fff; border-radius:12px; width:100%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.fm-modal-head { padding:18px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
.fm-modal-head h3 { font-size:16px; font-weight:600; color:#111; margin:0; display:flex; align-items:center; gap:8px; }
.fm-modal-head h3 i { color:#2563eb; }
.fm-modal-close { background:none; border:none; font-size:18px; color:#9ca3af; cursor:pointer; }
.fm-modal-body { padding:24px; }
.fm-modal-body label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.fm-modal-body input { width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; outline:none; transition:all .2s; }
.fm-modal-body input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.fm-modal-body .hint { font-size:12px; color:#9ca3af; margin-top:6px; }
.fm-modal-foot { padding:14px 24px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:8px; }
.fm-modal-foot .btn-cancel { background:#f3f4f6; color:#374151; border:none; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; }
.fm-modal-foot .btn-submit { background:#dc2626; color:#fff; border:none; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; }
.fm-modal-foot .btn-submit:hover { background:#b91c1c; }

/* ── Toast ───────────────────────────────────── */
.fm-toast { position:fixed; top:20px; right:20px; padding:14px 20px; border-radius:8px; color:#fff; font-size:14px; z-index:9999; animation:slideIn .3s ease; display:flex; align-items:center; gap:10px; box-shadow:0 4px 16px rgba(0,0,0,.2); }
.fm-toast.success { background:#16a34a; }
.fm-toast.error { background:#dc2626; }
.fm-toast.info { background:#111; border:1px solid #333; }
@keyframes slideIn { from { transform:translateX(100%); opacity:0; } to { transform:translateX(0); opacity:1; } }

/* ── Status Bar ─────────────────────────────── */
.fm-statusbar { height:32px; background:#111; display:flex; align-items:center; padding:0 16px; gap:16px; font-size:12px; color:#6b7280; border-top:1px solid #222; }
.fm-statusbar .status-dot { width:6px; height:6px; border-radius:50%; background:#22c55e; display:inline-block; margin-right:4px; }

/* ── Keyboard Hints ─────────────────────────── */
.shortcut-hint { position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,.9); color:#fff; padding:12px 24px; border-radius:8px; font-size:14px; z-index:9999; display:none; }
.shortcut-hint.show { display:block; }
.shortcuts-help { position:fixed; bottom:10px; right:10px; background:#111; border:1px solid #333; border-radius:8px; padding:8px 14px; font-size:12px; color:#6b7280; z-index:50; }
.shortcuts-help kbd { background:#1a1a1a; border:1px solid #333; border-radius:3px; padding:2px 6px; font-size:11px; color:#d1d5db; }
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
