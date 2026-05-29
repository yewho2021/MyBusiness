@extends('admin.layouts.app')
@section('title', 'Export Center')

@push('styles')
<style>
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-header h1 { font-size: 24px; font-weight: 700; color: var(--header-text,var(--code-bg)); margin-bottom: 5px; }
.page-header p { font-size: 14px; color: var(--text-muted); }

.btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; transition: all .2s; }
.btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.btn-primary { background: linear-gradient(135deg, var(--c-danger) 0%, var(--c-primary-hover) 100%); color: #fff; }
.btn-outline { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--input-border); }
.btn-outline:hover { background: var(--table-header-bg,var(--table-header-bg)); transform: none; }
.btn-danger { color: var(--c-primary,var(--c-danger)); border: 1.5px solid var(--c-danger-border); background: var(--card-bg,#fff); }
.btn-danger:hover { background: var(--c-danger-light); }
.btn-sm { padding: 8px 14px; font-size: 13px; }
.btn-icon { width: 34px; height: 34px; padding: 0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; }

.alert { padding: 14px 18px; border-radius: var(--card-radius,10px); margin-bottom: 18px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
.alert-success { background: var(--c-success-light); color: var(--c-success); border: 1px solid var(--c-success-border); }
.alert-danger { background: var(--c-danger-light); color: var(--c-primary-hover); border: 1px solid var(--c-danger-border); }

/* ── Wizard ── */
.card { background: var(--card-bg,#fff); border-radius: 14px; border: 1px solid var(--border-color,var(--border-color)); box-shadow: 0 1px 3px rgba(0,0,0,.04); margin-bottom: 22px; }
.card-head { padding: 18px 22px; border-bottom: 1px solid var(--border-light,var(--border-light)); display: flex; justify-content: space-between; align-items: center; }
.card-title { font-size: 16px; font-weight: 600; color: var(--header-text,var(--code-bg)); display: flex; align-items: center; gap: 10px; }
.card-title i { color: var(--text-faint); }
.card-body { padding: 22px; }

/* ── Step Labels ── */
.step-label { font-size: 11px; font-weight: 700; color: var(--c-primary,var(--c-danger)); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.step-num { width: 24px; height: 24px; border-radius: 50%; background: var(--c-primary,var(--c-danger)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }

/* ── Source Grid ── */
.source-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-bottom: 24px; }
.source-card { border: 2px solid var(--border-color); border-radius: var(--card-radius,10px); padding: 16px; cursor: pointer; transition: all .2s; }
.source-card:hover { border-color: var(--text-faint); background: var(--table-header-bg,var(--table-header-bg)); }
.source-card.selected { border-color: var(--c-primary,var(--c-danger)); background: var(--c-danger-light); }
.source-icon { width: 40px; height: 40px; border-radius: var(--card-radius,10px); display: flex; align-items: center; justify-content: center; font-size: 17px; margin-bottom: 10px; }
.source-icon.blue { background: var(--c-secondary-light); color: var(--c-secondary,var(--c-secondary)); }
.source-icon.green { background: var(--c-success-light); color: var(--c-success); }
.source-icon.purple { background: var(--c-purple-light); color: var(--c-purple); }
.source-icon.amber { background: var(--c-warning-light); color: var(--c-warning); }
.source-icon.red { background: var(--c-danger-light); color: var(--c-primary,var(--c-danger)); }
.source-icon.slate { background: var(--border-color); color: var(--text-secondary); }
.source-icon.dark { background: var(--text-heading); color: var(--border-color); }
.source-name { font-size: 14px; font-weight: 600; color: var(--header-text,var(--code-bg)); margin-bottom: 3px; }
.source-desc { font-size: 11px; color: var(--text-faint); line-height: 1.4; }

/* ── Filters Section ── */
.filters-section { display: none; margin-bottom: 24px; padding: 18px; background: var(--table-header-bg,var(--table-header-bg)); border-radius: var(--card-radius,10px); border: 1px solid var(--border-color,var(--border-color)); }
.filters-section.show { display: block; }
.filter-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
@media(max-width:800px) { .filter-grid { grid-template-columns: 1fr; } }
.form-group { margin-bottom: 0; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .3px; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 14px; color: var(--header-text,var(--text-heading)); background: var(--card-bg,#fff); box-sizing: border-box; }
.form-control:focus { outline: none; border-color: var(--c-secondary); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

/* ── Format Selection ── */
.format-section { display: none; margin-bottom: 24px; }
.format-section.show { display: block; }
.format-options { display: flex; gap: 12px; flex-wrap: wrap; }
.format-option { border: 2px solid var(--border-color); border-radius: var(--card-radius,10px); padding: 16px 24px; cursor: pointer; transition: all .2s; display: flex; align-items: center; gap: 12px; min-width: 160px; }
.format-option:hover { border-color: var(--text-faint); }
.format-option.selected { border-color: var(--c-primary,var(--c-danger)); background: var(--c-danger-light); }
.format-option i { font-size: 24px; }
.format-option .xlsx-icon { color: var(--c-success); }
.format-option .csv-icon { color: var(--c-secondary,var(--c-secondary)); }
.format-option .pdf-icon { color: var(--c-primary,var(--c-danger)); }
.format-label { font-size: 14px; font-weight: 600; color: var(--header-text,var(--code-bg)); }
.format-sub { font-size: 11px; color: var(--text-faint); }

/* ── Action Bar ── */
.action-bar { display: none; padding: 18px; background: var(--table-header-bg,var(--table-header-bg)); border-radius: var(--card-radius,10px); border: 1px solid var(--border-color,var(--border-color)); margin-bottom: 24px; align-items: center; gap: 12px; }
.action-bar.show { display: flex; }

/* ── Preview Table ── */
.preview-section { display: none; margin-bottom: 24px; }
.preview-section.show { display: block; }
.preview-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.preview-table th { background: var(--text-heading); color: #fff; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
.preview-table td { padding: 7px 12px; border-bottom: 1px solid var(--border-color,var(--border-color)); color: var(--text-body); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.preview-table tr:nth-child(even) td { background: var(--table-header-bg,var(--table-header-bg)); }
.preview-info { padding: 10px 0; font-size: 13px; color: var(--text-muted); }

/* ── History Table ── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 12px 18px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; background: var(--table-header-bg,var(--table-header-bg)); border-bottom: 2px solid var(--border-light); }
.data-table td { padding: 12px 18px; font-size: 14px; color: var(--text-body); border-bottom: 1px solid var(--border-light,var(--border-light)); }
.data-table tbody tr:hover td { background: var(--table-header-bg,var(--table-header-bg)); }
.badge { display: inline-flex; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge.green { background: var(--c-success-light); color: var(--c-success); }
.badge.blue { background: var(--c-secondary-light); color: var(--c-secondary); }
.badge.red { background: var(--c-danger-light); color: var(--c-danger); }
.badge.gray { background: var(--hover-bg); color: var(--text-body); }

.empty-state { padding: 50px 20px; text-align: center; }
.empty-state i { font-size: 44px; color: var(--hover-border); margin-bottom: 14px; display: block; }
.empty-state p { font-size: 14px; color: var(--text-faint); }

.toast { position: fixed; bottom: 24px; right: 24px; padding: 14px 22px; border-radius: var(--card-radius,10px); font-size: 14px; font-weight: 500; z-index: 10000; box-shadow: 0 8px 24px rgba(0,0,0,.15); display: none; align-items: center; gap: 8px; }
.toast.success { background: var(--c-success); color: #fff; }
.toast.error { background: var(--c-primary-hover); color: #fff; }
.toast.show { display: flex; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

<div class="page-header">
    <div>
        <h1>Export Center</h1>
        <p>Export any portal data to Excel, CSV, or PDF</p>
    </div>
</div>

{{-- ═══ EXPORT WIZARD ═══ --}}
<div class="card">
    <div class="card-head">
        <div class="card-title"><i class="fas fa-file-export"></i> New Export</div>
    </div>
    <div class="card-body">

        {{-- Step 1: Select Source --}}
        <div class="step-label"><span class="step-num">1</span> Select Data Source</div>
        <div class="source-grid">
            @foreach($sources as $key => $src)
            <div class="source-card" data-source="{{ $key }}" onclick="selectSource('{{ $key }}', this)">
                <div class="source-icon {{ $src['color'] }}"><i class="fas {{ $src['icon'] }}"></i></div>
                <div class="source-name">{{ $src['label'] }}</div>
                <div class="source-desc">{{ $src['desc'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- Step 2: Filters --}}
        <div class="filters-section" id="filtersSection">
            <div class="step-label"><span class="step-num">2</span> Apply Filters (Optional)</div>

            {{-- Login Log filters --}}
            <div class="filter-set" id="filters-login_log" style="display:none;">
                <div class="filter-grid">
                    <div class="form-group"><label>Status</label>
                        <select class="form-control" data-filter="status">
                            <option value="">All</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Date From</label><input type="date" class="form-control" data-filter="date_from"></div>
                    <div class="form-group"><label>Date To</label><input type="date" class="form-control" data-filter="date_to"></div>
                </div>
            </div>

            {{-- Activity Log filters --}}
            <div class="filter-set" id="filters-activity_log" style="display:none;">
                <div class="filter-grid">
                    <div class="form-group"><label>Event</label>
                        <select class="form-control" data-filter="event">
                            <option value="">All</option>
                            <option value="created">Created</option>
                            <option value="updated">Updated</option>
                            <option value="deleted">Deleted</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Date From</label><input type="date" class="form-control" data-filter="date_from"></div>
                    <div class="form-group"><label>Date To</label><input type="date" class="form-control" data-filter="date_to"></div>
                </div>
            </div>

            {{-- Configuration filters --}}
            <div class="filter-set" id="filters-configuration" style="display:none;">
                <div class="filter-grid">
                    <div class="form-group"><label>Group</label>
                        <select class="form-control" data-filter="group">
                            <option value="">All Groups</option>
                            <option value="brand">Brand</option>
                            <option value="colors">Colors</option>
                            <option value="sidebar">Sidebar</option>
                            <option value="header">Header</option>
                            <option value="typography">Typography</option>
                            <option value="layout">Layout</option>
                            <option value="login">Login</option>
                            <option value="email">Email</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Backup filters --}}
            <div class="filter-set" id="filters-backup_history" style="display:none;">
                <div class="filter-grid">
                    <div class="form-group"><label>Status</label>
                        <select class="form-control" data-filter="status">
                            <option value="">All</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="running">Running</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Date From</label><input type="date" class="form-control" data-filter="date_from"></div>
                    <div class="form-group"><label>Date To</label><input type="date" class="form-control" data-filter="date_to"></div>
                </div>
            </div>

            {{-- Custom SQL --}}
            <div class="filter-set" id="filters-custom_query" style="display:none;">
                <div class="form-group">
                    <label>SQL Query (SELECT only)</label>
                    <textarea class="form-control" data-filter="sql" rows="4" style="font-family:monospace;font-size:13px;" placeholder="SELECT * FROM tbl_admin LIMIT 100"></textarea>
                </div>
            </div>
        </div>

        {{-- Step 3: Format --}}
        <div class="format-section" id="formatSection">
            <div class="step-label"><span class="step-num">3</span> Choose Export Format</div>
            <div class="format-options">
                <div class="format-option selected" data-format="xlsx" onclick="selectFormat('xlsx', this)">
                    <i class="fas fa-file-excel xlsx-icon"></i>
                    <div><div class="format-label">Excel (.xlsx)</div><div class="format-sub">Styled with headers</div></div>
                </div>
                <div class="format-option" data-format="csv" onclick="selectFormat('csv', this)">
                    <i class="fas fa-file-csv csv-icon"></i>
                    <div><div class="format-label">CSV (.csv)</div><div class="format-sub">Plain text data</div></div>
                </div>
                <div class="format-option" data-format="pdf" onclick="selectFormat('pdf', this)">
                    <i class="fas fa-file-pdf pdf-icon"></i>
                    <div><div class="format-label">PDF (.pdf)</div><div class="format-sub">Formatted report</div></div>
                </div>
            </div>
        </div>

        {{-- Preview --}}
        <div class="preview-section" id="previewSection"></div>

        {{-- Actions --}}
        <div class="action-bar" id="actionBar">
            <button class="btn btn-outline btn-sm" onclick="previewData()"><i class="fas fa-eye"></i> Preview (10 rows)</button>
            <button class="btn btn-primary btn-sm" onclick="exportData()"><i class="fas fa-download"></i> Export & Download</button>
        </div>
    </div>
</div>

{{-- ═══ EXPORT HISTORY ═══ --}}
<div class="card">
    <div class="card-head">
        <div class="card-title"><i class="fas fa-history"></i> Export History</div>
        @if($history->count() > 0)
        <form method="POST" action="{{ route('admin.export.clear') }}" onsubmit="return confirm('Clear all export history and files?')">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Clear All</button>
        </form>
        @endif
    </div>

    @if($history->count() > 0)
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Format</th>
                    <th>Rows</th>
                    <th>Size</th>
                    <th style="width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($history as $h)
                <tr id="history-{{ $h->id }}">
                    <td style="white-space:nowrap;">{{ $h->created_at?->format('Y-m-d H:i') }}</td>
                    <td><span class="badge gray">{{ $h->source_label }}</span></td>
                    <td>
                        @php
                            $fmtClass = match($h->format) { 'xlsx' => 'green', 'csv' => 'blue', 'pdf' => 'red', default => 'gray' };
                        @endphp
                        <span class="badge {{ $fmtClass }}">{{ strtoupper($h->format) }}</span>
                    </td>
                    <td>{{ number_format($h->row_count) }}</td>
                    <td>{{ $h->file_size_human }}</td>
                    <td style="display:flex;gap:6px;">
                        <a href="{{ route('admin.export.download', $h->id) }}" class="btn btn-outline btn-icon btn-sm" title="Download"><i class="fas fa-download"></i></a>
                        <button class="btn btn-danger btn-icon btn-sm" onclick="deleteExport({{ $h->id }})" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-download"></i>
        <p>No exports yet. Use the wizard above to export data.</p>
    </div>
    @endif
</div>

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let selectedSource = null;
let selectedFormat = 'xlsx';

function selectSource(source, el) {
    selectedSource = source;
    document.querySelectorAll('.source-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');

    // Show filters
    document.getElementById('filtersSection').classList.add('show');
    document.querySelectorAll('.filter-set').forEach(f => f.style.display = 'none');
    const filterSet = document.getElementById('filters-' + source);
    if (filterSet) filterSet.style.display = 'block';

    // Show format and actions
    document.getElementById('formatSection').classList.add('show');
    document.getElementById('actionBar').classList.add('show');

    // Hide preview
    document.getElementById('previewSection').classList.remove('show');
}

function selectFormat(format, el) {
    selectedFormat = format;
    document.querySelectorAll('.format-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
}

function getFilters() {
    const filters = {};
    const filterSet = document.getElementById('filters-' + selectedSource);
    if (filterSet) {
        filterSet.querySelectorAll('[data-filter]').forEach(el => {
            const key = el.dataset.filter;
            const val = el.value;
            if (val) filters[key] = val;
        });
    }
    return filters;
}

function previewData() {
    if (!selectedSource) { showToast('Please select a data source.', 'error'); return; }

    const section = document.getElementById('previewSection');
    section.classList.add('show');
    section.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-faint);"><i class="fas fa-spinner fa-spin" style="font-size:20px;"></i> Loading preview...</div>';

    const params = { source: selectedSource, _token: csrfToken, ...getFilters() };

    fetch('{{ route("admin.export.preview") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(params),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            section.innerHTML = `<div style="color:var(--c-primary,var(--c-danger));padding:16px;">${data.message || 'Preview failed.'}</div>`;
            return;
        }

        let html = `<div class="preview-info"><strong>${data.total}</strong> total rows found — showing first ${data.rows.length}</div>`;
        html += '<div style="overflow-x:auto;"><table class="preview-table"><thead><tr>';
        data.headings.forEach(h => html += `<th>${h}</th>`);
        html += '</tr></thead><tbody>';

        if (data.rows.length === 0) {
            html += `<tr><td colspan="${data.headings.length}" style="text-align:center;padding:20px;color:var(--text-faint);">No data found.</td></tr>`;
        } else {
            data.rows.forEach(row => {
                html += '<tr>';
                row.forEach(cell => {
                    const val = cell === null ? '—' : String(cell);
                    html += `<td title="${val.replace(/"/g,'&quot;')}">${val.length > 60 ? val.substring(0,60) + '...' : val}</td>`;
                });
                html += '</tr>';
            });
        }

        html += '</tbody></table></div>';
        section.innerHTML = html;
    })
    .catch(err => {
        section.innerHTML = `<div style="color:var(--c-primary,var(--c-danger));padding:16px;">Preview failed: ${err.message}</div>`;
    });
}

function exportData() {
    if (!selectedSource) { showToast('Please select a data source.', 'error'); return; }

    const filters = getFilters();
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.export.generate") }}';
    form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">
        <input type="hidden" name="source" value="${selectedSource}">
        <input type="hidden" name="format" value="${selectedFormat}">`;

    Object.keys(filters).forEach(k => {
        form.innerHTML += `<input type="hidden" name="${k}" value="${filters[k].replace(/"/g,'&quot;')}">`;
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    showToast('Generating export...', 'success');
}

function deleteExport(id) {
    if (!confirm('Delete this export?')) return;

    fetch(`{{ url("export") }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('history-' + id)?.remove();
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Delete failed.', 'error');
        }
    })
    .catch(() => showToast('Delete failed.', 'error'));
}

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.className = `toast ${type} show`;
    t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    setTimeout(() => t.classList.remove('show'), 3500);
}
</script>
@endpush
