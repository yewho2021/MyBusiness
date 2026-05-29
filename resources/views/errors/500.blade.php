@php
    $portalName = 'Admin Portal';
    $primaryColor = '#dc2626';
    $primaryHover = '#b91c1c';
    $bodyBg = '#f1f5f9';
    $fontFamily = 'Inter';
    $fontSource = 'google';
    $faSource = 'cdn';
    try {
        $portalName = \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal'));
        $primaryColor = \App\Models\Configuration::get('primary', '#dc2626');
        $primaryHover = \App\Models\Configuration::get('primary_hover', '#b91c1c');
        $bodyBg = \App\Models\Configuration::get('body_bg', '#f1f5f9');
        $fontFamily = \App\Models\Configuration::get('font_family', 'Inter');
        $fontSource = \App\Models\Configuration::get('font_source', 'google');
        $faSource = \App\Models\Configuration::get('fontawesome_source', 'cdn');
    } catch (\Exception $e) {}
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server error</title>
    @if($fontSource === 'local')
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    @else
    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($fontFamily) }}:wght@400;600;700&display=swap" rel="stylesheet">
    @endif
    @if($faSource === 'local')
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    @else
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @endif
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'{{ $fontFamily }}',system-ui,sans-serif; background:{{ $bodyBg }}; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:48px; text-align:center; max-width:440px; width:90%; }
        .error-code { font-size:72px; font-weight:700; color:#dc2626; opacity:.15; line-height:1; margin-bottom:8px; }
        .icon { width:72px; height:72px; border-radius:16px; background:#fef2f2; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; }
        .icon i { font-size:32px; color:#dc2626; }
        h1 { font-size:22px; font-weight:700; color:#0f172a; margin-bottom:8px; }
        p { font-size:15px; color:#64748b; line-height:1.6; margin-bottom:24px; }
        .actions { display:flex; gap:8px; justify-content:center; flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; background:{{ $primaryColor }}; color:#fff; border-radius:10px; text-decoration:none; font-size:14px; font-weight:600; transition:background .2s; border:none; cursor:pointer; }
        .btn:hover { background:{{ $primaryHover }}; }
        .btn-outline { background:transparent; color:#475569; border:1px solid #e2e8f0; }
        .btn-outline:hover { background:#f8fafc; }
        .code { font-size:12px; color:#94a3b8; margin-top:20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="error-code">500</div>
        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h1>Something went wrong</h1>
        <p>An unexpected error occurred. If this persists, contact the system administrator or try clearing the cache.</p>
        <div class="actions">
            <a href="/" class="btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <button onclick="location.reload()" class="btn btn-outline"><i class="fas fa-redo"></i> Retry</button>
        </div>
        <div class="code">{{ $portalName }} — {{ date('Y-m-d H:i:s') }}</div>
    </div>
</body>
</html>
