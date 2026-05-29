@extends('admin.layouts.app')
@section('title', 'Restore Backup')
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
            border: 1px solid var(--border-color,var(--border-color));
            margin-bottom: 20px
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color,var(--border-color))
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--header-text,var(--text-heading))
        }

        .restore-info {
            padding: 20px
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light,var(--border-light));
            font-size: 14px
        }

        .info-row:last-child {
            border: none
        }

        .info-label {
            color: var(--text-muted);
            font-weight: 500
        }

        .info-value {
            color: var(--header-text,var(--text-heading));
            font-weight: 500;
            font-family: monospace
        }

        .warning-box {
            background: var(--c-warning-light);
            border: 1px solid var(--c-warning-border);
            border-radius: var(--card-radius,10px);
            padding: 16px 20px;
            margin-bottom: 20px
        }

        .warning-box h4 {
            color: var(--c-warning);
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .warning-box p,
        .warning-box li {
            color: var(--c-warning);
            font-size: 13px;
            line-height: 1.6
        }

        .warning-box ul {
            margin: 8px 0 0 20px;
            padding: 0
        }

        .danger-box {
            background: var(--c-secondary-light);
            border: 1px solid var(--c-secondary-border);
            border-radius: var(--card-radius,10px);
            padding: 16px 20px;
            margin-bottom: 20px
        }

        .danger-box h4 {
            color: var(--c-danger);
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .danger-box p,
        .danger-box li {
            color: var(--c-secondary);
            font-size: 13px;
            line-height: 1.6
        }

        .danger-box ul {
            margin: 8px 0 0 20px;
            padding: 0
        }

        .success-box {
            background: var(--c-success-light);
            border: 1px solid var(--c-success-border);
            border-radius: var(--card-radius,10px);
            padding: 16px 20px;
            margin-bottom: 20px
        }

        .success-box h4 {
            color: var(--c-success);
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .success-box p,
        .success-box li {
            color: var(--c-success);
            font-size: 13px;
            line-height: 1.6
        }

        .success-box ul {
            margin: 8px 0 0 20px;
            padding: 0
        }

        .btn-primary {
            background: var(--c-primary,var(--c-danger));
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .btn-primary:hover {
            background: var(--c-primary-hover)
        }

        .btn-danger {
            background: var(--c-danger);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .btn-danger:hover {
            background: var(--c-primary,var(--c-danger))
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--input-border);
            color: var(--text-body);
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .confirm-section {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px
        }

        .confirm-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-body);
            cursor: pointer
        }

        .confirm-checkbox input {
            width: 18px;
            height: 18px
        }

        .tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            background: var(--border-light);
            color: var(--text-secondary);
            margin: 2px;
            font-family: monospace
        }

        .phase-list {
            list-style: none;
            padding: 0;
            margin: 16px 0 0
        }

        .phase-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light,var(--border-light));
            font-size: 13px;
            color: var(--text-body)
        }

        .phase-item:last-child {
            border: none
        }

        .phase-num {
            background: var(--c-primary,var(--c-danger));
            color: #fff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0
        }

        .phase-text strong {
            color: var(--header-text,var(--text-heading))
        }
    </style>
