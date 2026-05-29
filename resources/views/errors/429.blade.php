@php
    $portalName = 'Admin Portal';
    $primaryColor = '#dc2626';
    $primaryHover = '#b91c1c';
    $bodyBg = '#f1f5f9';
    $fontFamily = 'Inter';
    try {
        $portalName = \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal'));
        $primaryColor = \App\Models\Configuration::get('primary', '#dc2626');
        $primaryHover = \App\Models\Configuration::get('primary_hover', '#b91c1c');
        $bodyBg = \App\Models\Configuration::get('body_bg', '#f1f5f9');
        $fontFamily = \App\Models\Configuration::get('font_family', 'Inter');
    } catch (\Exception $e) {}
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 — Page not found</title>
    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($fontFamily) }}:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'{{ $fontFamily }}',system-ui,sans-serif; background:{{ $bodyBg }}; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:48px; text-align:center; max-width:440px; width:90%; }
        .error-code { font-size:72px; font-weight:700; color:{{ $primaryColor }}; opacity:.15; line-height:1; margin-bottom:8px; }
        .icon { width:72px; height:72px; border-radius:16px; background:{{ $primaryColor }}10; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; }
        .icon i { font-size:32px; color:{{ $primaryColor }}; }
        h1 { font-size:22px; font-weight:700; color:#0f172a; margin-bottom:8px; }
        p { font-size:15px; color:#64748b; line-height:1.6; margin-bottom:24px; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; background:{{ $primaryColor }}; color:#fff; border-radius:10px; text-decoration:none; font-size:14px; font-weight:600; transition:background .2s; }
        .btn:hover { background:{{ $primaryHover }}; }
        .code { font-size:12px; color:#94a3b8; margin-top:20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="error-code">429</div>
        <div class="icon"><i class="fas fa-compass"></i></div>
        <h1>Page not found</h1>
        <p>You've made too many requests in a short period. Please wait a moment and try again.</p>
        <a href="/" class="btn"><i class="fas fa-arrow-left"></i> Back to {{ $portalName }}</a>
        <div class="code">{{ $portalName }}</div>
    </div>
</body>
</html>
