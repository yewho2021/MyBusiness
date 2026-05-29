<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Report')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; }
        .header { text-align: center; padding: 20px 30px 15px; border-bottom: 3px solid #dc2626; margin-bottom: 20px; }
        .header h1 { font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .header .subtitle { font-size: 11px; color: #64748b; }
        .header .portal { font-size: 12px; color: #dc2626; font-weight: 600; margin-bottom: 4px; }
        .content { padding: 0 30px; }
        .meta-bar { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 14px; margin-bottom: 16px; font-size: 10px; color: #475569; }
        .meta-bar strong { color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table th { background: #1e293b; color: #fff; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; padding: 8px 10px; text-align: left; }
        table td { padding: 6px 10px; font-size: 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        table tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .section-title { font-size: 14px; font-weight: 700; color: #0f172a; margin: 16px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #dc2626; }
        .page-break { page-break-after: always; }
    </style>
    @yield('styles')
</head>
<body>
    <div class="header">
        <div class="portal">{{ $portalName ?? \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal')) }}</div>
        <h1>@yield('title', 'Report')</h1>
        <div class="subtitle">Generated: {{ $generated ?? now()->format('Y-m-d H:i:s') }} | By: {{ $admin->name ?? 'System' }}</div>
    </div>
    <div class="content">
        @yield('content')
    </div>
    <div class="footer">
        {{ $portalName ?? \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal')) }} — @yield('title', 'Report')
    </div>
</body>
</html>
