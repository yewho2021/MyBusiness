@extends('pdf.layout')
@section('title', 'Login Activity Report')

@section('content')
<div class="meta-bar">
    <strong>Total Entries:</strong> {{ $logs->count() }}
    @if($dateFrom) | <strong>From:</strong> {{ $dateFrom }} @endif
    @if($dateTo) | <strong>To:</strong> {{ $dateTo }} @endif
</div>

<table>
    <thead>
        <tr>
            <th>Date & Time</th>
            <th>Admin</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>IP Address</th>
            <th>Location</th>
            <th>Browser</th>
            <th>Device</th>
            <th>Duration</th>
        </tr>
    </thead>
    <tbody>
        @forelse($logs as $log)
        <tr>
            <td style="white-space:nowrap;">{{ $log->login_at?->format('Y-m-d H:i:s') }}</td>
            <td style="font-weight:600;">{{ $log->admin_name ?? '—' }}</td>
            <td>{{ $log->admin_username ?? '—' }}</td>
            <td>{{ $log->role_name ?? '—' }}</td>
            <td>
                @php
                    $statusClass = match(true) {
                        $log->status === 'success' => 'badge-green',
                        str_starts_with($log->status, 'failed') => 'badge-red',
                        $log->status === 'active' => 'badge-blue',
                        default => 'badge-gray',
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ $log->status }}</span>
            </td>
            <td style="font-family:monospace;font-size:9px;">{{ $log->ip_address }}</td>
            <td>{{ $log->ip_city ? $log->ip_city . ', ' . $log->ip_country : '—' }}</td>
            <td>{{ $log->browser ?? '—' }}</td>
            <td>{{ $log->device_type ?? '—' }}</td>
            <td>{{ $log->formatted_duration ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="10" style="text-align:center;padding:20px;color:#94a3b8;">No records found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
