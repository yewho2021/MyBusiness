@extends('admin.layouts.app')

@section('title', 'File Manager')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ url('vendor-asset/file-manager/css/file-manager.css') }}">
<style>
    .fm-container {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
    }
    
    .editor-tabs {
        display: flex;
        background: #f3f3f3;
        border-bottom: 1px solid #e0e0e0;
        padding: 0;
        overflow-x: auto;
        min-height: 42px;
    }
    .editor-tabs::-webkit-scrollbar { height: 3px; }
    .editor-tabs::-webkit-scrollbar-thumb { background: #c0c0c0; }
    
    .editor-tab {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: transparent;
        border: none;
        border-right: 1px solid #e0e0e0;
        cursor: pointer;
        font-size: 14px;
        color: #444;
        white-space: nowrap;
        transition: all 0.15s;
        position: relative;
    }
    .editor-tab:hover { background: #e8e8e8; color: #000; }
    .editor-tab.active { background: #fff; color: #000; font-weight: 500; }
    .editor-tab.active::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 2px;
        background: #0078d4;
    }
    .editor-tab .close-tab {
        width: 20px; height: 20px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 4px;
        opacity: 0;
        color: #666;
        font-size: 16px;
    }
    .editor-tab:hover .close-tab { opacity: 1; }
    .editor-tab .close-tab:hover { background: #d0d0d0; color: #c42b1c; }
    .editor-tab .tab-modified {
        width: 9px; height: 9px;
        background: #0078d4;
        border-radius: 50%;
        display: none;
    }
    .editor-tab .tab-modified.show { display: block; }
    
    .tab-content-wrapper {
        display: none;
        height: calc(100vh - 160px);
        min-height: 600px;
        background: #fff;
    }
    .tab-content-wrapper.active { display: block; }
    
    #fm-main-block { height: 100% !important; }
    #fm-main-block > div, #fm-main-block .fm { height: 100% !important; background: #fff !important; }
    
    #fm-main-block .fm-navbar, #fm-main-block .card-header {
        background: #f8f8f8 !important;
        border-bottom: 1px solid #e0e0e0 !important;
        padding: 10px 14px !important;
    }
    #fm-main-block .fm-navbar .btn, #fm-main-block .btn-secondary, #fm-main-block .btn-light {
        background: #fff !important;
        border: 1px solid #d0d0d0 !important;
        color: #333 !important;
        border-radius: 4px !important;
        font-size: 14px !important;
    }
    #fm-main-block .fm-navbar .btn:hover { background: #e8e8e8 !important; }
    
    #fm-main-block .fm-body, #fm-main-block .card-body {
        height: calc(100% - 90px) !important;
        background: #fff !important;
        padding: 0 !important;
    }
    #fm-main-block .fm-body > .row { height: 100% !important; margin: 0 !important; }
    
    #fm-main-block .fm-tree, #fm-main-block .col-auto {
        background: #f8f8f8 !important;
        border-right: 1px solid #e0e0e0 !important;
        overflow-y: auto !important;
        padding: 8px 0 !important;
    }
    #fm-main-block .fm-tree-branch, #fm-main-block .fm-tree a, #fm-main-block .fm-tree span {
        color: #333 !important;
        font-size: 14px !important;
    }
    #fm-main-block .fm-tree-item, #fm-main-block .fm-tree li {
        padding: 6px 14px !important;
        color: #333 !important;
    }
    #fm-main-block .fm-tree-item:hover, #fm-main-block .fm-tree li:hover { background: #e8e8e8 !important; }
    #fm-main-block .fm-tree .active { background: #cce8ff !important; }
    
    #fm-main-block .fm-content, #fm-main-block .col { background: #fff !important; overflow-y: auto !important; }
    
    #fm-main-block .fm-disk-list, #fm-main-block .fm-breadcrumb, #fm-main-block .fm-info-block {
        background: #f8f8f8 !important;
        border-bottom: 1px solid #e0e0e0 !important;
        padding: 10px 14px !important;
    }
    #fm-main-block .fm-disk-list .btn {
        background: #fff !important;
        border: 1px solid #d0d0d0 !important;
        color: #333 !important;
        font-size: 13px !important;
        padding: 5px 14px !important;
    }
    #fm-main-block .fm-disk-list .btn.active, #fm-main-block .fm-disk-list .btn:hover {
        background: #0078d4 !important;
        border-color: #0078d4 !important;
        color: #fff !important;
    }
    #fm-main-block .fm-path-block, #fm-main-block .fm-breadcrumb a { color: #0078d4 !important; font-size: 14px !important; }
    
    #fm-main-block table { background: #fff !important; color: #333 !important; margin: 0 !important; width: 100% !important; }
    #fm-main-block table thead { background: #f8f8f8 !important; }
    #fm-main-block table th {
        background: #f8f8f8 !important;
        color: #555 !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        padding: 12px 16px !important;
        border-bottom: 1px solid #e0e0e0 !important;
    }
    #fm-main-block table td {
        padding: 11px 16px !important;
        border-bottom: 1px solid #f0f0f0 !important;
        font-size: 14px !important;
        color: #333 !important;
        background: #fff !important;
    }
    #fm-main-block table tbody tr:hover td { background: #f5f5f5 !important; }
    #fm-main-block table tbody tr.table-primary td, #fm-main-block .table-primary { background: #cce8ff !important; color: #000 !important; }
    
    #fm-main-block [class*="folder"] { color: #dcb67a !important; }
    
    #fm-main-block .fm-info-block.fm-footer, #fm-main-block .card-footer {
        background: #f0f0f0 !important;
        color: #333 !important;
        padding: 8px 16px !important;
        font-size: 13px !important;
        border-top: 1px solid #e0e0e0 !important;
        border-bottom: none !important;
    }
    
    #fm-main-block .fm-context-menu, .fm-context-menu, .dropdown-menu {
        background: #fff !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.14) !important;
        padding: 4px !important;
    }
    #fm-main-block .fm-context-menu .list-group-item, .dropdown-item {
        background: transparent !important;
        color: #333 !important;
        padding: 10px 16px !important;
        font-size: 14px !important;
        border-radius: 4px !important;
    }
    #fm-main-block .fm-context-menu .list-group-item:hover, .dropdown-item:hover {
        background: #f0f0f0 !important;
    }
    
    .modal-content { background: #fff !important; border: 1px solid #e0e0e0 !important; color: #333 !important; }
    .modal-header { border-bottom: 1px solid #e8e8e8 !important; }
    .modal-footer { border-top: 1px solid #e8e8e8 !important; }
    .modal-title { color: #000 !important; font-weight: 600 !important; }
    .modal .form-control { background: #fff !important; border: 1px solid #d0d0d0 !important; color: #333 !important; }
    .modal .btn-primary { background: #0078d4 !important; border: none !important; }
    .modal .btn-secondary { background: #f0f0f0 !important; border: 1px solid #d0d0d0 !important; color: #333 !important; }
    
    #fm-main-block ::-webkit-scrollbar { width: 12px; height: 12px; }
    #fm-main-block ::-webkit-scrollbar-track { background: #f0f0f0; }
    #fm-main-block ::-webkit-scrollbar-thumb { background: #c0c0c0; border-radius: 6px; border: 3px solid #f0f0f0; }
    
    .editor-container { height: 100%; display: flex; flex-direction: column; background: #1e1e1e; }
    .editor-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        background: #f3f3f3;
        border-bottom: 1px solid #e0e0e0;
        flex-wrap: wrap;
        gap: 10px;
    }
    .editor-toolbar .file-path { font-size: 14px; color: #444; display: flex; align-items: center; gap: 8px; }
    .editor-toolbar .file-path i { color: #0078d4; }
    .editor-toolbar .toolbar-actions { display: flex; gap: 12px; align-items: center; }
    .editor-toolbar .btn-group { display: flex; gap: 2px; }
    .editor-toolbar .btn {
        padding: 7px 14px;
        font-size: 13px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .editor-toolbar .btn-save { background: #0078d4; color: #fff; }
    .editor-toolbar .btn-save:hover { background: #106ebe; }
    .editor-toolbar .btn-secondary { background: #fff; color: #333; border: 1px solid #d0d0d0; }
    .editor-toolbar .btn-secondary:hover { background: #e8e8e8; }
    .editor-toolbar select { background: #fff; color: #333; border: 1px solid #d0d0d0; padding: 7px 12px; border-radius: 4px; font-size: 13px; }
    .monaco-wrapper { flex: 1; min-height: 0; }
    
    .fm-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 14px 20px;
        border-radius: 8px;
        color: #fff;
        font-size: 14px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    }
    .fm-toast.success { background: #107c10; }
    .fm-toast.error { background: #c42b1c; }
    .fm-toast.info { background: #0078d4; }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    
    .page-header h1 { font-size: 24px; font-weight: 600; color: #000; margin: 0; display: flex; align-items: center; gap: 10px; }
    .page-header p { color: #666; margin: 4px 0 0; font-size: 14px; }
    
    .shortcut-hint {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.85);
        color: #fff;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        z-index: 9999;
        display: none;
    }
    .shortcut-hint.show { display: block; }
    
    .shortcuts-help {
        position: fixed;
        bottom: 10px;
        right: 10px;
        background: #f8f8f8;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 12px;
        color: #666;
    }
    .shortcuts-help kbd {
        background: #fff;
        border: 1px solid #d0d0d0;
        border-radius: 3px;
        padding: 2px 6px;
        font-size: 11px;
        color: #333;
    }
</style>
@endpush

@section('content')
<div class="p-4">
    <div class="page-header mb-3">
        <h1><i class="bi bi-folder2-open text-primary"></i> File Manager</h1>
        <p>Browse, edit and manage your files with Monaco Editor</p>
    </div>
    
    <div class="fm-container">
        <div class="editor-tabs" id="editorTabs">
            <div class="editor-tab active" data-tab="filemanager" onclick="switchTab('filemanager')">
                <i class="bi bi-folder2"></i>
                <span>Explorer</span>
            </div>
        </div>
        
        <div id="tabContents">
            <div class="tab-content-wrapper active" data-content="filemanager">
                <div id="fm-main-block">
                    <div id="fm"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="shortcut-hint" id="shortcutHint"></div>
<div class="shortcuts-help">
    <kbd>Ctrl</kbd>+<kbd>`</kbd> Next Tab |
    <kbd>Ctrl</kbd>+<kbd>S</kbd> Save |
    <kbd>Ctrl</kbd>+<kbd>W</kbd> Close
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js"></script>
<script src="{{ url('vendor-asset/file-manager/js/file-manager.js') }}"></script>
<script>
let openTabs = { filemanager: { type: 'filemanager' } };
let activeTab = 'filemanager';
let editors = {};
let monacoReady = false;

require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs' } });
require(['vs/editor/editor.main'], () => { monacoReady = true; });

function switchTab(tabId) {
    activeTab = tabId;
    document.querySelectorAll('.editor-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tabId));
    document.querySelectorAll('.tab-content-wrapper').forEach(c => c.classList.toggle('active', c.dataset.content === tabId));
    if (editors[tabId]) setTimeout(() => editors[tabId].layout(), 50);
}

function nextTab() {
    const tabs = Object.keys(openTabs);
    const idx = tabs.indexOf(activeTab);
    const next = tabs[(idx + 1) % tabs.length];
    switchTab(next);
    showShortcutHint('Tab: ' + (openTabs[next].title || 'Explorer'));
}

function prevTab() {
    const tabs = Object.keys(openTabs);
    const idx = tabs.indexOf(activeTab);
    const prev = tabs[(idx - 1 + tabs.length) % tabs.length];
    switchTab(prev);
    showShortcutHint('Tab: ' + (openTabs[prev].title || 'Explorer'));
}

function showShortcutHint(msg) {
    const h = document.getElementById('shortcutHint');
    h.textContent = msg;
    h.classList.add('show');
    setTimeout(() => h.classList.remove('show'), 1200);
}

function getLanguageFromPath(p) {
    const ext = p.split('.').pop().toLowerCase();
    const m = {js:'javascript',ts:'typescript',php:'php',html:'html',css:'css',json:'json',xml:'xml',md:'markdown',sql:'sql',py:'python',yaml:'yaml',yml:'yaml',sh:'shell',env:'ini',ini:'ini',txt:'plaintext'};
    if (p.includes('.blade.php')) return 'php';
    if (p.includes('.env')) return 'ini';
    return m[ext] || 'plaintext';
}

function generateTabId(d, p) {
    return 'ed-' + Math.abs((d+':'+p).split('').reduce((a,b)=>(((a<<5)-a)+b.charCodeAt(0))|0,0)).toString(36);
}

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
                            <option value="ini" ${lang==='ini'?'selected':''}>INI/Env</option>
                            <option value="plaintext" ${lang==='plaintext'?'selected':''}>Plain Text</option>
                        </select>
                        <div class="btn-group">
                            <button class="btn btn-secondary" onclick="foldAll('${tabId}')" title="Collapse"><i class="bi bi-arrows-collapse"></i></button>
                            <button class="btn btn-secondary" onclick="unfoldAll('${tabId}')" title="Expand"><i class="bi bi-arrows-expand"></i></button>
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

function saveFile(tabId) {
    const tab = openTabs[tabId];
    if (!tab || tab.type !== 'editor') return;
    showShortcutHint('Saving...');
    fetch('/file-manager/update-file', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'X-Requested-With':'XMLHttpRequest' },
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

function toast(msg, type = 'info') {
    const t = document.createElement('div');
    t.className = `fm-toast ${type}`;
    t.innerHTML = `<i class="bi bi-${type==='success'?'check-circle':type==='error'?'x-circle':'info-circle'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
}

// Keyboard shortcuts - Ctrl+` for tab switch (Ctrl+Tab is browser reserved)
document.addEventListener('keydown', function(e) {
    // Ctrl + ` (backtick) = next tab
    if (e.ctrlKey && (e.key === '`' || e.key === '~' || e.code === 'Backquote')) {
        e.preventDefault();
        e.shiftKey ? prevTab() : nextTab();
        return;
    }
    
    // Ctrl + S = save
    if (e.ctrlKey && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 's') {
        e.preventDefault();
        if (activeTab !== 'filemanager' && editors[activeTab]) saveFile(activeTab);
        return;
    }
    
    // Ctrl + W = close tab
    if (e.ctrlKey && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 'w') {
        e.preventDefault();
        if (activeTab !== 'filemanager') closeTab(activeTab);
        return;
    }
    
    // Ctrl + 1-9 = switch to tab
    if (e.ctrlKey && !e.shiftKey && !e.altKey && e.key >= '1' && e.key <= '9') {
        e.preventDefault();
        const tabs = Object.keys(openTabs);
        const idx = parseInt(e.key) - 1;
        if (tabs[idx]) {
            switchTab(tabs[idx]);
            showShortcutHint('Tab ' + e.key + ': ' + (openTabs[tabs[idx]].title || 'Explorer'));
        }
        return;
    }
}, true); // Use capture phase

// File manager intercept
document.addEventListener('DOMContentLoaded', function() {
    let ready = setInterval(function() {
        if (window.fm?.$store) {
            clearInterval(ready);
            const store = window.fm.$store;
            const origCommit = store.commit.bind(store);
            
            // Helper: resolve selected file path from store
            function getSelectedFilePath(mgr) {
                const sel = mgr?.selected;
                if (!sel?.files?.length) return null;
                
                const idx = sel.files[0];
                const dir = mgr.selectedDirectory || '';
                
                // sel.files[0] is an index into mgr.files[]
                if (typeof idx === 'number' && mgr.files?.[idx]) {
                    const fileObj = mgr.files[idx];
                    // file object has basename and path
                    const name = fileObj.basename || fileObj.filename || fileObj.name || '';
                    return { path: fileObj.path || (dir ? dir + '/' + name : name), name: name };
                }
                
                // fallback: treat as path string
                const pathStr = String(idx);
                return { path: pathStr, name: pathStr.split('/').pop() };
            }
            
            // Helper: extract filename from a table row DOM element
            function getFileNameFromRow(row) {
                if (!row) return null;
                // The first <td> contains the filename text
                const cells = row.querySelectorAll('td');
                for (const cell of cells) {
                    const text = cell.textContent.trim();
                    // Skip empty cells, size cells (contain KB/MB/bytes), date cells
                    if (text && !text.match(/^\d+(\.\d+)?\s*(B|KB|MB|GB|bytes?)/i) && !text.match(/^\d{4}-\d{2}-\d{2}/)) {
                        return text;
                    }
                }
                return null;
            }
            
            // Guard to prevent double-open from interceptor + dblclick both firing
            let lastOpenedAt = 0;
            
            function openFileInEditor(disk, filePath, fileName) {
                const now = Date.now();
                if (now - lastOpenedAt < 500) return; // debounce 500ms
                lastOpenedAt = now;
                loadFile(disk, filePath, fileName);
            }
            
            // Intercept TextEditModal so our Monaco editor opens instead
            store.commit = function(type, payload, options) {
                if (type === 'fm/modal/setModalState' && payload?.modalName === 'TextEditModal') {
                    const state = store.state.fm;
                    const mgr = state[state.activeManager || 'left'];
                    const disk = mgr?.selectedDisk || 'home';
                    const file = getSelectedFilePath(mgr);
                    if (file) {
                        openFileInEditor(disk, file.path, file.name);
                    }
                    return; // block the default TextEditModal
                }
                return origCommit(type, payload, options);
            };
            
            // File manager ready - no forced directory, uses config leftPath
            
            // Double-click handler for files the FM doesn't natively edit
            // Uses DOM to get the filename (avoids store index mismatch)
            document.addEventListener('dblclick', function(e) {
                const row = e.target.closest('tr');
                if (!row || row.querySelector('[class*="folder"]')) return;
                
                // Get filename directly from the clicked row
                const fileName = getFileNameFromRow(row);
                if (!fileName) return;
                
                const textExts = ['php','js','ts','html','htm','css','scss','json','xml','md','txt','sql','py','yaml','yml','sh','env','ini','conf','htaccess','gitignore','vue','jsx','tsx'];
                if (!textExts.some(ext => fileName.toLowerCase().endsWith('.'+ext) || fileName.toLowerCase().includes('.'+ext+'.'))) return;
                
                const state = store.state.fm;
                const mgr = state[state.activeManager || 'left'];
                const disk = mgr?.selectedDisk || 'home';
                const dir = mgr?.selectedDirectory || '';
                const fullPath = dir ? dir + '/' + fileName : fileName;
                
                openFileInEditor(disk, fullPath, fileName);
            });
        }
    }, 100);
    setTimeout(() => clearInterval(ready), 10000);
});
</script>
@endpush
