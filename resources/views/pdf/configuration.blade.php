@extends('pdf.layout')
@section('title', 'System Configuration')

@section('content')
<div class="meta-bar">
    <strong>Total Groups:</strong> {{ $groups->count() }} |
    <strong>Total Keys:</strong> {{ $groups->flatten()->count() }}
</div>

@foreach($groups as $groupName => $rows)
<div class="section-title">{{ ucfirst(str_replace('_', ' ', $groupName)) }}</div>
<table>
    <thead>
        <tr>
            <th style="width:180px;">Key</th>
            <th>Label</th>
            <th>Value</th>
            <th>Default</th>
            <th style="width:60px;">Type</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td style="font-family:monospace;font-size:9px;color:#475569;">{{ $row->key }}</td>
            <td style="font-weight:600;">{{ $row->label }}</td>
            <td>
                @if($row->type === 'color')
                    <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:{{ $row->value ?? $row->default_value }};border:1px solid #ccc;vertical-align:middle;"></span>
                    {{ $row->value ?? $row->default_value }}
                @elseif($row->type === 'boolean')
                    {!! ($row->value ?? $row->default_value) ? '<span class="badge badge-green">Yes</span>' : '<span class="badge badge-red">No</span>' !!}
                @elseif($row->type === 'image')
                    {{ $row->value ? '[uploaded]' : '[none]' }}
                @else
                    {{ \Illuminate\Support\Str::limit($row->value ?? $row->default_value, 60) }}
                @endif
            </td>
            <td style="color:#94a3b8;">{{ \Illuminate\Support\Str::limit($row->default_value, 40) }}</td>
            <td><span class="badge badge-gray">{{ $row->type }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endforeach
@endsection
