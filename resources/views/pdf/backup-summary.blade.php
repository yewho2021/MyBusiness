@extends('pdf.layout')
@section('title', 'Backup Summary Report')

@section('content')
<div class="meta-bar">
    <strong>Total Runs:</strong> {{ $runs->count() }} |
    <strong>Completed:</strong> {{ $runs->where('status', 'completed')->count() }} |
    <strong>Failed:</strong> {{ $runs->where('status', 'failed')->count() }}
</div>

<table>
    <thead>
        <tr>
            <th style="width:30px;">#</th>
            <th>Folder</th>
            <th>Status</th>
            <th>Files</th>
            <th>Size</th>
            <th>Database</th>
            <th>Started</th>
            <th>Completed</th>
            <th>Duration</th>
        </tr>
    </thead>
    <tbody>
        @forelse($runs as $run)
        @php
            $statusClass = match($run->status) {
                'completed' => 'badge-green',
                'failed'    => 'badge-red',
                'running'   => 'badge-blue',
                default     => 'badge-gray',
            };
            $sz = $run->total_size;
            if ($sz >= 1048576) $sizeStr = round($sz / 1048576, 1) . ' MB';
            elseif ($sz >= 1024) $sizeStr = round($sz / 1024, 1) . ' KB';
            else $sizeStr = $sz . ' B';

            $duration = '—';
            if ($run->started_at && $run->completed_at) {
                $diff = $run->started_at->diff($run->completed_at);
                if ($diff->h > 0) $duration = $diff->h . 'h ' . $diff->i . 'm';
                elseif ($diff->i > 0) $duration = $diff->i . 'm ' . $diff->s . 's';
                else $duration = $diff->s . 's';
            }
        @endphp
        <tr>
            <td>{{ $run->id }}</td>
            <td style="font-family:monospace;font-size:9px;">{{ $run->folder_name ?? '—' }}</td>
            <td><span class="badge {{ $statusClass }}">{{ $run->status }}</span></td>
            <td>{{ number_format($run->processed_files) }}/{{ number_format($run->total_files) }}</td>
            <td>{{ $sizeStr }}</td>
            <td>{!! $run->include_database ? '<span class="badge badge-green">Yes</span>' : '<span class="badge badge-gray">No</span>' !!}</td>
            <td style="white-space:nowrap;">{{ $run->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
            <td style="white-space:nowrap;">{{ $run->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
            <td>{{ $duration }}</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center;padding:20px;color:#94a3b8;">No backup runs found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
