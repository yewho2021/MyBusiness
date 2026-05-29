@extends('admin.layouts.app')
@section('title', 'PDF Tools')

@push('styles')
<style>
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.page-header h1 { font-size: 24px; font-weight: 700; color: var(--code-bg); margin-bottom: 5px; }
.page-header p { font-size: 14px; color: var(--text-muted); }

.tabs { display: flex; gap: 4px; background: var(--border-light); border-radius: 12px; padding: 4px; margin-bottom: 22px; flex-wrap: wrap; }
.tab-btn { padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; background: transparent; color: var(--text-muted); transition: all .2s; display: inline-flex; align-items: center; gap: 7px; }
.tab-btn:hover { color: var(--text-heading); background: rgba(255,255,255,.5); }
.tab-btn.active { background: #fff; color: var(--c-danger); box-shadow: 0 1px 3px rgba(0,0,0,.1); }

.card { background: #fff; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-body { padding: 22px; }
.tab-content { display: none; }
.tab-content.active { display: block; }

.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-body); margin-bottom: 5px; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 14px; color: var(--text-heading); background: #fff; box-sizing: border-box; }
.form-control:focus { outline: none; border-color: var(--c-secondary); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media(max-width:640px) { .form-row { grid-template-columns: 1fr; } }
.form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
@media(max-width:800px) { .form-row-3 { grid-template-columns: 1fr; } }

.btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; transition: all .2s; }
.btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.btn-primary { background: linear-gradient(135deg, var(--c-danger) 0%, var(--c-primary-hover) 100%); color: #fff; }
.btn-outline { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--input-border); }
.btn-outline:hover { background: var(--table-header-bg); border-color: var(--text-faint); transform: none; }
.btn-sm { padding: 8px 14px; font-size: 13px; }
.btn-danger { color: var(--c-danger); border: 1.5px solid var(--c-danger-border); background: #fff; }
.btn-danger:hover { background: var(--c-danger-light); }

/* ── Code Editor ── */
.code-editor { width: 100%; min-height: 300px; padding: 16px; border: 1.5px solid var(--border-color); border-radius: 8px; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 13px; line-height: 1.6; color: var(--text-heading); background: var(--table-header-bg); resize: vertical; tab-size: 4; }
.code-editor:focus { outline: none; border-color: var(--c-secondary); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

/* ── Report Cards ── */
.report-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
@media(max-width:1000px) { .report-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width:640px) { .report-grid { grid-template-columns: 1fr; } }
.report-card { background: #fff; border: 1.5px solid var(--border-color); border-radius: 12px; padding: 22px; transition: all .2s; cursor: pointer; }
.report-card:hover { border-color: var(--c-danger); box-shadow: 0 4px 16px rgba(220,38,38,.08); transform: translateY(-2px); }
.report-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 14px; }
.report-icon.red { background: linear-gradient(135deg, var(--c-danger-light), var(--c-danger-light)); color: var(--c-danger); }
.report-icon.blue { background: linear-gradient(135deg, var(--c-secondary-light), var(--c-secondary-light)); color: var(--c-secondary); }
.report-icon.green { background: linear-gradient(135deg, var(--c-success-light), var(--c-success-light)); color: var(--c-success); }
.report-icon.amber { background: linear-gradient(135deg, var(--c-warning-light), var(--c-warning-light)); color: var(--c-warning); }
.report-icon.purple { background: linear-gradient(135deg, var(--c-purple-light), var(--c-purple-light)); color: var(--c-purple); }
.report-icon.slate { background: linear-gradient(135deg, var(--table-header-bg), var(--border-color)); color: var(--text-secondary); }
.report-title { font-size: 15px; font-weight: 700; color: var(--code-bg); margin-bottom: 6px; }
.report-desc { font-size: 12px; color: var(--text-muted); line-height: 1.5; }

/* ── Template List ── */
.template-list { border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; }
.template-item { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-bottom: 1px solid var(--border-light); transition: background .15s; }
.template-item:last-child { border-bottom: none; }
.template-item:hover { background: var(--table-header-bg); }
.template-name { font-weight: 600; color: var(--code-bg); font-size: 14px; }
.template-meta { font-size: 12px; color: var(--text-faint); margin-top: 2px; }
.template-actions { display: flex; gap: 8px; }
.empty-state { padding: 50px 20px; text-align: center; color: var(--text-faint); font-size: 14px; }
.empty-state i { font-size: 40px; margin-bottom: 12px; display: block; color: var(--hover-border); }

/* ── Settings Panel ── */
.settings-bar { background: var(--table-header-bg); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px 20px; margin-bottom: 16px; display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; }
.settings-bar .form-group { margin-bottom: 0; min-width: 140px; }

/* ── Modal ── */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.show { display: flex; }
.modal { background: #fff; border-radius: 16px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-light); }
.modal-header h3 { font-size: 18px; font-weight: 700; color: var(--code-bg); display: flex; align-items: center; gap: 10px; }
.modal-close { width: 32px; height: 32px; border-radius: 8px; background: var(--table-header-bg); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; color: var(--text-muted); }
.modal-close:hover { background: var(--c-danger-light); color: var(--c-danger); }
.modal-body { padding: 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 18px 24px; border-top: 1px solid var(--border-light); }

/* ── Toast ── */
.toast { position: fixed; bottom: 24px; right: 24px; padding: 14px 22px; border-radius: 10px; font-size: 14px; font-weight: 500; z-index: 10000; box-shadow: 0 8px 24px rgba(0,0,0,.15); display: none; align-items: center; gap: 8px; }
.toast.success { background: var(--c-success); color: #fff; }
.toast.error { background: var(--c-primary-hover); color: #fff; }
.toast.show { display: flex; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1>PDF Tools</h1>
        <p>Generate PDFs from HTML, download reports, and manage templates</p>
    </div>
</div>

<div class="tabs">
    <button class="tab-btn active" onclick="switchTab('html',this)"><i class="fas fa-code"></i> HTML to PDF</button>
    <button class="tab-btn" onclick="switchTab('reports',this)"><i class="fas fa-chart-bar"></i> Reports</button>
    <button class="tab-btn" onclick="switchTab('templates',this)"><i class="fas fa-save"></i> Saved Templates</button>
</div>

{{-- ═══ HTML TO PDF TAB ═══ --}}
<div class="tab-content active" id="tab-html">
<div class="card"><div class="card-body">
    <div class="settings-bar">
        <div class="form-group">
            <label>Paper Size</label>
            <select id="htmlPaper" class="form-control">
                <option value="a4">A4</option>
                <option value="a3">A3</option>
                <option value="letter">Letter</option>
                <option value="legal">Legal</option>
            </select>
        </div>
        <div class="form-group">
            <label>Orientation</label>
            <select id="htmlOrientation" class="form-control">
                <option value="portrait">Portrait</option>
                <option value="landscape">Landscape</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="btn btn-outline btn-sm" onclick="previewHtml()"><i class="fas fa-eye"></i> Preview</button>
            <button class="btn btn-primary btn-sm" onclick="downloadHtml()"><i class="fas fa-download"></i> Download PDF</button>
            <button class="btn btn-outline btn-sm" onclick="openSaveModal()"><i class="fas fa-save"></i> Save as Template</button>
        </div>
    </div>
    <textarea id="htmlEditor" class="code-editor" placeholder="Enter your HTML here...

Example:
<h1>My Report Title</h1>
<p>This is a paragraph of text.</p>
<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;width:100%;'>
  <tr style='background:var(--text-heading);color:#fff;'>
    <th>Name</th><th>Value</th>
  </tr>
  <tr><td>Item 1</td><td>100</td></tr>
  <tr><td>Item 2</td><td>200</td></tr>
</table>"></textarea>
</div></div>
</div>

{{-- ═══ REPORTS TAB ═══ --}}
<div class="tab-content" id="tab-reports">
<div class="card"><div class="card-body">
    <div class="settings-bar" style="margin-bottom:20px;">
        <div class="form-group">
            <label>Date From (for Login Log)</label>
            <input type="date" id="reportDateFrom" class="form-control">
        </div>
        <div class="form-group">
            <label>Date To</label>
            <input type="date" id="reportDateTo" class="form-control">
        </div>
    </div>

    <div class="report-grid">
        <div class="report-card" onclick="generateReport('admin-users')">
            <div class="report-icon blue"><i class="fas fa-users"></i></div>
            <div class="report-title">Admin Users</div>
            <div class="report-desc">All admin accounts with roles, status, 2FA, and last login</div>
        </div>
        <div class="report-card" onclick="generateReport('login-log')">
            <div class="report-icon green"><i class="fas fa-user-shield"></i></div>
            <div class="report-title">Login Activity</div>
            <div class="report-desc">Login history with IP, location, browser, and duration</div>
        </div>
        <div class="report-card" onclick="generateReport('configuration')">
            <div class="report-icon amber"><i class="fas fa-cogs"></i></div>
            <div class="report-title">System Configuration</div>
            <div class="report-desc">Full dump of all configuration groups and values</div>
        </div>
        <div class="report-card" onclick="generateReport('backup-summary')">
            <div class="report-icon purple"><i class="fas fa-database"></i></div>
            <div class="report-title">Backup Summary</div>
            <div class="report-desc">Backup runs with status, file counts, sizes, and timing</div>
        </div>
        <div class="report-card" onclick="generateReport('changelog')">
            <div class="report-icon red"><i class="fas fa-history"></i></div>
            <div class="report-title">Changelog</div>
            <div class="report-desc">Version history with all features and changes</div>
        </div>
        <div class="report-card" onclick="generateReport('role-permissions')">
            <div class="report-icon slate"><i class="fas fa-lock"></i></div>
            <div class="report-title">Role Permissions</div>
            <div class="report-desc">Matrix view of which roles can access which menus</div>
        </div>
    </div>
</div></div>
</div>

{{-- ═══ TEMPLATES TAB ═══ --}}
<div class="tab-content" id="tab-templates">
<div class="card"><div class="card-body">
    @if($templates->count() > 0)
    <div class="template-list">
        @foreach($templates as $tpl)
        <div class="template-item" id="tpl-{{ $tpl->id }}">
            <div>
                <div class="template-name">{{ $tpl->name }}</div>
                <div class="template-meta">{{ $tpl->paper_size }} / {{ $tpl->orientation }} — {{ $tpl->updated_at?->format('Y-m-d H:i') }}</div>
            </div>
            <div class="template-actions">
                <button class="btn btn-outline btn-sm" onclick="loadTemplate({{ $tpl->id }})"><i class="fas fa-edit"></i> Edit</button>
                <button class="btn btn-outline btn-sm" onclick="useTemplate({{ $tpl->id }})"><i class="fas fa-file-pdf"></i> Generate</button>
                <button class="btn btn-danger btn-sm" onclick="deleteTemplate({{ $tpl->id }})"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-save"></i>
        <p>No saved templates yet. Create one from the HTML to PDF tab.</p>
    </div>
    @endif
</div></div>
</div>

{{-- Save Template Modal --}}
<div class="modal-overlay" id="saveModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-save" style="color:var(--c-secondary);"></i> Save Template</h3>
            <button class="modal-close" onclick="closeSaveModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Template Name</label>
                <input type="text" id="tplName" class="form-control" placeholder="e.g. Monthly Report" required>
            </div>
            <div class="form-group">
                <label>Description (optional)</label>
                <input type="text" id="tplDesc" class="form-control" placeholder="Brief description...">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline btn-sm" onclick="closeSaveModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" onclick="saveTemplate()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let editingTemplateId = null;

function switchTab(name, el) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('tab-' + name).classList.add('active');
}

// ── HTML to PDF ──
function downloadHtml() {
    const html = document.getElementById('htmlEditor').value;
    if (!html.trim()) { showToast('Please enter some HTML content.', 'error'); return; }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.pdf-tools.html-to-pdf") }}';
    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrfToken}">
        <input type="hidden" name="html" value="${escAttr(html)}">
        <input type="hidden" name="paper_size" value="${document.getElementById('htmlPaper').value}">
        <input type="hidden" name="orientation" value="${document.getElementById('htmlOrientation').value}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function previewHtml() {
    const html = document.getElementById('htmlEditor').value;
    if (!html.trim()) { showToast('Please enter some HTML content.', 'error'); return; }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.pdf-tools.html-preview") }}';
    form.target = '_blank';
    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrfToken}">
        <input type="hidden" name="html" value="${escAttr(html)}">
        <input type="hidden" name="paper_size" value="${document.getElementById('htmlPaper').value}">
        <input type="hidden" name="orientation" value="${document.getElementById('htmlOrientation').value}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// ── Reports ──
function generateReport(report) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.pdf-tools.report") }}';
    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrfToken}">
        <input type="hidden" name="report" value="${report}">
        <input type="hidden" name="date_from" value="${document.getElementById('reportDateFrom').value}">
        <input type="hidden" name="date_to" value="${document.getElementById('reportDateTo').value}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// ── Templates ──
function openSaveModal() {
    const html = document.getElementById('htmlEditor').value;
    if (!html.trim()) { showToast('Please enter HTML content before saving.', 'error'); return; }
    editingTemplateId = null;
    document.getElementById('tplName').value = '';
    document.getElementById('tplDesc').value = '';
    document.getElementById('saveModal').classList.add('show');
}

function closeSaveModal() {
    document.getElementById('saveModal').classList.remove('show');
}

function saveTemplate() {
    const name = document.getElementById('tplName').value.trim();
    if (!name) { showToast('Template name is required.', 'error'); return; }

    fetch('{{ route("admin.pdf-tools.template.save") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
            id: editingTemplateId,
            name: name,
            description: document.getElementById('tplDesc').value,
            html_content: document.getElementById('htmlEditor').value,
            paper_size: document.getElementById('htmlPaper').value,
            orientation: document.getElementById('htmlOrientation').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeSaveModal();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Save failed.', 'error');
        }
    })
    .catch(() => showToast('Save failed.', 'error'));
}

function loadTemplate(id) {
    fetch(`{{ url("pdf-tools/templates") }}/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const tpl = data.template;
            document.getElementById('htmlEditor').value = tpl.html_content;
            document.getElementById('htmlPaper').value = tpl.paper_size || 'a4';
            document.getElementById('htmlOrientation').value = tpl.orientation || 'portrait';
            editingTemplateId = tpl.id;
            // Switch to HTML tab
            document.querySelectorAll('.tab-btn')[0].click();
            showToast('Template loaded. Edit and download or save.', 'success');
        }
    })
    .catch(() => showToast('Failed to load template.', 'error'));
}

function useTemplate(id) {
    fetch(`{{ url("pdf-tools/templates") }}/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const tpl = data.template;
            // Generate PDF directly
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.pdf-tools.html-to-pdf") }}';
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="html" value="${escAttr(tpl.html_content)}">
                <input type="hidden" name="paper_size" value="${tpl.paper_size || 'a4'}">
                <input type="hidden" name="orientation" value="${tpl.orientation || 'portrait'}">
            `;
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    })
    .catch(() => showToast('Failed to generate PDF.', 'error'));
}

function deleteTemplate(id) {
    if (!confirm('Delete this template? This cannot be undone.')) return;

    fetch(`{{ url("pdf-tools/templates") }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('tpl-' + id)?.remove();
        } else {
            showToast(data.message || 'Delete failed.', 'error');
        }
    })
    .catch(() => showToast('Delete failed.', 'error'));
}

// ── Helpers ──
function escAttr(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML.replace(/"/g, '&quot;');
}

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.className = `toast ${type} show`;
    t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    setTimeout(() => t.classList.remove('show'), 3500);
}


// Tab support in code editor
document.getElementById('htmlEditor').addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        const start = this.selectionStart;
        const end = this.selectionEnd;
        this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
        this.selectionStart = this.selectionEnd = start + 4;
    }
});
</script>
@endpush
