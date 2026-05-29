@extends('admin.layouts.app')

@section('title', 'Backup Dashboard')

@push('styles')
    <style>
        .backup-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: box-shadow 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-icon.blue {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-icon.green {
            background: #f0fdf4;
            color: #22c55e;
        }

        .stat-icon.purple {
            background: #faf5ff;
            color: #a855f7;
        }

        .stat-icon.amber {
            background: #fffbeb;
            color: #f59e0b;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary:hover {
            background: #4338ca;
            color: #fff;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
        }

        .btn-success {
            background: #22c55e;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-danger {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-outline:hover {
            background: #f3f4f6;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 12px 16px;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f1f5f9;
        }

        tr:hover td {
            background: #fafbfc;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-completed {
            background: #dcfce7;
            color: #166534;
        }

        .badge-running {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-restored {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .badge-restoring {
            background: #e0f2fe;
            color: #075985;
        }

        .quick-action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            padding: 20px;
        }

        .quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px;
            border: 2px dashed #e2e8f0;
            border-radius: 10px;
            text-decoration: none;
            color: #475569;
            transition: all 0.2s;
            cursor: pointer;
        }

        .quick-action:hover {
            border-color: #4f46e5;
            background: #f5f3ff;
            color: #4f46e5;
        }

        .quick-action i {
            font-size: 24px;
        }

        .quick-action span {
            font-size: 13px;
            font-weight: 500;
        }

        .progress-bar-wrap {
            background: #e2e8f0;
            border-radius: 8px;
            height: 8px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: #4f46e5;
            border-radius: 8px;
            transition: width 0.5s;
        }

        .progress-bar-fill.green {
            background: #22c55e;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Backup Dashboard</h1>
            <p style="font-size: 13px; color: #64748b;">Manage your system backups and restore points</p>
        </div>
        <a href="{{ route('admin.backup.jobs') }}" class="btn-primary">
            <i class="fas fa-plus"></i> New Backup Job
        </a>
    </div>

    {{-- Stats --}}
    <div class="backup-stats">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-briefcase"></i></div>
            <div>
                <div class="stat-value">{{ $stats['total_jobs'] }}</div>
                <div class="stat-label">Total Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-play-circle"></i></div>
            <div>
                <div class="stat-value">{{ $stats['active_jobs'] }}</div>
                <div class="stat-label">Active Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-archive"></i></div>
            <div>
                <div class="stat-value">{{ $stats['total_backups'] }}</div>
                <div class="stat-label">Completed Backups</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-hdd"></i></div>
            <div>
                <div class="stat-value">
                    @php
                        $bytes = $stats['total_size'] ?? 0;
                        if ($bytes >= 1073741824) {
                            echo number_format($bytes / 1073741824, 1) . ' GB';
                        } elseif ($bytes >= 1048576) {
                            echo number_format($bytes / 1048576, 1) . ' MB';
                        } elseif ($bytes >= 1024) {
                            echo number_format($bytes / 1024, 1) . ' KB';
                        } else {
                            echo $bytes . ' B';
                        }
                    @endphp
                </div>
                <div class="stat-label">Total Storage Used</div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">Quick Actions</span>
        </div>
        <div class="quick-action-grid">
            <a href="{{ route('admin.backup.jobs') }}" class="quick-action">
                <i class="fas fa-cog"></i>
                <span>Manage Jobs</span>
            </a>
            <a href="{{ route('admin.backup.history') }}" class="quick-action">
                <i class="fas fa-history"></i>
                <span>Backup History</span>
            </a>
            <div class="quick-action" onclick="document.getElementById('manualBackupModal').style.display='flex'">
                <i class="fas fa-bolt"></i>
                <span>Manual Backup</span>
            </div>
        </div>
    </div>

    {{-- Recent Backup Runs --}}
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">Recent Backup Runs</span>
            <a href="{{ route('admin.backup.history') }}" class="btn-outline btn-sm">View All</a>
        </div>
        @if($recentRuns->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No backups yet. Create a job or run a manual backup.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Backup</th>
                        <th>Job</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Size</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentRuns->take(10) as $run)
                        <tr id="run-row-{{ $run->id }}">
                            <td style="font-weight: 500;">
                                <div style="display:flex; flex-direction:column; gap:4px">
                                    <span>{{ $run->folder_name ?? 'Pending...' }}</span>
                                    <div style="display:flex; gap:4px">
                                        @if(str_contains($run->folder_name, 'safety_'))
                                            <span
                                                style="font-size:10px; background:#fef3c7; color:#92400e; padding:1px 6px; border-radius:4px; font-weight:600">SAFETY</span>
                                        @endif
                                        @if(file_exists($run->getBackupPath() . '/file_manifest.json'))
                                            <span
                                                style="font-size:10px; background:#eff6ff; color:#1e40af; padding:1px 6px; border-radius:4px; font-weight:600">ENTERPRISE</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $run->job ? $run->job->name : 'Manual' }}</td>
                            <td>
                                <span class="badge badge-{{ $run->status }}">
                                    {{ ucfirst($run->status) }}
                                </span>
                            </td>
                            <td style="min-width: 120px;">
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill {{ $run->status === 'completed' ? 'green' : ($run->status === 'failed' ? 'red' : '') }}"
                                        style="width: {{ $run->progress }}%"></div>
                                </div>
                                <span style="font-size: 11px; color: #94a3b8;">{{ $run->processed_files }}/{{ $run->total_files }}
                                    files</span>
                            </td>
                            <td>{{ $run->formatted_size }}</td>
                            <td style="white-space: nowrap;">{{ $run->created_at->format('d M Y H:i') }}</td>
                            <td style="white-space: nowrap;">
                                <div style="display:flex; gap:4px">
                                    <a href="{{ route('admin.backup.logs', $run->id) }}" class="btn-outline btn-sm"
                                        title="View Logs"><i class="fas fa-file-alt"></i></a>
                                    @if($run->status === 'completed')
                                        <a href="{{ route('admin.backup.restore.confirm', $run->id) }}" class="btn-success btn-sm"
                                            title="Restore"><i class="fas fa-undo"></i></a>
                                    @endif
                                    @if(in_array($run->status, ['completed', 'failed', 'restored']))
                                        <form method="POST" action="{{ route('admin.backup.delete', $run->id) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this backup and all its files?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm" title="Delete"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Manual Backup Modal --}}
    <div id="manualBackupModal"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div
            style="background:#fff; border-radius:12px; padding:24px; width:90%; max-width:500px; max-height:90vh; overflow-y:auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:18px; font-weight:600;">Manual Backup</h3>
                <button onclick="document.getElementById('manualBackupModal').style.display='none'"
                    style="background:none; border:none; font-size:20px; cursor:pointer; color:#94a3b8;">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.backup.run.manual') }}">
                @csrf
                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#374151;">Include
                        Paths <span style="color:#94a3b8;">(one per line)</span></label>
                    <textarea name="include_paths" rows="4"
                        style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; font-family:monospace;"
                        placeholder="app&#10;config&#10;database&#10;resources&#10;routes&#10;storage/app">app
    config
    database
    resources
    routes
    storage/app</textarea>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#374151;">Exclude
                        Paths <span style="color:#94a3b8;">(one per line)</span></label>
                    <textarea name="exclude_paths" rows="3"
                        style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; font-family:monospace;"
                        placeholder="vendor&#10;node_modules&#10;storage/logs">vendor
    node_modules
    storage/logs</textarea>
                </div>
                <div style="margin-bottom: 18px;">
                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" name="include_database" value="1" checked style="width:16px; height:16px;">
                        Include database dump
                    </label>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('manualBackupModal').style.display='none'"
                        class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fas fa-play"></i> Start Backup</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div id="toast"
            style="position:fixed; bottom:24px; right:24px; background:#22c55e; color:#fff; padding:12px 20px; border-radius:8px; font-size:13px; z-index:10000; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        <script>setTimeout(() => document.getElementById('toast')?.remove(), 4000);</script>
    @endif

    @if(session('error'))
        <div id="toast"
            style="position:fixed; bottom:24px; right:24px; background:#ef4444; color:#fff; padding:12px 20px; border-radius:8px; font-size:13px; z-index:10000; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        <script>setTimeout(() => document.getElementById('toast')?.remove(), 4000);</script>
    @endif
@endsection

@push('scripts')
    <script>
        // Auto-refresh running backups
        setInterval(() => {
            document.querySelectorAll('.badge-running, .badge-pending').forEach(badge => {
                const row = badge.closest('tr');
                if (row) {
                    const runId = row.id.replace('run-row-', '');
                    if (runId) {
                        fetch(`/backup/progress/${runId}`)
                            .then(r => r.json())
                            .then(data => {
                                if (data.status === 'completed' || data.status === 'failed') {
                                    location.reload();
                                }
                            })
                            .catch(() => { });
                    }
                }
            });
        }, 5000);
    </script>
@endpush