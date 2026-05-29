<?php
    $__cfg = \App\Models\Configuration::getAll();
    $__fontUrl = \App\Models\Configuration::googleFontUrl();
    $__portalName = $__cfg['portal_name'] ?? 'Admin Portal';
    $__tagline = $__cfg['portal_tagline'] ?? 'Sign in to your account';
    $__logoType = $__cfg['logo_type'] ?? 'icon';
    $__logoIcon = $__cfg['logo_icon'] ?? 'fas fa-shield-alt';
    $__logoImage = $__cfg['logo_image'] ?? null;
    $__primary = $__cfg['primary'] ?? '#2563eb';
    $__loginBgType = $__cfg['login_bg_type'] ?? 'gradient';
    $__loginBgColor = $__cfg['login_bg_color'] ?? '#0f172a';
    $__loginBgGradEnd = $__cfg['login_bg_gradient_end'] ?? '#1e293b';
    $__loginBgImage = $__cfg['login_bg_image'] ?? null;
    $__loginHeaderBg = $__cfg['login_header_bg'] ?? '#1e293b';
    $__loginHeaderEnd = $__cfg['login_header_bg_end'] ?? '#334155';
    $__loginCardRadius = $__cfg['login_card_radius'] ?? '20';
    $__fontFamily = $__cfg['font_family'] ?? 'Inter';
    $__footerText = $__cfg['footer_text'] ?? '© {year} {portal_name}. All rights reserved.';
    $__footerText = str_replace(['{year}', '{portal_name}'], [date('Y'), $__portalName], $__footerText);

    if ($__loginBgType === 'gradient') {
        $__bodyBg = "linear-gradient(135deg, {$__loginBgColor} 0%, {$__loginBgGradEnd} 100%)";
    } elseif ($__loginBgType === 'image' && $__loginBgImage) {
        $__bodyBg = "{$__loginBgColor}";
    } else {
        $__bodyBg = $__loginBgColor;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo e($__portalName); ?></title>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($__cfg['favicon'])): ?>
    <link rel="icon" href="<?php echo e(asset($__cfg['favicon'])); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <link href="<?php echo e($__fontUrl); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: '<?php echo e($__fontFamily); ?>', -apple-system, BlinkMacSystemFont, sans-serif;
            background: <?php echo e($__bodyBg); ?>;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            <?php if($__loginBgType === 'image' && $__loginBgImage): ?>
            background-image: url('<?php echo e(asset($__loginBgImage)); ?>');
            background-size: cover;
            background-position: center;
            <?php endif; ?>
        }

        /* Subtle animated background pattern */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.03) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.02) 0%, transparent 40%);
            pointer-events: none;
        }

        .login-wrap {
            width: 100%;
            max-width: 420px;
            animation: fadeUp 0.5s ease;
        }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(20px); }
            to { opacity:1; transform:translateY(0); }
        }

        /* ── Card ── */
        .login-card {
            background: rgba(255,255,255,0.98);
            border-radius: <?php echo e($__loginCardRadius); ?>px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.2), 0 0 0 1px rgba(255,255,255,0.05);
            overflow: hidden;
            backdrop-filter: blur(20px);
        }

        /* ── Header ── */
        .login-header {
            background: linear-gradient(135deg, <?php echo e($__loginHeaderBg); ?>, <?php echo e($__loginHeaderEnd); ?>);
            padding: 40px 32px 52px;
            text-align: center;
            position: relative;
        }
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0; right: 0;
            height: 30px;
            background: rgba(255,255,255,0.98);
            border-radius: <?php echo e($__loginCardRadius); ?>px <?php echo e($__loginCardRadius); ?>px 0 0;
        }

        .login-logo {
            position: relative;
            z-index: 2;
            margin-bottom: 16px;
        }
        .login-logo .logo-circle {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        .login-logo .logo-circle.has-image {
            background: #fff;
            border-color: rgba(255,255,255,0.3);
            padding: 10px;
        }
        .login-logo .logo-circle i { font-size: 32px; color: #fff; }
        .login-logo .logo-circle img {
            max-height: 56px;
            max-width: 56px;
            object-fit: contain;
        }

        .login-header h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
            margin-bottom: 4px;
            position: relative;
            z-index: 2;
        }
        .login-header p {
            color: rgba(255,255,255,0.6);
            font-size: 14px;
            font-weight: 400;
            position: relative;
            z-index: 2;
        }

        /* ── Body ── */
        .login-body { padding: 12px 32px 32px; position: relative; z-index: 2; }

        .alert-error {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.5;
        }
        .alert-error i { margin-top: 2px; flex-shrink: 0; }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: 0.01em;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            background: #f9fafb;
            transition: all 0.2s ease;
        }
        .input-wrap:focus-within {
            border-color: <?php echo e($__primary); ?>;
            background: #fff;
            box-shadow: 0 0 0 3px <?php echo e($__primary); ?>20;
        }
        .input-wrap i.icon {
            position: absolute;
            left: 14px;
            color: #9ca3af;
            font-size: 15px;
            pointer-events: none;
            transition: color 0.2s;
        }
        .input-wrap:focus-within i.icon { color: <?php echo e($__primary); ?>; }

        .input-wrap input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            border: none;
            background: transparent;
            font-size: 14px;
            font-family: inherit;
            color: #1f2937;
            outline: none;
        }
        .input-wrap input::placeholder { color: #9ca3af; }

        .pw-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            font-size: 15px;
            transition: color 0.2s;
        }
        .pw-toggle:hover { color: #6b7280; }

        /* ── Options row ── */
        .form-options {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 13px;
            color: #6b7280;
        }
        .remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: <?php echo e($__primary); ?>;
            border-radius: 4px;
            cursor: pointer;
        }

        /* ── Submit ── */
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: <?php echo e($__primary); ?>;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px <?php echo e($__primary); ?>40;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px <?php echo e($__primary); ?>50;
        }
        .btn-submit:active {
            transform: translateY(0);
        }

        /* ── Footer ── */
        .login-footer {
            text-align: center;
            padding: 16px 32px;
            border-top: 1px solid #f3f4f6;
        }
        .login-footer p { font-size: 12px; color: #9ca3af; }

        /* ── Mobile ── */
        @media (max-width: 480px) {
            .login-header { padding: 32px 24px 24px; }
            .login-body { padding: 8px 24px 24px; }
            .login-footer { padding: 14px 24px; }
        }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <div class="logo-circle <?php echo e(($__logoType === 'image' || $__logoType === 'both') && $__logoImage ? 'has-image' : ''); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($__logoType === 'image' || $__logoType === 'both') && $__logoImage): ?>
                            <img src="<?php echo e(asset($__logoImage)); ?>" alt="<?php echo e($__portalName); ?>">
                        <?php else: ?>
                            <i class="<?php echo e($__logoIcon); ?>"></i>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <h1><?php echo e($__portalName); ?></h1>
                <p><?php echo e($__tagline); ?></p>
            </div>
            <div class="login-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="alert-error">
                        <i class="fas fa-circle-exclamation"></i>
                        <div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><?php echo e($error); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <form method="POST" action="<?php echo e(route('admin.login.submit')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <div class="input-wrap">
                            <i class="fas fa-user icon"></i>
                            <input type="text" id="username" name="username" value="<?php echo e(old('username')); ?>" placeholder="Enter username or email" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock icon"></i>
                            <input type="password" id="password" name="password" placeholder="Enter password" required>
                            <button type="button" class="pw-toggle" onclick="togglePw()">
                                <i class="fas fa-eye" id="pwIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-options">
                        <label class="remember">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        Sign In
                    </button>
                </form>
            </div>
            <div class="login-footer">
                <p><?php echo \App\Services\HtmlSanitizer::sanitizeRichText($__footerText); ?></p>
            </div>
        </div>
    </div>
    <script>
        function togglePw(){
            const p=document.getElementById('password'),i=document.getElementById('pwIcon');
            if(p.type==='password'){p.type='text';i.classList.replace('fa-eye','fa-eye-slash');}
            else{p.type='password';i.classList.replace('fa-eye-slash','fa-eye');}
        }
    </script>
</body>
</html>
<?php /**PATH /home/mybusiness/office.mybusiness.com.my/resources/views/admin/auth/login.blade.php ENDPATH**/ ?>