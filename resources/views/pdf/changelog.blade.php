@extends('pdf.layout')
@section('title', 'Changelog Report')

@section('content')
<div class="meta-bar">
    <strong>Total Entries:</strong> {{ $entries->count() }} |
    <strong>Latest Version:</strong> {{ $entries->first()->version ?? '—' }}
</div>

<table>
    <thead>
        <tr>
            <th style="width:80px;">Version</th>
            <th style="width:200px;">Title</th>
            <th>Details</th>
            <th style="width:90px;">Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($entries as $entry)
        <tr>
            <td><span class="badge badge-blue">{{ $entry->version }}</span></td>
            <td style="font-weight:600;">{{ $entry->title }}</td>
            <td style="font-size:9px;line-height:1.5;">{{ $entry->details }}</td>
            <td style="white-space:nowrap;">{{ $entry->created_at?->format('Y-m-d') ?? $entry->created_at }}</td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center;padding:20px;color:#94a3b8;">No entries found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
