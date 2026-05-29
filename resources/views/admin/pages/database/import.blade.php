@extends('admin.layouts.app')
@section('title', 'Import Database')
@push('styles')
<style>
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.page-title { font-size:22px; font-weight:700; color:var(--header-text,var(--text-heading)); }
.nav-pills { display:flex; gap:6px; flex-wrap:wrap; }
.nav-pill { padding:6px 14px; border-radius:6px; font-size:13px; text-decoration:none; color:var(--text-muted); border:1px solid var(--border-color,var(--border-color)); display:inline-flex; align-items:center; gap:6px; }
.nav-pill:hover { background:var(--border-light,var(--border-light)); color:var(--text-body); }
.nav-pill.active { background:var(--c-secondary,var(--c-secondary)); color:#fff; border-color:var(--c-secondary,var(--c-secondary)); }

.imp-warn { display:flex; align-items:flex-start; gap:10px; background:var(--c-warning-light); border:1px solid var(--c-warning-border); border-radius:var(--card-radius,10px); padding:14px 16px; font-size:13px; color:var(--c-warning); margin-bottom:20px; line-height:1.5; }
.imp-warn i { color:var(--c-warning,var(--c-warning)); margin-top:2px; flex-shrink:0; }

.imp-drop-zone { border:2px dashed var(--input-border); border-radius:var(--card-radius,12px); padding:48px 24px; text-align:center; cursor:pointer; transition:all .2s; }
.imp-drop-zone:hover { border-color:var(--c-secondary,var(--c-secondary)); background:var(--c-secondary-light); }
.imp-drop-zone.dragover { border-color:var(--c-success,var(--c-success)); background:var(--c-success-light); border-style:solid; }
.imp-drop-zone.has-file { border-color:var(--c-success); background:var(--c-success-light); border-style:solid; }
.imp-drop-icon { font-size:42px; color:var(--text-faint); margin-bottom:12px; }
.imp-drop-zone:hover .imp-drop-icon { color:var(--c-secondary,var(--c-secondary)); }
.imp-drop-zone.dragover .imp-drop-icon { color:var(--c-success,var(--c-success)); }
.imp-drop-text { font-size:15px; color:var(--text-secondary); font-weight:500; margin-bottom:4px; }
.imp-drop-sub { font-size:12px; color:var(--text-faint); }

.imp-file-info { margin-top:16px; }
.imp-file-card { display:flex; align-items:center; gap:14px; padding:14px 18px; background:var(--table-header-bg,var(--table-header-bg)); border:1px solid var(--border-color,var(--border-color)); border-radius:var(--card-radius,10px); }
.imp-file-icon { width:44px; height:44px; background:linear-gradient(135deg,var(--c-secondary-light),var(--c-secondary-light)); border-radius:var(--card-radius,10px); display:flex; align-items:center; justify-content:center; font-size:20px; color:var(--c-secondary,var(--c-secondary)); flex-shrink:0; }
.imp-file-details { flex:1; min-width:0; }
.imp-file-name { font-size:14px; font-weight:600; color:var(--header-text,var(--text-heading)); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.imp-file-meta { font-size:12px; color:var(--text-faint); margin-top:2px; }
.imp-file-remove { background:none; border:none; color:var(--text-faint); cursor:pointer; padding:4px 8px; border-radius:6px; font-size:14px; }
.imp-file-remove:hover { color:var(--c-primary,var(--c-danger)); background:var(--c-danger-light); }

.imp-btn-run { background:linear-gradient(135deg,var(--c-danger),var(--c-primary-hover)); color:#fff; border:none; padding:12px 28px; border-radius:var(--card-radius,10px); font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; margin-top:16px; transition:all .2s; }
.imp-btn-run:hover:not(:disabled) { box-shadow:0 4px 12px rgba(220,38,38,.3); transform:translateY(-1px); }
.imp-btn-run:disabled { opacity:.45; cursor:not-allowed; }

.imp-result-header { padding:16px 20px; border-radius:var(--card-radius,10px); margin-bottom:12px; display:flex; align-items:center; gap:12px; font-size:14px; font-weight:600; }
.imp-result-header.ok { background:var(--c-success-light); border:1px solid var(--c-success-border); color:var(--c-success); }
.imp-result-header.err { background:var(--c-danger-light); border:1px solid var(--c-danger-border); color:var(--c-primary-hover); }
.imp-result-header.running { background:var(--c-secondary-light); border:1px solid var(--c-secondary-border); color:var(--c-secondary); }

.imp-stats { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:12px; }
.imp-stat { background:var(--card-bg,#fff); border:1px solid var(--border-color,var(--border-color)); border-radius:8px; padding:10px 16px; font-size:13px; color:var(--text-muted); display:flex; align-items:center; gap:8px; }
.imp-stat strong { font-size:16px; color:var(--header-text,var(--text-heading)); }
.imp-stat.ok strong { color:var(--c-success,var(--c-success)); }
.imp-stat.err strong { color:var(--c-primary,var(--c-danger)); }

.imp-log-wrap { background:var(--card-bg,#fff); border:1px solid var(--border-color,var(--border-color)); border-radius:var(--card-radius,10px); overflow:hidden; }
.imp-log-header { padding:12px 18px; background:var(--table-header-bg,var(--table-header-bg)); border-bottom:1px solid var(--border-color,var(--border-color)); display:flex; justify-content:space-between; align-items:center; }
.imp-log-title { font-size:13px; font-weight:600; color:var(--header-text,var(--text-heading)); }
.imp-log-filter { display:flex; gap:4px; }
.imp-log-filter button { padding:4px 12px; border-radius:6px; border:1px solid var(--border-color,var(--border-color)); background:var(--card-bg,#fff); font-size:12px; color:var(--text-muted); cursor:pointer; }
.imp-log-filter button:hover { background:var(--border-light,var(--border-light)); }
.imp-log-filter button.active { background:var(--text-heading); color:#fff; border-color:var(--header-text,var(--text-heading)); }

.imp-log { max-height:500px; overflow-y:auto; }
.imp-log-row { display:flex; align-items:flex-start; gap:10px; padding:8px 18px; border-bottom:1px solid var(--table-header-bg); font-size:13px; line-height:1.5; }
.imp-log-row:last-child { border-bottom:none; }
.imp-log-row i { margin-top:3px; font-size:11px; flex-shrink:0; }
.imp-log-num { color:var(--text-faint); font-size:11px; min-width:30px; text-align:right; font-weight:500; flex-shrink:0; }
.imp-log-msg { flex:1; color:var(--text-body); word-break:break-word; }
.imp-log-err { display:block; color:var(--c-primary-hover); font-size:12px; margin-top:4px; padding:6px 10px; background:var(--c-danger-light); border-radius:6px; border:1px solid var(--c-danger-border); font-family:var(--font-mono,'JetBrains Mono'),monospace; word-break:break-all; }
.imp-log-row.type-ok i { color:var(--c-success,var(--c-success)); }
.imp-log-row.type-err i { color:var(--c-primary,var(--c-danger)); }
.imp-log-row.type-info i { color:var(--c-secondary,var(--c-secondary)); }
.imp-log-row.type-warn i { color:var(--c-warning,var(--c-warning)); }
.imp-log-row.type-err { background:var(--card-bg); }
</style>
@endpush

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="fas fa-upload" style="color:var(--c-secondary,var(--c-secondary))"></i> Import SQL</h1></div>
    <div class="nav-pills">
        <a href="{{ route('admin.database.index') }}" class="nav-pill"><i class="fas fa-table"></i> Tables</a>
        <a href="{{ route('admin.database.query') }}" class="nav-pill"><i class="fas fa-terminal"></i> SQL Query</a>
        <a href="{{ route('admin.database.export') }}" class="nav-pill"><i class="fas fa-download"></i> Export</a>
        <a href="{{ route('admin.database.import') }}" class="nav-pill active"><i class="fas fa-upload"></i> Import</a>
    </div>
</div>

<div class="imp-warn">
    <i class="fas fa-exclamation-triangle"></i>
    <div><strong>Warning:</strong> Importing SQL may overwrite existing data. Make sure you have a backup before importing.</div>
</div>

<form id="importForm" onsubmit="handleImport(event)">
    @csrf
    <div class="imp-drop-zone" id="impDropZone">
        <div class="imp-drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
        <div class="imp-drop-text">Drag & drop a .sql file here, or click to browse</div>
        <div class="imp-drop-sub">Supports .sql and .txt files · Max 50 MB</div>
        <input type="file" name="sql_file" id="sqlFileInput" accept=".sql,.txt" style="display:none">
    </div>

    <div class="imp-file-info" id="fileInfo" style="display:none">
        <div class="imp-file-card">
            <div class="imp-file-icon"><i class="fas fa-file-code"></i></div>
            <div class="imp-file-details">
                <div class="imp-file-name" id="fileName"></div>
                <div class="imp-file-meta" id="fileMeta"></div>
            </div>
            <button type="button" class="imp-file-remove" onclick="clearFile()"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <button type="submit" class="imp-btn-run" id="importBtn" disabled>
        <i class="fas fa-upload"></i> Import Now
    </button>
</form>

<div id="importResults" style="margin-top:20px;"></div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const zone = document.getElementById('impDropZone');
const fileInput = document.getElementById('sqlFileInput');
const fileInfo = document.getElementById('fileInfo');
const fileNameEl = document.getElementById('fileName');
const fileMetaEl = document.getElementById('fileMeta');
const importBtn = document.getElementById('importBtn');
const results = document.getElementById('importResults');

function formatSize(b) { return b >= 1048576 ? (b/1048576).toFixed(2)+' MB' : b >= 1024 ? (b/1024).toFixed(1)+' KB' : b+' B'; }
function escHtml(s) { return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }

function selectFile(file) {
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['sql','txt'].includes(ext)) { alert('Only .sql and .txt files are supported.'); return; }
    const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
    fileNameEl.textContent = file.name;
    fileMetaEl.textContent = formatSize(file.size) + ' · ' + ext.toUpperCase() + ' file';
    fileInfo.style.display = 'block';
    zone.classList.add('has-file');
    importBtn.disabled = false;
}

function clearFile() {
    fileInput.value = '';
    fileInfo.style.display = 'none';
    zone.classList.remove('has-file');
    importBtn.disabled = true;
}

// Click to browse
zone.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', () => { if (fileInput.files[0]) selectFile(fileInput.files[0]); });

// Drag & drop
zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', (e) => { e.preventDefault(); zone.classList.remove('dragover'); if (e.dataTransfer.files[0]) selectFile(e.dataTransfer.files[0]); });

async function handleImport(e) {
    e.preventDefault();
    const form = document.getElementById('importForm');
    const formData = new FormData(form);
    const file = formData.get('sql_file');
    if (!file || !file.name || file.size === 0) {
        results.innerHTML = '<div class="imp-result-header err"><i class="fas fa-exclamation-circle"></i> No file selected.</div>';
        return;
    }

    importBtn.disabled = true; importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
    results.innerHTML = '<div class="imp-result-header running"><i class="fas fa-circle-notch fa-spin"></i> Importing ' + escHtml(file.name) + '...</div>';

    try {
        const res = await fetch("{{ route('admin.database.import') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData
        });

        let data;
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
            data = await res.json();
        } else {
            const text = await res.text();
            let msg = 'Import failed (HTTP ' + res.status + ')';
            try { const j = JSON.parse(text); msg = j.message || Object.values(j.errors||{}).flat().join(', ') || msg; } catch(pe) { msg = text.substring(0,300) || msg; }
            data = { success: false, summary: msg, log: [], ok: 0, errors: 1 };
        }

        results.innerHTML = renderResults(data);

    } catch(err) {
        results.innerHTML = '<div class="imp-result-header err"><i class="fas fa-times-circle"></i> Network error: ' + escHtml(err.message) + '</div>';
    } finally {
        importBtn.disabled = false; importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Now';
    }
}

function renderResults(data) {
    const icons = { ok:'check-circle', err:'times-circle', info:'info-circle', warn:'exclamation-triangle' };
    let html = '';

    const hdrClass = data.success ? 'ok' : 'err';
    const hdrIcon = data.success ? 'check-circle' : 'times-circle';
    html += `<div class="imp-result-header ${hdrClass}"><i class="fas fa-${hdrIcon}"></i> ${escHtml(data.summary || 'Import completed')}</div>`;

    html += '<div class="imp-stats">';
    html += `<div class="imp-stat ok"><i class="fas fa-check"></i> Succeeded: <strong>${data.ok||0}</strong></div>`;
    if (data.errors > 0) html += `<div class="imp-stat err"><i class="fas fa-times"></i> Failed: <strong>${data.errors}</strong></div>`;
    if (data.skipped > 0) html += `<div class="imp-stat"><i class="fas fa-forward"></i> Skipped: <strong>${data.skipped}</strong></div>`;
    html += `<div class="imp-stat"><i class="fas fa-clock"></i> Time: <strong>${data.elapsed_ms||0}ms</strong></div>`;
    if (data.file_name) html += `<div class="imp-stat"><i class="fas fa-file"></i> ${escHtml(data.file_name)} (${data.file_size||''})</div>`;
    html += '</div>';

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
        html += '</div><div class="imp-log">';
        data.log.forEach(entry => {
            const icon = icons[entry.type] || 'chevron-right';
            const numHtml = entry.num ? `<span class="imp-log-num">#${entry.num}</span>` : '<span class="imp-log-num"></span>';
            let msgHtml = `<span class="imp-log-msg">${escHtml(entry.msg)}`;
            if (entry.error) msgHtml += `<span class="imp-log-err">${escHtml(entry.error)}</span>`;
            msgHtml += '</span>';
            html += `<div class="imp-log-row type-${entry.type}" data-type="${entry.type}"><i class="fas fa-${icon}"></i>${numHtml}${msgHtml}</div>`;
        });
        html += '</div></div>';
    }
    return html;
}

function impFilterLog(btn, type) {
    const wrap = btn.closest('.imp-log-wrap');
    if (!wrap) return;
    wrap.querySelectorAll('.imp-log-filter button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    wrap.querySelectorAll('.imp-log-row').forEach(row => {
        row.style.display = (type === 'all' || row.dataset.type === type) ? '' : 'none';
    });
}
</script>
@endpush
