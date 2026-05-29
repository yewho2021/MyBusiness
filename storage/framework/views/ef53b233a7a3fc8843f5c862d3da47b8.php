<?php
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page not found</title>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fontSource === 'local'): ?>
    <link href="<?php echo e(asset('vendor/fonts/fonts.css')); ?>" rel="stylesheet">
    <?php else: ?>
    <link href="https://fonts.googleapis.com/css2?family=<?php echo e(urlencode($fontFamily)); ?>:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($faSource === 'local'): ?>
    <link rel="stylesheet" href="<?php echo e(asset('vendor/fontawesome/css/all.min.css')); ?>">
    <?php else: ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'<?php echo e($fontFamily); ?>',system-ui,sans-serif; background:<?php echo e($bodyBg); ?>; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:48px; text-align:center; max-width:440px; width:90%; }
        .error-code { font-size:72px; font-weight:700; color:<?php echo e($primaryColor); ?>; opacity:.15; line-height:1; margin-bottom:8px; }
        .icon { width:72px; height:72px; border-radius:16px; background:<?php echo e($primaryColor); ?>10; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; }
        .icon i { font-size:32px; color:<?php echo e($primaryColor); ?>; }
        h1 { font-size:22px; font-weight:700; color:#0f172a; margin-bottom:8px; }
        p { font-size:15px; color:#64748b; line-height:1.6; margin-bottom:24px; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; background:<?php echo e($primaryColor); ?>; color:#fff; border-radius:10px; text-decoration:none; font-size:14px; font-weight:600; transition:background .2s; }
        .btn:hover { background:<?php echo e($primaryHover); ?>; }
        .code { font-size:12px; color:#94a3b8; margin-top:20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="error-code">404</div>
        <div class="icon"><i class="fas fa-compass"></i></div>
        <h1>Page not found</h1>
        <p>The page you're looking for doesn't exist or has been moved.</p>
        <a href="/" class="btn"><i class="fas fa-arrow-left"></i> Back to <?php echo e($portalName); ?></a>
        <div class="code"><?php echo e($portalName); ?></div>
    </div>
</body>
</html>
<?php /**PATH /home/mybusiness/office.mybusiness.com.my/resources/views/errors/404.blade.php ENDPATH**/ ?>