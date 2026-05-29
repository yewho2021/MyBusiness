<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Log Report</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: var(--text-heading); margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid var(--c-danger); padding-bottom: 15px; }
        .header h1 { font-size: 22px; font-weight: 700; color: var(--code-bg); margin: 0 0 6px 0; }
        .header p { font-size: 11px; color: var(--text-muted); margin: 0; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 10px; color: var(--text-muted); }
        .meta-row span { display: inline-block; margin-right: 20px; }
        .filters-box { background: var(--table-header-bg); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px 14px; margin-bottom: 15px; font-size: 10px; color: var(--text-secondary); }
        .filters-box strong { color: var(--code-bg); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: var(--c-danger); color: #fff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; padding: 8px 10px; text-align: left; }
        td { padding: 7px 10px; font-size: 10px; border-bottom: 1px solid var(--border-color); vertical-align: top; }
        tr:nth-child(even) td { background: var(--table-header-bg); }
        .event-created { color: var(--c-success); font-weight: 600; }
        .event-updated { color: var(--c-secondary); font-weight: 600; }
        .event-deleted { color: var(--c-primary-hover); font-weight: 600; }
        .model-name { font-family: monospace; font-size: 9px; color: var(--text-secondary); }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: var(--text-faint); border-top: 1px solid var(--border-color); padding-top: 10px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Activity Log Report</h1>
        <p>Generated: {{ $generated }} | By: {{ $admin?->name ?? 'System' }}</p>
    </div>

    @if(array_filter($filters))
    <div class="filters-box">
        <strong>Filters applied:</strong>
        @if(!empty($filters['subject_type']))
            Model: {{ class_basename($filters['subject_type']) }} &nbsp;|&nbsp;
        @endif
        @if(!empty($filters['event']))
            Event: {{ ucfirst($filters['event']) }} &nbsp;|&nbsp;
        @endif
        @if(!empty($filters['causer_id']))
            Admin ID: {{ $filters['causer_id'] }} &nbsp;|&nbsp;
        @endif
        @if(!empty($filters['date_from']))
            From: {{ $filters['date_from'] }} &nbsp;|&nbsp;
        @endif
        @if(!empty($filters['date_to']))
            To: {{ $filters['date_to'] }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 110px;">Date & Time</th>
                <th style="width: 80px;">Admin</th>
                <th style="width: 60px;">Event</th>
                <th style="width: 80px;">Model</th>
                <th style="width: 30px;">ID</th>
                <th>Changes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                <td>
                    @if($log->causer_id)
                        @php $ca = \App\Models\Admin::find($log->causer_id); @endphp
                        {{ $ca?->name ?? 'ID:'.$log->causer_id }}
                    @else
                        System
                    @endif
                </td>
                <td>
                    <span class="event-{{ $log->event ?? 'unknown' }}">{{ ucfirst($log->event ?? $log->description) }}</span>
                </td>
                <td><span class="model-name">{{ $log->subject_type ? class_basename($log->subject_type) : '—' }}</span></td>
                <td>{{ $log->subject_id ?? '—' }}</td>
                <td>
                    @php
                        $props = $log->properties ? $log->properties->toArray() : [];
                        $old = $props['old'] ?? [];
                        $attrs = $props['attributes'] ?? [];
                        $allKeys = array_unique(array_merge(array_keys($old), array_keys($attrs)));
                    @endphp
                    @if(count($allKeys) > 0)
                        @foreach($allKeys as $key)
                            <strong>{{ $key }}:</strong>
                            @if(isset($old[$key]))
                                <span style="color:var(--c-primary-hover);">{{ is_array($old[$key]) ? json_encode($old[$key]) : $old[$key] }}</span> →
                            @endif
                            @if(isset($attrs[$key]))
                                <span style="color:var(--c-success);">{{ is_array($attrs[$key]) ? json_encode($attrs[$key]) : $attrs[$key] }}</span>
                            @endif
                            @if(!$loop->last); @endif
                        @endforeach
                    @else
                        —
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-faint);">No activity records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $portalName ?? \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal')) }} — Activity Log Report — Total: {{ $logs->count() }} entries — Page {PAGE_NUM} of {PAGE_COUNT}
    </div>
</body>
</html>
