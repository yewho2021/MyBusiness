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
            color: var(--header-text,var(--text-heading))
        }

        .breadcrumb {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 4px
        }

        .breadcrumb a {
            color: var(--c-secondary,var(--c-secondary));
            text-decoration: none
        }

        .section-card {
            background: var(--card-bg,#fff);
            border-radius: var(--card-radius,10px);
            border: 1px solid var(--border-color,var(--border-color))
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
            background: var(--c-success);
            color: #fff
        }

        .btn-danger {
            background: var(--c-danger);
            color: #fff
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--input-border);
            color: var(--text-body)
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
            color: var(--text-muted);
            text-transform: uppercase;
            background: var(--table-header-bg,var(--table-header-bg));
            border-bottom: 1px solid var(--border-color,var(--border-color))
        }

        td {
            padding: 12px 16px;
            font-size: 13px;
            color: var(--text-body);
            border-bottom: 1px solid var(--border-light,var(--border-light));
            vertical-align: middle
        }

        tr:hover td {
            background: var(--hover-bg)
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600
        }

        .badge-completed {
            background: var(--c-success-light);
            color: var(--c-success)
        }

        .badge-running {
            background: var(--c-secondary-light);
            color: var(--c-secondary);
            animation: pulse 1.5s infinite
        }

        .badge-pending {
            background: var(--c-warning-light);
            color: var(--c-warning)
        }

        .badge-failed {
            background: var(--c-secondary-light);
            color: var(--c-danger)
        }

        .badge-restored {
            background: var(--c-purple-light);
            color: var(--c-purple)
        }

        .badge-restoring {
            background: var(--c-info-light);
            color: var(--c-info);
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
            background: var(--border-color);
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
            background: var(--c-success)
        }

        .fill-blue {
            background: var(--c-primary,var(--c-danger))
        }

        .fill-red {
            background: var(--c-danger)
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-faint)
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
                            <td style="color:var(--text-faint)">{{ $run->id }}</td>
                            <td style="font-weight:500;font-family:monospace;font-size:12px">
                                <div style="display:flex; flex-direction:column; gap:2px">
                                    <span>{{ $run->folder_name ?? '...' }}</span>
                                    <div style="display:flex; gap:4px">
                                        @if(str_contains($run->folder_name, 'safety_'))
                                            <span
                                                style="font-size:9px; background:var(--c-warning-light); color:var(--c-warning); padding:0px 4px; border-radius:3px; font-weight:600">SAFETY</span>
                                        @endif
                                        @if(file_exists($run->getBackupPath() . '/file_manifest.json'))
                                            <span
                                                style="font-size:9px; background:var(--c-secondary-light); color:var(--c-secondary); padding:0px 4px; border-radius:3px; font-weight:600">ENT</span>
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
                                <span style="font-size:11px;color:var(--text-faint)" id="pt-{{ $run->id }}">{{ $run->progress }}%</span>
                            </td>
                            <td id="fc-{{ $run->id }}">{{ $run->processed_files }}/{{ $run->total_files }}</td>
                            <td style="max-width:250px; font-size:11px; color:var(--text-muted); line-height:1.4">
                                {{ $run->description ?? 'No description' }}
                            </td>
                            <td id="sz-{{ $run->id }}">
                                {{ $run->formatted_size }}
                                @if($run->zip_size)
                                    <div style="font-size:10px;color:var(--c-success,var(--c-success));margin-top:2px;">
                                        <i class="fas fa-file-archive"></i> {{ $run->formatted_zip_size }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $run->duration }}</td>
                            <td style="white-space:nowrap">{{ $run->created_at->format('d M Y H:i') }}</td>
                            <td style="white-space:nowrap">
                                <a href="{{ route('admin.backup.logs', $run->id) }}" class="btn-sm btn-outline" title="Logs"><i
                                        class="fas fa-file-alt"></i></a>
                                @if($run->status === 'completed' && $run->hasZip())
                                    <a href="{{ route('admin.backup.download', $run->id) }}" class="btn-sm btn-outline" title="Download ZIP" style="color:var(--c-secondary,var(--c-secondary));border-color:var(--c-secondary,var(--c-secondary));"><i
                                            class="fas fa-download"></i></a>
                                @endif
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
            style="position:fixed;bottom:24px;right:24px;background:var(--c-success);color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;z-index:10000">
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