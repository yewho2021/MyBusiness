@extends('admin.layouts.app')
@section('title', 'Database Connections')

@push('styles')
<style>
/* ═══════════════════════════════════════════════
   DATABASE CONNECTIONS — Management UI
   ═══════════════════════════════════════════════ */
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.page-title { font-size:22px; font-weight:700; color:#1e293b; }
.page-sub { font-size:15px; color:#64748b; }

.nav-pills { display:flex; gap:6px; flex-wrap:wrap; }
.nav-pill { padding:8px 16px; border-radius:8px; font-size:15px; font-weight:500; text-decoration:none; color:#64748b; border:1px solid #e2e8f0; display:inline-flex; align-items:center; gap:8px; transition:all .2s; }
.nav-pill:hover { background:#f8fafc; color:#1e293b; border-color:#cbd5e1; }
.nav-pill.active { background:#dc2626; color:#fff; border-color:#dc2626; box-shadow:0 2px 4px rgba(220,38,38,.2); }

.btn-primary { background:#dc2626; color:#fff; border:none; padding:10px 18px; border-radius:8px; font-weight:500; font-size:16px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all .2s; box-shadow:0 2px 4px rgba(220,38,38,.2); }
.btn-primary:hover { background:#b91c1c; box-shadow:0 4px 6px rgba(220,38,38,.25); transform:translateY(-1px); }

.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; margin-bottom:20px; }
.stat-card { background:#fff; border-radius:12px; padding:18px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:16px; box-shadow:0 2px 4px rgba(0,0,0,.02); transition:all .2s; }
.stat-card:hover { transform:translateY(-2px); box-shadow:0 6px 12px rgba(0,0,0,.05); }
.stat-icon { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; }
.stat-icon.blue { background:#eff6ff; color:#3b82f6; }
.stat-icon.green { background:#f0fdf4; color:#22c55e; }
.stat-icon.amber { background:#fffbeb; color:#f59e0b; }
.stat-val { font-size:22px; font-weight:700; color:#1e293b; line-height:1.2; margin-bottom:2px; }
.stat-label { font-size:14px; color:#64748b; font-weight:500; }

.card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:20px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,.02); }
.card-header { padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#fff; }
.card-title { font-size:16px; font-weight:600; color:#1e293b; }

/* Connection cards grid */
.conn-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(360px,1fr)); gap:16px; padding:20px; }
.conn-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:20px; transition:all .2s; position:relative; }
.conn-card:hover { border-color:#bfdbfe; box-shadow:0 4px 12px rgba(59,130,246,.08); }
.conn-card.inactive { opacity:.6; }
.conn-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
.conn-name { font-size:16px; font-weight:600; color:#1e293b; display:flex; align-items:center; gap:8px; }
.conn-name i { color:#2563eb; font-size:16px; }
.conn-badge { font-size:12px; padding:2px 8px; border-radius:10px; font-weight:600; }
.conn-badge.active { background:#f0fdf4; color:#16a34a; }
.conn-badge.inactive { background:#eff6ff; color:#dc2626; }
.conn-details { font-size:14px; color:#64748b; margin-bottom:14px; }
.conn-detail { display:flex; align-items:center; gap:8px; padding:4px 0; }
.conn-detail i { width:14px; text-align:center; color:#94a3b8; font-size:13px; }
.conn-detail strong { color:#334155; font-weight:500; }
.conn-desc { font-size:14px; color:#94a3b8; font-style:italic; margin-bottom:12px; padding:8px 10px; background:#f8fafc; border-radius:6px; }
.conn-actions { display:flex; gap:6px; flex-wrap:wrap; }
.conn-btn { padding:6px 12px; font-size:14px; font-weight:500; border-radius:6px; cursor:pointer; border:1px solid transparent; display:inline-flex; align-items:center; gap:5px; transition:all .15s; text-decoration:none; }
.conn-btn.browse { background:#dc2626; color:#fff; border-color:#dc2626; }
.conn-btn.browse:hover { background:#b91c1c; }
.conn-btn.edit { background:#fff; color:#3b82f6; border-color:#bfdbfe; }
.conn-btn.edit:hover { background:#eff6ff; }
.conn-btn.delete { background:#fff; color:#ef4444; border-color:#bfdbfe; }
.conn-btn.delete:hover { background:#fef2f2; }
.conn-btn.toggle { background:#fff; color:#64748b; border-color:#e2e8f0; }
.conn-btn.toggle:hover { background:#f8fafc; }
.conn-time { font-size:13px; color:#cbd5e1; margin-top:10px; padding-top:10px; border-top:1px solid #f1f5f9; }

/* Empty state */
.empty-state { text-align:center; padding:60px 20px; }
.empty-state i { font-size:48px; color:#cbd5e1; display:block; margin-bottom:16px; }
.empty-state p { font-size:16px; color:#64748b; margin-bottom:8px; }
.empty-state span { font-size:15px; color:#94a3b8; }

/* ── Modal ───────────────────────────────────── */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9998; display:none; justify-content:center; align-items:center; backdrop-filter:blur(3px); }
.modal-overlay.show { display:flex; }
.modal { background:#fff; border-radius:14px; width:100%; max-width:540px; max-height:90vh; overflow:auto; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.modal-head { padding:20px 24px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
.modal-title { font-size:16px; font-weight:600; color:#1e293b; }
.modal-close { background:none; border:none; font-size:18px; color:#94a3b8; cursor:pointer; padding:4px; }
.modal-close:hover { color:#334155; }
.modal-body { padding:24px; }
.modal-foot { padding:16px 24px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:8px; }

.form-group { margin-bottom:16px; }
.form-label { display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:6px; }
.form-input { width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:15px; transition:all .2s; outline:none; }
.form-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.form-hint { font-size:13px; color:#94a3b8; margin-top:4px; }

.btn-cancel { background:#f1f5f9; color:#475569; border:none; padding:10px 18px; border-radius:8px; font-weight:500; font-size:15px; cursor:pointer; }
.btn-cancel:hover { background:#e2e8f0; }
.btn-save { background:#dc2626; color:#fff; border:none; padding:10px 18px; border-radius:8px; font-weight:500; font-size:15px; cursor:pointer; }
.btn-save:hover { background:#b91c1c; }
.btn-test { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; padding:10px 18px; border-radius:8px; font-weight:500; font-size:15px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.btn-test:hover { background:#dcfce7; }

.test-result { margin-top:12px; padding:10px 14px; border-radius:8px; font-size:15px; display:none; }
.test-result.success { display:block; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
.test-result.error { display:block; background:#eff6ff; border:1px solid #bfdbfe; color:#991b1b; }

.toast { position:fixed; bottom:24px; right:24px; padding:12px 20px; border-radius:8px; color:#fff; font-size:15px; z-index:10000; display:flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(0,0,0,.15); animation:slideIn .3s ease; }
@keyframes slideIn { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-plug" style="color:#2563eb"></i> Database Connections</h1>
        <p class="page-sub">Manage saved database connections</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <button class="btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Connection</button>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-plug"></i></div>
        <div>
            <div class="stat-val">{{ $connections->count() }}</div>
            <div class="stat-label">Saved Connections</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-val">{{ $connections->where('is_active', true)->count() }}</div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-database"></i></div>
        <div>
            <div class="stat-val">{{ config('database.connections.mysql.database') }}</div>
            <div class="stat-label">Default (from .env)</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">All Saved Connections</span>
        @if(session('db_connection_id'))
            <a href="{{ route('admin.database.connections.clear') }}" class="conn-btn" style="background:#f0f9ff;color:#0369a1;border-color:#bae6fd">
                <i class="fas fa-undo"></i> Switch to Default DB
            </a>
        @endif
    </div>

    @if($connections->isEmpty())
        <div class="empty-state">
            <i class="fas fa-database"></i>
            <p>No saved connections yet</p>
            <span>Click "Add Connection" to save your first database connection.</span>
        </div>
    @else
        <div class="conn-grid">
            {{-- Default .env connection card --}}
            <div class="conn-card" style="border-left:3px solid #22c55e;">
                <div class="conn-header">
                    <div class="conn-name"><i class="fas fa-home"></i> Default Connection</div>
                    <span class="conn-badge active">ENV</span>
                </div>
                <div class="conn-details">
                    <div class="conn-detail"><i class="fas fa-server"></i> <strong>{{ config('database.connections.mysql.host') }}:{{ config('database.connections.mysql.port', '3306') }}</strong></div>
                    <div class="conn-detail"><i class="fas fa-database"></i> <strong>{{ config('database.connections.mysql.database') }}</strong></div>
                    <div class="conn-detail"><i class="fas fa-user"></i> <strong>{{ config('database.connections.mysql.username') }}</strong></div>
                </div>
                <div class="conn-actions">
                    @if(session('db_connection_id'))
                        <a href="{{ route('admin.database.connections.clear') }}" class="conn-btn browse"><i class="fas fa-terminal"></i> Switch Back</a>
                    @else
                        <a href="{{ route('admin.database.query') }}" class="conn-btn browse"><i class="fas fa-terminal"></i> Browse</a>
                    @endif
                </div>
            </div>

            {{-- Saved connections --}}
            @foreach($connections as $conn)
            <div class="conn-card {{ !$conn->is_active ? 'inactive' : '' }}" id="conn-{{ $conn->id }}" style="{{ session('db_connection_id') == $conn->id ? 'border-left:3px solid #2563eb;' : '' }}">
                <div class="conn-header">
                    <div class="conn-name">
                        <i class="fas fa-database"></i> {{ $conn->name }}
                        @if(session('db_connection_id') == $conn->id)
                            <span style="font-size:12px;background:#f0fdf4;color:#16a34a;padding:2px 6px;border-radius:4px;font-weight:600">ACTIVE</span>
                        @endif
                    </div>
                    <span class="conn-badge {{ $conn->is_active ? 'active' : 'inactive' }}">
                        {{ $conn->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="conn-details">
                    <div class="conn-detail"><i class="fas fa-server"></i> <strong>{{ $conn->dbhost }}:{{ $conn->dbport }}</strong></div>
                    <div class="conn-detail"><i class="fas fa-database"></i> <strong>{{ $conn->dbname }}</strong></div>
                    <div class="conn-detail"><i class="fas fa-user"></i> <strong>{{ $conn->dbusername }}</strong></div>
                </div>
                @if($conn->description)
                    <div class="conn-desc">{{ $conn->description }}</div>
                @endif
                <div class="conn-actions">
                    @if($conn->is_active)
                        <a href="{{ route('admin.database.connections.browse', $conn->id) }}" class="conn-btn browse"><i class="fas fa-terminal"></i> Browse</a>
                    @endif
                    <button class="conn-btn edit" onclick="openEditModal({{ $conn->id }}, {{ json_encode($conn->only(['name','dbhost','dbport','dbname','dbusername','description'])) }})"><i class="fas fa-pen"></i> Edit</button>
                    <button class="conn-btn toggle" onclick="toggleConn({{ $conn->id }})">
                        <i class="fas fa-{{ $conn->is_active ? 'pause' : 'play' }}"></i> {{ $conn->is_active ? 'Disable' : 'Enable' }}
                    </button>
                    <button class="conn-btn delete" onclick="deleteConn({{ $conn->id }}, '{{ addslashes($conn->name) }}')"><i class="fas fa-trash"></i></button>
                </div>
                @if($conn->last_connected_at)
                    <div class="conn-time"><i class="fas fa-clock"></i> Last browsed: {{ $conn->last_connected_at->diffForHumans() }}</div>
                @endif
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add/Edit Modal --}}
<div class="modal-overlay" id="connModal">
    <div class="modal">
        <div class="modal-head">
            <span class="modal-title" id="modalTitle">Add Database Connection</span>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="connForm" onsubmit="saveConnection(event)">
            @csrf
            <input type="hidden" id="connId" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Connection Name *</label>
                    <input type="text" class="form-input" id="fName" name="name" placeholder="e.g. Production DB, Staging, Client XYZ" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Host *</label>
                        <input type="text" class="form-input" id="fHost" name="dbhost" placeholder="localhost or IP" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Port</label>
                        <input type="text" class="form-input" id="fPort" name="dbport" placeholder="3306" value="3306">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Database Name *</label>
                    <input type="text" class="form-input" id="fDbname" name="dbname" placeholder="database_name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-input" id="fUser" name="dbusername" placeholder="db_user" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-input" id="fPass" name="dbpassword" placeholder="••••••••">
                        <div class="form-hint" id="passHint" style="display:none">Leave blank to keep current password</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" class="form-input" id="fDesc" name="description" placeholder="Brief note about this connection">
                </div>
                <div id="testResult" class="test-result"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-test" onclick="testConnection()"><i class="fas fa-plug"></i> Test Connection</button>
                <div style="flex:1"></div>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-save" id="btnSave"><i class="fas fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div class="toast" style="background:#16a34a" id="toastMsg"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
<script>setTimeout(()=>document.getElementById('toastMsg')?.remove(),4000)</script>
@endif
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openModal() {
    document.getElementById('modalTitle').textContent = 'Add Database Connection';
    document.getElementById('connId').value = '';
    document.getElementById('connForm').reset();
    document.getElementById('fPort').value = '3306';
    document.getElementById('fPass').required = true;
    document.getElementById('passHint').style.display = 'none';
    document.getElementById('testResult').className = 'test-result';
    document.getElementById('testResult').textContent = '';
    document.getElementById('connModal').classList.add('show');
}

function openEditModal(id, data) {
    document.getElementById('modalTitle').textContent = 'Edit Connection';
    document.getElementById('connId').value = id;
    document.getElementById('fName').value = data.name || '';
    document.getElementById('fHost').value = data.dbhost || '';
    document.getElementById('fPort').value = data.dbport || '3306';
    document.getElementById('fDbname').value = data.dbname || '';
    document.getElementById('fUser').value = data.dbusername || '';
    document.getElementById('fPass').value = '';
    document.getElementById('fPass').required = false;
    document.getElementById('passHint').style.display = 'block';
    document.getElementById('fDesc').value = data.description || '';
    document.getElementById('testResult').className = 'test-result';
    document.getElementById('testResult').textContent = '';
    document.getElementById('connModal').classList.add('show');
}

function closeModal() {
    document.getElementById('connModal').classList.remove('show');
}

async function saveConnection(e) {
    e.preventDefault();
    const id = document.getElementById('connId').value;
    const form = document.getElementById('connForm');
    const data = Object.fromEntries(new FormData(form));
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    const url = id
        ? "{{ url('database/connections') }}/" + id
        : "{{ route('admin.database.connections.store') }}";

    // Always POST — use Laravel _method spoofing for PUT (cPanel Apache blocks raw PUT)
    if (id) data._method = 'PUT';

    try {
        const res = await fetch(url, {
            method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify(data)
        });
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const text = await res.text();
            throw new Error('Server returned non-JSON (status ' + res.status + '). Check Laravel logs.');
        }
        const result = await res.json();
        if (result.success) {
            location.reload();
        } else {
            if (result.errors) {
                const msgs = Object.values(result.errors).flat().join(', ');
                toast(msgs, 'error');
            } else {
                toast(result.message || 'Save failed', 'error');
            }
        }
    } catch(err) {
        toast('Error: ' + err.message, 'error');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save';
    }
}

async function testConnection() {
    const el = document.getElementById('testResult');
    el.className = 'test-result'; el.style.display = 'block';
    el.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing connection...';

    const data = {
        dbhost: document.getElementById('fHost').value,
        dbport: document.getElementById('fPort').value || '3306',
        dbname: document.getElementById('fDbname').value,
        dbusername: document.getElementById('fUser').value,
        dbpassword: document.getElementById('fPass').value,
    };

    try {
        const res = await fetch("{{ route('admin.database.connections.test') }}", {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        el.className = 'test-result ' + (result.success ? 'success' : 'error');
        el.innerHTML = '<i class="fas fa-' + (result.success ? 'check-circle' : 'times-circle') + '"></i> ' + result.message;
    } catch(err) {
        el.className = 'test-result error';
        el.innerHTML = '<i class="fas fa-times-circle"></i> Connection test failed: ' + err.message;
    }
}

async function deleteConn(id, name) {
    if (!confirm('Delete connection "' + name + '"? This cannot be undone.')) return;
    try {
        await fetch("{{ url('database/connections') }}/" + id, {
            method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({_method: 'DELETE'})
        });
        location.reload();
    } catch(err) { toast('Delete failed', 'error'); }
}

async function toggleConn(id) {
    try {
        const res = await fetch("{{ url('database/connections') }}/" + id + "/toggle", {
            method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
        });
        location.reload();
    } catch(err) { toast('Toggle failed', 'error'); }
}

function toast(msg, type='info') {
    const t = document.createElement('div');
    t.className = 'toast';
    t.style.background = type==='success'?'#16a34a':type==='error'?'#dc2626':'#dc2626';
    t.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'times-circle':'info-circle'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
}

// Modal only closes via X button or Cancel — not by clicking outside
document.getElementById('connModal').addEventListener('click', function(e) {
    // Do nothing — prevent accidental dismissal
});
</script>
@endpush
