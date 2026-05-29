@extends('admin.layouts.app')

@section('title', 'Backup Jobs')

@push('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .page-title { font-size: 22px; font-weight: 700; color: #1e293b; }
    .breadcrumb { font-size: 13px; color: #64748b; margin-bottom: 4px; }
    .breadcrumb a { color: #4f46e5; text-decoration: none; }

    .section-card { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 24px; }
    .section-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .section-title { font-size: 16px; font-weight: 600; color: #1e293b; }

    .btn-primary { background: #4f46e5; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary:hover { background: #4338ca; color: #fff; }
    .btn-sm { padding: 5px 12px; font-size: 12px; }
    .btn-success { background: #22c55e; color: #fff; border: none; padding: 5px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .btn-danger { background: #ef4444; color: #fff; border: none; padding: 5px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; }
    .btn-outline { background: transparent; border: 1px solid #d1d5db; color: #374151; padding: 5px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .btn-outline:hover { background: #f3f4f6; color: #374151; }
    .btn-warning { background: #f59e0b; color: #fff; border: none; padding: 5px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; }

    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 10px 16px; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    td { padding: 12px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    tr:hover td { background: #fafbfc; }

    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-active { background: #dcfce7; color: #166534; }
    .badge-inactive { background: #f1f5f9; color: #64748b; }
    .badge-freq { background: #eff6ff; color: #1e40af; }

    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
    .modal-box { background:#fff; border-radius:12px; padding:24px; width:90%; max-width:560px; max-height:90vh; overflow-y:auto; }
    .modal-title { font-size:18px; font-weight:600; margin-bottom:16px; }
    .form-group { margin-bottom: 14px; }
    .form-label { display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#374151; }
    .form-input { width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; }
    .form-input:focus { outline:none; border-color:#4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
    .form-textarea { width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; font-family:monospace; }
    .form-select { width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; background:#fff; }

    .empty-state { text-align: center; padding: 40px 20px; color: #94a3b8; }
    .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; }

    .tag { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; background: #f1f5f9; color: #475569; margin: 2px; font-family: monospace; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.backup.index') }}">Backup</a> &rsaquo; Jobs</div>
        <h1 class="page-title">Backup Jobs</h1>
    </div>
    <button onclick="openCreateModal()" class="btn-primary"><i class="fas fa-plus"></i> Create Job</button>
</div>

<div class="section-card">
    @if($jobs->isEmpty())
        <div class="empty-state">
            <i class="fas fa-briefcase"></i>
            <p>No backup jobs yet.</p>
            <button onclick="openCreateModal()" class="btn-primary" style="margin-top: 12px;"><i class="fas fa-plus"></i> Create First Job</button>
        </div>
    @else
    <table>
        <thead>
            <tr>
                <th>Job Name</th>
                <th>Frequency</th>
                <th>Include Paths</th>
                <th>Destination</th>
                <th>Database</th>
                <th>Retention</th>
                <th>Status</th>
                <th>Last Run</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobs as $job)
            <tr>
                <td style="font-weight: 500;">{{ $job->name }}</td>
                <td>
                    <span class="badge badge-freq">{{ ucfirst($job->frequency) }}</span>
                    @if($job->frequency === 'custom')
                        <br><code style="font-size:11px; color:#64748b;">{{ $job->getRawOriginal('cron_expression') }}</code>
                    @endif
                </td>
                <td>
                    @foreach(($job->include_paths ?? []) as $path)
                        <span class="tag">{{ $path }}</span>
                    @endforeach
                    @if(empty($job->include_paths))
                        <span style="color:#94a3b8; font-size:12px;">Default</span>
                    @endif
                </td>
                <td>
                    <code style="font-size:12px; color:#475569;">{{ $job->destination_path ?: 'backup/' }}</code>
                </td>
                <td>
                    @if($job->include_database)
                        <i class="fas fa-check-circle" style="color:#22c55e;"></i>
                    @else
                        <i class="fas fa-times-circle" style="color:#d1d5db;"></i>
                    @endif
                </td>
                <td>Keep {{ $job->retention_count }}</td>
                <td>
                    <span class="badge {{ $job->is_active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $job->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td style="white-space: nowrap;">
                    {{ $job->last_run_at ? $job->last_run_at->format('d M Y H:i') : 'Never' }}
                </td>
                <td style="white-space: nowrap;">
                    <form method="POST" action="{{ route('admin.backup.run.now', $job->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-success btn-sm" title="Run Now"><i class="fas fa-play"></i> Run</button>
                    </form>
                    <button onclick="openEditModal({{ json_encode($job) }})" class="btn-outline btn-sm" title="Edit"><i class="fas fa-edit"></i></button>
                    <form method="POST" action="{{ route('admin.backup.jobs.toggle', $job->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-warning btn-sm" title="{{ $job->is_active ? 'Disable' : 'Enable' }}">
                            <i class="fas fa-{{ $job->is_active ? 'pause' : 'play' }}"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.backup.jobs.delete', $job->id) }}" style="display:inline;" onsubmit="return confirm('Delete this job and all its backup history?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Create/Edit Modal --}}
<div id="jobModal" class="modal-overlay">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 class="modal-title" id="modalTitle">Create Backup Job</h3>
            <button onclick="closeModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>
        <form id="jobForm" method="POST" action="{{ route('admin.backup.jobs.store') }}">
            @csrf
            <div id="methodField"></div>
            <div class="form-group">
                <label class="form-label">Job Name</label>
                <input type="text" name="name" id="jobName" class="form-input" required placeholder="e.g. Daily Full Backup">
            </div>
            <div class="form-group">
                <label class="form-label">Frequency</label>
                <select name="frequency" id="jobFrequency" class="form-select" onchange="toggleCron()">
                    <option value="daily">Daily (2:00 AM)</option>
                    <option value="weekly">Weekly (Sunday 2:00 AM)</option>
                    <option value="monthly">Monthly (1st day 2:00 AM)</option>
                    <option value="custom">Custom Cron</option>
                </select>
            </div>
            <div class="form-group" id="cronGroup" style="display:none;">
                <label class="form-label">Cron Expression</label>
                <input type="text" name="cron_expression" id="jobCron" class="form-input" placeholder="0 2 * * *" style="font-family:monospace;">
                <span style="font-size:11px; color:#94a3b8;">Format: minute hour day month weekday</span>
            </div>
            <div class="form-group">
                <label class="form-label">Destination Path <span style="color:#94a3b8;">(where backups are stored)</span></label>
                <input type="text" name="destination_path" id="jobDestination" class="form-input" placeholder="backup" style="font-family:monospace;">
                <span style="font-size:11px; color:#94a3b8;">Relative to project root (e.g. <code>backup</code>) or absolute (e.g. <code>/home/syncoffice/backups</code>). Leave empty = <code>backup/</code></span>
            </div>
            <div class="form-group">
                <label class="form-label">Include Paths <span style="color:#94a3b8;">(one per line, empty = default)</span></label>
                <textarea name="include_paths" id="jobInclude" class="form-textarea" rows="4" placeholder="app&#10;config&#10;database&#10;resources&#10;routes&#10;storage/app"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Exclude Paths <span style="color:#94a3b8;">(one per line)</span></label>
                <textarea name="exclude_paths" id="jobExclude" class="form-textarea" rows="3" placeholder="vendor&#10;node_modules&#10;storage/logs"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Exclude File Extensions <span style="color:#94a3b8;">(comma or newline separated)</span></label>
                <textarea name="exclude_extensions" id="jobExcludeExt" class="form-textarea" rows="2" placeholder="zip,tar,gz,rar,7z,mp4,avi,mov,log,tmp"></textarea>
                <span style="font-size:11px;color:#94a3b8;">Common: zip, tar, gz, rar, 7z, mp4, avi, mov, mkv, log, tmp, bak, swp, DS_Store</span>
            </div>
            <div class="form-group">
                <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                    <input type="checkbox" name="include_database" id="jobDatabase" value="1" checked style="width:16px; height:16px;">
                    Include database dump
                </label>
            </div>
            <div class="form-group">
                <label class="form-label">Retention (keep last N backups)</label>
                <input type="number" name="retention_count" id="jobRetention" class="form-input" value="10" min="1" max="100" style="width:120px;">
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:18px;">
                <button type="button" onclick="closeModal()" class="btn-outline" style="padding:8px 16px;">Cancel</button>
                <button type="submit" class="btn-primary" id="submitBtn"><i class="fas fa-save"></i> Save Job</button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div id="toast" style="position:fixed; bottom:24px; right:24px; background:#22c55e; color:#fff; padding:12px 20px; border-radius:8px; font-size:13px; z-index:10000; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
<script>setTimeout(() => document.getElementById('toast')?.remove(), 4000);</script>
@endif
@endsection

@push('scripts')
<script>
function toggleCron() {
    document.getElementById('cronGroup').style.display = document.getElementById('jobFrequency').value === 'custom' ? 'block' : 'none';
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Backup Job';
    document.getElementById('jobForm').action = '{{ route("admin.backup.jobs.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('jobName').value = '';
    document.getElementById('jobFrequency').value = 'daily';
    document.getElementById('jobCron').value = '';
    document.getElementById('jobDestination').value = '';
    document.getElementById('jobInclude').value = "app\nconfig\ndatabase\nresources\nroutes\nstorage/app";
    document.getElementById('jobExclude').value = "vendor\nnode_modules\nstorage/logs";
    document.getElementById('jobExcludeExt').value = "zip,tar,gz,rar,7z,mp4,avi,mov,log,tmp,bak";
    document.getElementById('jobDatabase').checked = true;
    document.getElementById('jobRetention').value = 10;
    toggleCron();
    document.getElementById('jobModal').style.display = 'flex';
}

function openEditModal(job) {
    document.getElementById('modalTitle').textContent = 'Edit Backup Job';
    document.getElementById('jobForm').action = '/backup/jobs/' + job.id;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('jobName').value = job.name;
    document.getElementById('jobFrequency').value = job.frequency;
    document.getElementById('jobCron').value = job.cron_expression || '';
    document.getElementById('jobDestination').value = job.destination_path || '';
    document.getElementById('jobInclude').value = (job.include_paths || []).join("\n");
    document.getElementById('jobExclude').value = (job.exclude_paths || []).join("\n");
    document.getElementById('jobDatabase').checked = job.include_database;
    document.getElementById('jobRetention').value = job.retention_count || 10;
    toggleCron();
    document.getElementById('jobModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('jobModal').style.display = 'none';
}
</script>
@endpush