@endpush
@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><a href="{{ route('admin.backup.index') }}">Backup</a> &rsaquo; <a
                    href="{{ route('admin.backup.history') }}">History</a> &rsaquo; Restore</div>
            <h1 class="page-title"><i class="fas fa-undo" style="color:var(--c-secondary,var(--c-secondary))"></i> Restore Backup</h1>
        </div>
        <a href="{{ route('admin.backup.history') }}" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    {{-- Enterprise Safety Info --}}
    <div class="success-box">
        <h4><i class="fas fa-shield-alt"></i> Enterprise-Grade Restore</h4>
        <p>This restore uses a <strong>5-phase enterprise process</strong> to ensure safety:</p>
        <ul class="phase-list">
            <li class="phase-item"><span class="phase-num">1</span><span class="phase-text"><strong>Safety Snapshot</strong>
                    — Automatically creates a backup of your current state before restoring, so you can recover if anything
                    goes wrong.</span></li>
            <li class="phase-item"><span class="phase-num">2</span><span class="phase-text"><strong>Maintenance
                        Mode</strong> — Puts the site into maintenance so users don't hit a half-restored state.</span></li>
            <li class="phase-item"><span class="phase-num">3</span><span class="phase-text"><strong>Database First</strong>
                    — Restores the database first for consistency. <strong>Your login session will be
                        preserved.</strong></span></li>
            <li class="phase-item"><span class="phase-num">4</span><span class="phase-text"><strong>File Restore +
                        Cleanup</strong> — Overwrites files AND <strong>deletes any extra files</strong> that weren't in the
                    backup (keeping your state clean).</span></li>
            <li class="phase-item"><span class="phase-num">5</span><span class="phase-text"><strong>Post-Restore</strong> —
                    Clears all caches and brings the site back online.</span></li>
        </ul>
    </div>

    {{-- Warning --}}
    <div class="warning-box">
        <h4><i class="fas fa-exclamation-triangle"></i> What Will Happen</h4>
        <ul>
            <li>All files will be restored to the state they were at
                <strong>{{ $run->created_at->format('d M Y H:i:s') }}</strong>
            </li>
            <li>Files added <strong>after</strong> this backup will be <strong>deleted</strong> (cleaned up)</li>
            @if($run->include_database)
                <li>The database will be <strong>overwritten</strong> with backup data (your session is preserved)</li>
            @endif
            <li>A <strong>safety snapshot</strong> will be created automatically before any changes</li>
            <li>The site will go into <strong>maintenance mode</strong> during restore</li>
        </ul>
    </div>

    {{-- Backup Details --}}
    <div class="section-card">
        <div class="section-header"><span class="section-title">Backup Snapshot Details</span></div>
        <div class="restore-info">
            <div class="info-row"><span class="info-label">Folder</span><span
                    class="info-value">{{ $run->folder_name }}</span></div>
            <div class="info-row"><span class="info-label">Created</span><span
                    class="info-value">{{ $run->created_at->format('d M Y H:i:s') }}</span></div>
            <div class="info-row"><span class="info-label">Job</span><span
                    class="info-value">{{ $run->job ? $run->job->name : 'Manual Backup' }}</span></div>
            <div class="info-row"><span class="info-label">Total Files</span><span
                    class="info-value">{{ number_format($run->total_files) }}</span></div>
            <div class="info-row"><span class="info-label">Total Size</span><span
                    class="info-value">{{ $run->formatted_size }}</span></div>
            <div class="info-row"><span class="info-label">Database Included</span><span
                    class="info-value">{{ $run->include_database ? 'Yes ✓' : 'No' }}</span></div>
            <div class="info-row"><span class="info-label" style="color:var(--c-secondary,var(--c-secondary))">Changelog Content</span><span
                    class="info-value"
                    style="font-family:inherit; color:var(--c-secondary,var(--c-secondary))">{{ $run->description ?? 'No details' }}</span></div>
            @if($metadata)
                <div class="info-row"><span class="info-label">File Manifest</span><span
                        class="info-value">{{ isset($metadata['manifest_count']) ? $metadata['manifest_count'] . ' tracked files ✓' : 'Not available (legacy)' }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="info-label">Include Paths</span>
                <span>@foreach(($run->include_paths ?? []) as $p)<span class="tag">{{ $p }}</span>@endforeach
                    @if(empty($run->include_paths))<span style="color:var(--text-faint);font-size:12px">Default</span>@endif</span>
            </div>
            @if($metadata)
                <div class="info-row"><span class="info-label">Laravel Version</span><span
                        class="info-value">{{ $metadata['laravel_version'] ?? '--' }}</span></div>
                <div class="info-row"><span class="info-label">PHP Version</span><span
                        class="info-value">{{ $metadata['php_version'] ?? '--' }}</span></div>
            @endif
        </div>
    </div>

    {{-- Database Warning --}}
    @if($run->include_database)
        <div class="danger-box">
            <h4><i class="fas fa-database"></i> Database Restore Warning</h4>
            <p>This backup includes a database dump. Restoring it will <strong>DROP and recreate all tables</strong> with the
                backup data. Any data entered after this backup was created will be lost.</p>
            <p style="margin-top:8px"><i class="fas fa-check-circle" style="color:var(--c-success)"></i> <strong>Your login session will
                    be preserved</strong> — you will NOT be logged out during restore.</p>
        </div>
    @endif

    {{-- Confirm & Restore --}}
    <div class="section-card">
        <div class="confirm-section">
            <label class="confirm-checkbox">
                <input type="checkbox" id="confirmCheck" onchange="toggleRestoreBtn()">
                I understand this restore will overwrite existing
                files{{ $run->include_database ? ', overwrite the database,' : '' }} and delete extra files not in this
                backup. A safety snapshot will be created automatically. I want to proceed.
            </label>
            <div style="display:flex;gap:12px">
                <a href="{{ route('admin.backup.history') }}" class="btn-outline"><i class="fas fa-times"></i> Cancel</a>
                <form method="POST" action="{{ route('admin.backup.restore.execute', $run->id) }}" id="restoreForm">
                    @csrf
                    <button type="submit" class="btn-danger" id="restoreBtn" disabled
                        onclick="return confirm('FINAL CONFIRMATION: Are you absolutely sure you want to restore from {{ $run->folder_name }}?')">
                        <i class="fas fa-undo"></i> Restore Now
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function toggleRestoreBtn() {
            document.getElementById('restoreBtn').disabled = !document.getElementById('confirmCheck').checked;
        }
    </script>
@endpush