@extends('admin.layouts.app')
@section('title', 'Backup Logs')
@push('styles')
<style>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.page-title{font-size:22px;font-weight:700;color:#1e293b}
.breadcrumb{font-size:13px;color:#64748b;margin-bottom:4px}.breadcrumb a{color:#4f46e5;text-decoration:none}
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;padding:20px}
.info-item label{font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px}
.info-item .val{font-size:14px;font-weight:500;color:#1e293b}
.section-card{background:#fff;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:20px}
.section-header{padding:14px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center}
.section-title{font-size:15px;font-weight:600;color:#1e293b}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-completed{background:#dcfce7;color:#166534}.badge-running{background:#dbeafe;color:#1e40af}.badge-pending{background:#fef3c7;color:#92400e}.badge-failed{background:#fee2e2;color:#991b1b}.badge-restored{background:#f3e8ff;color:#6b21a8}.badge-restoring{background:#e0f2fe;color:#075985}
.progress-bar{background:#e2e8f0;border-radius:10px;height:28px;overflow:hidden;position:relative}
.progress-fill{height:100%;border-radius:10px;transition:width .8s ease;background:linear-gradient(90deg,#4f46e5,#7c3aed);min-width:40px}
.progress-fill.green{background:linear-gradient(90deg,#22c55e,#16a34a)}
.progress-fill.red{background:linear-gradient(90deg,#ef4444,#dc2626)}
.progress-fill.pending{background:linear-gradient(90deg,#f59e0b,#d97706);animation:pulse-bg 1.5s infinite}
@keyframes pulse-bg{0%,100%{opacity:1}50%{opacity:.7}}
.progress-text{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#1e293b;z-index:2}
.progress-text-inner{background:rgba(255,255,255,.85);padding:1px 10px;border-radius:20px}
.log-terminal{background:#0f172a;padding:20px;min-height:300px;max-height:600px;overflow-y:auto;font-family:'Courier New',monospace;font-size:12px;line-height:2;scroll-behavior:smooth}
.log-line{color:#94a3b8;white-space:pre-wrap;word-break:break-all}
.log-line.info{color:#cbd5e1}.log-line.success{color:#4ade80}.log-line.warning{color:#fbbf24}.log-line.error{color:#f87171}
.log-time{color:#475569;margin-right:8px}
.btn-outline{background:transparent;border:1px solid #d1d5db;color:#374151;padding:6px 14px;border-radius:6px;font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-outline:hover{background:#f3f4f6;color:#374151}
.error-box{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;color:#991b1b;font-size:13px;margin:0 20px 20px}
.status-banner{padding:12px 20px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:8px;border-bottom:1px solid #e2e8f0}
.status-banner.running{background:#eff6ff;color:#1e40af}
.status-banner.pending{background:#fffbeb;color:#92400e}
.status-banner.completed{background:#f0fdf4;color:#166534}
.status-banner.failed{background:#fef2f2;color:#991b1b}
.spinner{display:inline-block;width:14px;height:14px;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.log-footer{padding:10px 20px;background:#1e293b;border-radius:0 0 10px 10px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#64748b}
.duration-live{font-family:'Courier New',monospace;font-size:16px;font-weight:700;color:#4f46e5;letter-spacing:1px}
</style>
@endpush
@section('content')
<div class="page-header">
<div>
<div class="breadcrumb"><a href="{{ route('admin.backup.index') }}">Backup</a> &rsaquo; <a href="{{ route('admin.backup.history') }}">History</a> &rsaquo; Logs</div>
<h1 class="page-title" id="pageTitle">{{ $run->folder_name ?? 'Backup #' . $run->id }}</h1>
</div>
<a href="{{ route('admin.backup.history') }}" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
@if(in_array($run->status, ['completed','failed','restored']))
<form method="POST" action="{{ route('admin.backup.logs.delete', $run->id) }}" style="display:inline;margin-left:8px" onsubmit="return confirm('Clear all log entries for this backup?')">@csrf @method('DELETE')
<button class="btn-outline" style="color:#ef4444;border-color:#fecaca"><i class="fas fa-trash"></i> Clear Logs</button>
</form>
@endif
</div>

<div class="section-card">
<div id="statusBanner" class="status-banner {{ $run->status }}" style="{{ in_array($run->status, ['pending','running','restoring']) ? '' : 'display:none' }}">
<span class="spinner"></span>
<span id="statusText">
@if($run->status === 'pending') Initializing backup...
@elseif($run->status === 'running') Backup in progress...
@elseif($run->status === 'restoring') Restore in progress...
@endif
</span>
</div>
<div class="info-grid">
<div class="info-item"><label>Status</label><span class="badge badge-{{ $run->status }}" id="badgeStatus">{{ ucfirst($run->status) }}</span></div>
<div class="info-item"><label>Job</label><span class="val">{{ $run->job ? $run->job->name : 'Manual' }}</span></div>
<div class="info-item"><label>Files</label><span class="val" id="infoFiles">{{ number_format($run->processed_files) }}/{{ number_format($run->total_files) }}</span></div>
<div class="info-item"><label>Size</label><span class="val" id="infoSize">{{ $run->formatted_size }}</span></div>
<div class="info-item"><label>Started</label><span class="val" id="infoStarted">{{ $run->started_at ? $run->started_at->format('d M Y H:i:s') : '--' }}</span></div>
<div class="info-item"><label>Duration</label><span class="val duration-live" id="infoDuration">{{ $run->duration }}</span></div>
</div>
<div style="padding:0 20px 20px">
<div class="progress-bar">
<div class="progress-fill {{ $run->status==='completed'||$run->status==='restored'?'green':($run->status==='failed'?'red':($run->status==='pending'?'pending':'')) }}" id="progressBar" style="width:{{ max($run->progress, 2) }}%"></div>
<div class="progress-text"><span class="progress-text-inner" id="progressText">{{ $run->progress }}%</span></div>
</div>
</div>
<div id="errorBox" class="error-box" style="{{ $run->error_message ? '' : 'display:none' }}">
<i class="fas fa-exclamation-triangle"></i> <span id="errorMsg">{{ $run->error_message }}</span>
</div>
</div>

<div class="section-card" style="overflow:hidden">
<div class="section-header">
<span class="section-title"><i class="fas fa-terminal" style="color:#64748b"></i> Log Output</span>
<span style="font-size:12px;color:#64748b" id="logCount">{{ $logs->total() }} entries</span>
</div>
<div class="log-terminal" id="logTerminal">
@forelse($logs as $log)
<div class="log-line {{ $log->level }}"><span class="log-time">[{{ $log->logged_at->format('H:i:s') }}]</span>{{ $log->message }}</div>
@empty
<div class="log-line info" id="waitingMsg"><span class="spinner" style="vertical-align:middle;margin-right:8px"></span>Waiting for backup to start...</div>
@endforelse
</div>
<div class="log-footer">
<span id="logStatus">{{ in_array($run->status, ['pending','running','restoring']) ? 'Live — auto-refreshing...' : 'Backup ' . $run->status }}</span>
<span id="autoScroll" style="cursor:pointer" onclick="toggleAutoScroll()"><i class="fas fa-arrow-down"></i> Auto-scroll: ON</span>
</div>
@if($logs->lastPage() > 1 && in_array($run->status, ['completed','failed','restored']))
<div style="padding:10px 20px;background:#1e293b;border-top:1px solid #334155;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;border-radius:0 0 10px 10px">
<span style="font-size:12px;color:#64748b">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }} ({{ $logs->total() }} entries)</span>
<div style="display:flex;gap:4px;align-items:center">
@if($logs->currentPage() > 1)
<a href="{{ $logs->url(1) }}" style="color:#818cf8;text-decoration:none;padding:4px 8px;border:1px solid #334155;border-radius:4px;font-size:12px">First</a>
<a href="{{ $logs->previousPageUrl() }}" style="color:#818cf8;text-decoration:none;padding:4px 8px;border:1px solid #334155;border-radius:4px;font-size:12px">&laquo;</a>
@endif
@php $from=max(1,$logs->currentPage()-3);$to=min($logs->lastPage(),$logs->currentPage()+3); @endphp
@for($i=$from;$i<=$to;$i++)
@if($i==$logs->currentPage())<span style="background:#4f46e5;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px">{{ $i }}</span>
@else<a href="{{ $logs->url($i) }}" style="color:#818cf8;text-decoration:none;padding:4px 8px;border:1px solid #334155;border-radius:4px;font-size:12px">{{ $i }}</a>@endif
@endfor
@if($logs->hasMorePages())
<a href="{{ $logs->nextPageUrl() }}" style="color:#818cf8;text-decoration:none;padding:4px 8px;border:1px solid #334155;border-radius:4px;font-size:12px">&raquo;</a>
<a href="{{ $logs->url($logs->lastPage()) }}" style="color:#818cf8;text-decoration:none;padding:4px 8px;border:1px solid #334155;border-radius:4px;font-size:12px">Last</a>
@endif
</div>
</div>
@endif
</div>
@endsection

@push('scripts')
<script>
const runId = {{ $run->id }};
const initialStatus = '{{ $run->status }}';
const csrfToken = '{{ csrf_token() }}';
const serverStartedAt = {{ $run->started_at ? $run->started_at->timestamp : 'null' }};

let autoScroll = true;
let lastLogId = 0;
let pollTimer = null;
let durationTimer = null;
let backupTriggered = false;
let startedTimestamp = serverStartedAt;

const terminal = document.getElementById('logTerminal');

function toggleAutoScroll() {
    autoScroll = !autoScroll;
    document.getElementById('autoScroll').innerHTML = '<i class="fas fa-arrow-down"></i> Auto-scroll: ' + (autoScroll ? 'ON' : 'OFF');
}

function appendLog(level, message, time) {
    const waitMsg = document.getElementById('waitingMsg');
    if (waitMsg) waitMsg.remove();
    const line = document.createElement('div');
    line.className = 'log-line ' + level;
    line.innerHTML = '<span class="log-time">[' + time + ']</span>' + escapeHtml(message);
    terminal.appendChild(line);
    if (autoScroll) terminal.scrollTop = terminal.scrollHeight;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDuration(seconds) {
    seconds = Math.max(0, Math.floor(seconds));
    if (seconds < 3600) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function startDurationTimer() {
    if (durationTimer) clearInterval(durationTimer);
    durationTimer = setInterval(() => {
        if (!startedTimestamp) return;
        const now = Math.floor(Date.now() / 1000);
        const elapsed = now - startedTimestamp;
        document.getElementById('infoDuration').textContent = formatDuration(elapsed);
    }, 1000);
}

function stopDurationTimer() {
    if (durationTimer) clearInterval(durationTimer);
}

function numberFormat(n) { return (n || 0).toLocaleString(); }
function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function updateUI(data) {
    // Progress bar
    const bar = document.getElementById('progressBar');
    const pct = Math.max(data.progress, 2);
    bar.style.width = pct + '%';
    document.getElementById('progressText').textContent = data.progress + '%';

    // Info fields
    document.getElementById('infoFiles').textContent = numberFormat(data.processed_files) + '/' + numberFormat(data.total_files);
    document.getElementById('infoSize').textContent = data.total_size;

    // Started at
    if (data.started_at) {
        document.getElementById('infoStarted').textContent = data.started_at;
    }
    if (data.started_ts && !startedTimestamp) {
        startedTimestamp = data.started_ts;
        startDurationTimer();
    }

    // Folder name in title
    if (data.folder_name) {
        document.getElementById('pageTitle').textContent = data.folder_name;
    }

    // Status badge
    const badge = document.getElementById('badgeStatus');
    badge.textContent = capitalize(data.status);
    badge.className = 'badge badge-' + data.status;

    // Banner
    const banner = document.getElementById('statusBanner');
    if (['running', 'restoring'].includes(data.status)) {
        banner.style.display = 'flex';
        banner.className = 'status-banner ' + data.status;
        document.getElementById('statusText').textContent = data.status === 'running' ? 'Backup in progress...' : 'Restore in progress...';
        bar.className = 'progress-fill';
    } else if (data.status === 'completed' || data.status === 'restored') {
        stopDurationTimer();
        banner.style.display = 'flex';
        banner.className = 'status-banner completed';
        banner.innerHTML = '<i class="fas fa-check-circle"></i> ' + (data.status === 'restored' ? 'Restore completed!' : 'Backup completed!');
        bar.className = 'progress-fill green';
        document.getElementById('logStatus').textContent = 'Backup ' + data.status;
    } else if (data.status === 'failed') {
        stopDurationTimer();
        banner.style.display = 'flex';
        banner.className = 'status-banner failed';
        banner.innerHTML = '<i class="fas fa-times-circle"></i> Backup failed';
        bar.className = 'progress-fill red';
        document.getElementById('logStatus').textContent = 'Backup failed';
        if (data.error_message) {
            document.getElementById('errorBox').style.display = 'block';
            document.getElementById('errorMsg').textContent = data.error_message;
        }
    }

    // Append new logs
    if (data.logs && data.logs.length > 0) {
        data.logs.forEach(log => appendLog(log.level, log.message, log.time));
        lastLogId = data.logs[data.logs.length - 1].id;
    }

    document.getElementById('logCount').textContent = document.querySelectorAll('.log-line').length + ' entries';
}

function pollProgress() {
    fetch('/backup/progress/' + runId + '?after_id=' + lastLogId)
        .then(r => r.json())
        .then(data => {
            updateUI(data);
            if (['completed', 'failed', 'restored'].includes(data.status)) {
                clearInterval(pollTimer);
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(() => {});
}

function triggerBackup() {
    if (backupTriggered) return;
    backupTriggered = true;
    fetch('/backup/execute/' + runId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    }).then(r => r.json()).catch(() => {});
}

function triggerRestore() {
    fetch('/backup/restore-ajax/' + runId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    }).then(r => r.json()).catch(() => {});
}

// ── Init ──
if (initialStatus === 'pending') {
    triggerBackup();
    pollTimer = setInterval(pollProgress, 1500);
    startDurationTimer();
} else if (initialStatus === 'running' || initialStatus === 'restoring') {
    pollTimer = setInterval(pollProgress, 1500);
    if (startedTimestamp) startDurationTimer();
} else {
    terminal.scrollTop = terminal.scrollHeight;
}
</script>
@endpush
