@extends('admin.layouts.app')
@section('title', 'Backup History')
@push('styles')
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b
        }

        .breadcrumb {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 4px
        }

        .breadcrumb a {
            color: #4f46e5;
            text-decoration: none
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e2e8f0
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: none
        }

        .btn-success {
            background: #22c55e;
            color: #fff
        }

        .btn-danger {
            background: #ef4444;
            color: #fff
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #374151
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th {
            text-align: left;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0
        }

        td {
            padding: 12px 16px;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle
        }

        tr:hover td {
            background: #fafbfc
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600
        }

        .badge-completed {
            background: #dcfce7;
            color: #166534
        }

        .badge-running {
            background: #dbeafe;
            color: #1e40af;
            animation: pulse 1.5s infinite
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e
        }

        .badge-failed {
            background: #fee2e2;
            color: #991b1b
        }

        .badge-restored {
            background: #f3e8ff;
            color: #6b21a8
        }

        .badge-restoring {
            background: #e0f2fe;
            color: #075985;
            animation: pulse 1.5s infinite
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .6
            }
        }

        .progress-wrap {
            background: #e2e8f0;
            border-radius: 8px;
            height: 8px;
            overflow: hidden;
            min-width: 100px
        }

        .progress-fill {
            height: 100%;
            border-radius: 8px;
            transition: width .5s
        }

        .fill-green {
            background: #22c55e
        }

        .fill-blue {
            background: #4f46e5
        }

        .fill-red {
            background: #ef4444
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block
        }
    </style>
@endpush
@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><a href="{{ route('admin.backup.index') }}">Backup</a> &rsaquo; History</div>
            <h1 class="page-title">Backup History</h1>
        </div>
    </div>
    <div class="section-card">
        @if($runs->isEmpty())
            <div class="empty-state"><i class="fas fa-history"></i>
                <p>No backup history yet.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Folder</th>
                        <th>Job</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Files</th>
                        <th>Changelog Content</th>
                        <th>Size</th>
                        <th>Duration</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($runs as $run)
                        <tr>
                            <td style="color:#94a3b8">{{ $run->id }}</td>
                            <td style="font-weight:500;font-family:monospace;font-size:12px">
                                <div style="display:flex; flex-direction:column; gap:2px">
                                    <span>{{ $run->folder_name ?? '...' }}</span>
                                    <div style="display:flex; gap:4px">
                                        @if(str_contains($run->folder_name, 'safety_'))
                                            <span
                                                style="font-size:9px; background:#fef3c7; color:#92400e; padding:0px 4px; border-radius:3px; font-weight:600">SAFETY</span>
                                        @endif
                                        @if(file_exists($run->getBackupPath() . '/file_manifest.json'))
                                            <span
                                                style="font-size:9px; background:#eff6ff; color:#1e40af; padding:0px 4px; border-radius:3px; font-weight:600">ENT</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $run->job ? $run->job->name : 'Manual' }}</td>
                            <td><span class="badge badge-{{ $run->status }}"
                                    id="st-{{ $run->id }}">{{ ucfirst($run->status) }}</span></td>
                            <td style="min-width:130px">
                                <div class="progress-wrap">
                                    <div class="progress-fill {{ $run->status === 'completed' || $run->status === 'restored' ? 'fill-green' : ($run->status === 'failed' ? 'fill-red' : 'fill-blue') }}"
                                        id="pb-{{ $run->id }}" style="width:{{ $run->progress }}%"></div>
                                </div>
                                <span style="font-size:11px;color:#94a3b8" id="pt-{{ $run->id }}">{{ $run->progress }}%</span>
                            </td>
                            <td id="fc-{{ $run->id }}">{{ $run->processed_files }}/{{ $run->total_files }}</td>
                            <td style="max-width:250px; font-size:11px; color:#64748b; line-height:1.4">
                                {{ $run->description ?? 'No description' }}
                            </td>
                            <td id="sz-{{ $run->id }}">{{ $run->formatted_size }}</td>
                            <td>{{ $run->duration }}</td>
                            <td style="white-space:nowrap">{{ $run->created_at->format('d M Y H:i') }}</td>
                            <td style="white-space:nowrap">
                                <a href="{{ route('admin.backup.logs', $run->id) }}" class="btn-sm btn-outline" title="Logs"><i
                                        class="fas fa-file-alt"></i></a>
                                @if($run->status === 'completed')
                                    <a href="{{ route('admin.backup.restore.confirm', $run->id) }}" class="btn-sm btn-success"
                                        title="Restore"><i class="fas fa-undo"></i></a>
                                @endif
                                @if(in_array($run->status, ['completed', 'failed', 'restored']))
                                    <form method="POST" action="{{ route('admin.backup.delete', $run->id) }}" style="display:inline"
                                        onsubmit="return confirm('Delete this backup?')">@csrf @method('DELETE')
                                        <button type="submit" class="btn-sm btn-danger" title="Delete"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($runs->hasPages())
            <div style="padding:16px;display:flex;justify-content:center">{{ $runs->links() }}</div>@endif
        @endif
    </div>
    @if(session('success'))
        <div id="toast"
            style="position:fixed;bottom:24px;right:24px;background:#22c55e;color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;z-index:10000">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    <script>setTimeout(() => document.getElementById('toast')?.remove(), 4000)</script>@endif
@endsection
@push('scripts')
    <script>
        setInterval(() => {
            document.querySelectorAll('.badge-running,.badge-pending,.badge-restoring').forEach(el => {
                const id = el.id.replace('st-', '');
                fetch('/backup/progress/' + id).then(r => r.json()).then(d => {
                    const pb = document.getElementById('pb-' + id); if (pb) pb.style.width = d.progress + '%';
                    const pt = document.getElementById('pt-' + id); if (pt) pt.textContent = d.progress + '%';
                    const fc = document.getElementById('fc-' + id); if (fc) fc.textContent = d.processed_files + '/' + d.total_files;
                    const sz = document.getElementById('sz-' + id); if (sz) sz.textContent = d.total_size;
                    if (['completed', 'failed', 'restored'].includes(d.status)) location.reload();
                }).catch(() => { });
            });
        }, 3000);
    </script>
@endpush