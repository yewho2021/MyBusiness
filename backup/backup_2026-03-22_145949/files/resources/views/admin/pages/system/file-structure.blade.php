@extends('admin.layouts.app')
@section('title', 'File Structure')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
.page-header h2 { font-size:22px; font-weight:700; color:#1e293b; margin:0 0 4px; }
.page-desc { font-size:13px; color:#64748b; margin:0; }
.header-actions { display:flex; gap:8px; }

.btn { padding:8px 14px; border-radius:7px; font-size:12px; font-weight:500; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:6px; transition:all .15s; }
.btn-primary { background:#dc2626; color:#fff; }
.btn-primary:hover { background:#b91c1c; }
.btn-outline { background:#fff; color:#374151; border:1px solid #d1d5db; }
.btn-outline:hover { background:#f3f4f6; }
.btn-sm { padding:6px 10px; font-size:11px; }

.panels { display:grid; grid-template-columns:1fr 1fr; gap:16px; height:calc(100vh - 190px); }
@media(max-width:1100px) { .panels { grid-template-columns:1fr; height:auto; } }

.panel { background:#fff; border-radius:10px; border:1px solid #e2e8f0; display:flex; flex-direction:column; overflow:hidden; min-height:400px; }
.panel-header { padding:12px 16px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-shrink:0; gap:8px; }
.panel-title { font-size:13px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px; }
.panel-title i { color:#2563eb; font-size:14px; }
.panel-actions { display:flex; gap:5px; flex-shrink:0; }
.panel-meta { font-size:11px; color:#94a3b8; display:flex; align-items:center; gap:5px; }
.panel-wrap { flex:1; position:relative; overflow:hidden; }

.panel-output {
    width:100%; height:100%;
    padding:16px;
    border:none; resize:none;
    font-family:'JetBrains Mono','Fira Code','Courier New',monospace;
    font-size:11px; line-height:1.7;
    color:#e2e8f0; background:#0f172a;
    outline:none; white-space:pre; overflow:auto;
}
.panel-output::selection { background:#334155; }

.panel-footer { padding:8px 16px; background:#1e293b; border-top:1px solid #334155; display:flex; justify-content:space-between; align-items:center; font-size:11px; color:#64748b; flex-shrink:0; }

.loading-mask { position:absolute; inset:0; background:rgba(15,23,42,.9); display:none; flex-direction:column; align-items:center; justify-content:center; gap:10px; z-index:10; color:#e2e8f0; font-size:12px; }
.loading-mask.show { display:flex; }
.loading-mask i { font-size:24px; color:#f87171; }

.toast { position:fixed; bottom:24px; right:24px; padding:10px 16px; border-radius:8px; color:#fff; font-size:12px; font-weight:500; display:flex; align-items:center; gap:7px; z-index:9999; box-shadow:0 4px 12px rgba(0,0,0,.15); animation:toastIn .3s ease; }
.toast-success { background:#16a34a; }
@keyframes toastIn { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-sitemap" style="color:#2563eb;margin-right:6px"></i> File Structure</h2>
        <p class="page-desc">Project files & database schema — copy or download for documentation</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-outline" onclick="refreshAll()" id="btnRefresh"><i class="fas fa-sync-alt"></i> Refresh All</button>
        <button class="btn btn-primary" onclick="downloadAll()"><i class="fas fa-download"></i> Download Both</button>
    </div>
</div>

<div class="panels">
    {{-- Panel 1: File Structure --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-folder-tree"></i> File Structure</div>
            <div class="panel-actions">
                <button class="btn btn-outline btn-sm" onclick="refreshPanel('files')" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                <button class="btn btn-outline btn-sm" onclick="copyPanel('files')" title="Copy"><i class="fas fa-copy"></i></button>
                <button class="btn btn-outline btn-sm" onclick="downloadPanel('files','file_structure')" title="Download"><i class="fas fa-download"></i></button>
            </div>
        </div>
        <div class="panel-wrap">
            <div class="loading-mask" id="loading-files"><i class="fas fa-circle-notch fa-spin"></i><span>Scanning files...</span></div>
            <textarea class="panel-output" id="output-files" readonly spellcheck="false">{{ $fileOutput }}</textarea>
        </div>
        <div class="panel-footer">
            <span id="meta-files-lines"><i class="fas fa-align-left"></i> {{ substr_count($fileOutput, "\n") + 1 }} lines</span>
            <span><i class="fas fa-folder"></i> {{ base_path() }}</span>
        </div>
    </div>

    {{-- Panel 2: Database Schema --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-database"></i> Database Schema</div>
            <div class="panel-actions">
                <button class="btn btn-outline btn-sm" onclick="refreshPanel('db')" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                <button class="btn btn-outline btn-sm" onclick="copyPanel('db')" title="Copy"><i class="fas fa-copy"></i></button>
                <button class="btn btn-outline btn-sm" onclick="downloadPanel('db','database_schema')" title="Download"><i class="fas fa-download"></i></button>
            </div>
        </div>
        <div class="panel-wrap">
            <div class="loading-mask" id="loading-db"><i class="fas fa-circle-notch fa-spin"></i><span>Reading database...</span></div>
            <textarea class="panel-output" id="output-db" readonly spellcheck="false">{{ $dbOutput }}</textarea>
        </div>
        <div class="panel-footer">
            <span id="meta-db-lines"><i class="fas fa-align-left"></i> {{ substr_count($dbOutput, "\n") + 1 }} lines</span>
            <span><i class="fas fa-database"></i> {{ config('database.connections.mysql.database') }}</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const GENERATE_URL = '{{ route("admin.file-structure.generate") }}';

function updateLineCount(panel) {
    const area = document.getElementById('output-' + panel);
    const count = area.value.split('\n').length;
    document.getElementById('meta-' + panel + '-lines').innerHTML = '<i class="fas fa-align-left"></i> ' + count.toLocaleString() + ' lines';
}

function copyPanel(panel) {
    const area = document.getElementById('output-' + panel);
    navigator.clipboard.writeText(area.value).then(() => showToast('Copied!')).catch(() => {
        area.select(); document.execCommand('copy'); showToast('Copied!');
    });
}

function downloadPanel(panel, filename) {
    const area = document.getElementById('output-' + panel);
    const blob = new Blob([area.value], { type:'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename + '_' + new Date().toISOString().slice(0,10) + '.txt';
    a.click();
    URL.revokeObjectURL(a.href);
    showToast('Downloaded!');
}

function downloadAll() {
    downloadPanel('files', 'file_structure');
    setTimeout(() => downloadPanel('db', 'database_schema'), 300);
}

function refreshPanel(type) {
    const mask = document.getElementById('loading-' + type);
    mask.classList.add('show');

    fetch(GENERATE_URL + '?type=' + type, {
        headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.fileOutput) {
            document.getElementById('output-files').value = data.fileOutput;
            updateLineCount('files');
        }
        if (data.dbOutput) {
            document.getElementById('output-db').value = data.dbOutput;
            updateLineCount('db');
        }
        showToast('Refreshed!');
    })
    .catch(err => showToast('Error: ' + err.message))
    .finally(() => mask.classList.remove('show'));
}

function refreshAll() {
    const btn = document.getElementById('btnRefresh');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';

    document.getElementById('loading-files').classList.add('show');
    document.getElementById('loading-db').classList.add('show');

    fetch(GENERATE_URL + '?type=both', {
        headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.fileOutput) {
            document.getElementById('output-files').value = data.fileOutput;
            updateLineCount('files');
        }
        if (data.dbOutput) {
            document.getElementById('output-db').value = data.dbOutput;
            updateLineCount('db');
        }
        showToast('Both panels refreshed!');
    })
    .catch(err => showToast('Error: ' + err.message))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh All';
        document.getElementById('loading-files').classList.remove('show');
        document.getElementById('loading-db').classList.remove('show');
    });
}

function showToast(msg) {
    document.querySelectorAll('.toast').forEach(t => t.remove());
    const t = document.createElement('div');
    t.className = 'toast toast-success';
    t.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(() => t.remove(), 300); }, 2500);
}
</script>
@endpush
