<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — Export</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: var(--text-heading); }
        .header { text-align: center; padding: 20px 30px 15px; border-bottom: 3px solid var(--c-danger); margin-bottom: 16px; }
        .header h1 { font-size: 18px; font-weight: 700; color: var(--code-bg); margin-bottom: 4px; }
        .header .portal { font-size: 12px; color: var(--c-danger); font-weight: 600; margin-bottom: 4px; }
        .header .subtitle { font-size: 10px; color: var(--text-muted); }
        .content { padding: 0 20px; }
        .meta { background: var(--table-header-bg); border: 1px solid var(--border-color); border-radius: 4px; padding: 8px 14px; margin-bottom: 12px; font-size: 9px; color: var(--text-secondary); }
        .meta strong { color: var(--code-bg); }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--text-heading); color: #fff; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; padding: 6px 8px; text-align: left; }
        td { padding: 5px 8px; font-size: 9px; border-bottom: 1px solid var(--border-color); }
        tr:nth-child(even) td { background: var(--table-header-bg); }
        .footer { margin-top: 16px; text-align: center; font-size: 8px; color: var(--text-faint); border-top: 1px solid var(--border-color); padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="portal">{{ $portalName ?? \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal')) }}</div>
        <h1>{{ $title }}</h1>
        <div class="subtitle">Generated: {{ $generated }} | Total rows: {{ $total }}</div>
    </div>
    <div class="content">
        @if(!empty(array_filter($filters ?? [])))
        <div class="meta">
            <strong>Filters:</strong>
            @foreach($filters as $k => $v)
                @if($v) {{ ucfirst(str_replace('_',' ',$k)) }}: {{ $v }} &nbsp;|&nbsp; @endif
            @endforeach
        </div>
        @endif

        <table>
            <thead>
                <tr>
                    @foreach($headings as $h)
                    <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                    <td>{{ \Illuminate\Support\Str::limit(is_array($cell) ? json_encode($cell) : $cell, 80) }}</td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ count($headings) }}" style="text-align:center;padding:20px;color:var(--text-faint);">No data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="footer">{{ $portalName ?? \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal')) }} — {{ $title }} — {{ $generated }}</div>
</body>
</html>
