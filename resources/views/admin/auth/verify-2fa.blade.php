@php
    $__cfg = \App\Models\Configuration::getAll();
    $__fontUrl = \App\Models\Configuration::googleFontUrl();
    $__portalName = $__cfg['portal_name'] ?? 'Admin Portal';
    $__logoType = $__cfg['logo_type'] ?? 'icon';
    $__logoIcon = $__cfg['logo_icon'] ?? 'fas fa-shield-alt';
    $__logoImage = $__cfg['logo_image'] ?? null;
    $__primary = $__cfg['primary'] ?? '#dc2626';
    $__loginBgType = $__cfg['login_bg_type'] ?? 'solid';
    $__loginBgColor = $__cfg['login_bg_color'] ?? '#dc2626';
    $__loginBgGradEnd = $__cfg['login_bg_gradient_end'] ?? '#991b1b';
    $__loginHeaderBg = $__cfg['login_header_bg'] ?? 'var(--text-primary)';
    $__loginHeaderEnd = $__cfg['login_header_bg_end'] ?? 'var(--text-body)';
    $__loginCardRadius = $__cfg['login_card_radius'] ?? '16';
    $__fontFamily = $__cfg['font_family'] ?? 'Inter';
    $__footerText = $__cfg['footer_text'] ?? '© {year} {portal_name}. All rights reserved.';
    $__footerText = str_replace(['{year}', '{portal_name}'], [date('Y'), $__portalName], $__footerText);

    if ($__loginBgType === 'gradient') {
        $__bodyBg = "linear-gradient(135deg, {$__loginBgColor} 0%, {$__loginBgGradEnd} 100%)";
    } else {
        $__bodyBg = $__loginBgColor;
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify 2FA - {{ $__portalName }}</title>
    @if(!empty($__cfg['favicon']))
    <link rel="icon" href="{{ asset($__cfg['favicon']) }}">
    @endif
    <link href="{{ $__fontUrl }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'{{ $__fontFamily }}',sans-serif; background:{{ $__bodyBg }}; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px; }
        .login-card { background:#fff; border-radius:{{ $__loginCardRadius }}px; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,.15); overflow:hidden; }
        .login-header { background:linear-gradient(135deg, {{ $__loginHeaderBg }}, {{ $__loginHeaderEnd }}); padding:32px 36px; text-align:center; }
        .logo { display:flex; align-items:center; justify-content:center; gap:12px; margin-bottom:12px; }
        .logo-icon { width:44px; height:44px; background:{{ $__primary }}; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px; }
        .logo-text { font-size:20px; font-weight:700; color:#fff; }
        .login-subtitle { font-size:14px; color:rgba(255,255,255,.6); }
        .login-body { padding:32px 36px; }

        .twofa-icon { text-align:center; margin-bottom:20px; }
        .twofa-icon i { font-size:48px; color:{{ $__primary }}; opacity:.8; }
        .twofa-info { text-align:center; margin-bottom:24px; }
        .twofa-info h3 { font-size:18px; font-weight:600; color:var(--text-heading); margin-bottom:6px; }
        .twofa-info p { font-size:14px; color:var(--text-muted); line-height:1.5; }
        .twofa-info .admin-name { font-weight:600; color:var(--text-heading); }

        .form-group { margin-bottom:20px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:var(--text-body); margin-bottom:6px; }
        .otp-input { width:100%; padding:14px 16px; border:2px solid var(--border-color); border-radius:10px; font-size:24px; font-weight:700; text-align:center; letter-spacing:12px; font-family:'{{ $__fontFamily }}',monospace; outline:none; transition:all .2s; }
        .otp-input:focus { border-color:{{ $__primary }}; box-shadow:0 0 0 4px {{ $__primary }}1a; }
        .otp-input.error { border-color:var(--c-danger); box-shadow:0 0 0 4px rgba(239,68,68,.1); }

        .error-msg { background:var(--c-danger-light); color:#b91c1c; border:1px solid #fecaca; padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
        .error-msg i { flex-shrink:0; }

        .btn-verify { width:100%; padding:14px; background:linear-gradient(135deg, {{ $__primary }}, {{ $__loginBgGradEnd }}); color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
        .btn-verify:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 6px 20px rgba(0,0,0,.15); }

        .back-link { display:block; text-align:center; margin-top:16px; font-size:13px; color:var(--text-muted); text-decoration:none; }
        .back-link:hover { color:var(--text-heading); }
        .back-link i { margin-right:4px; }

        .login-footer { text-align:center; margin-top:24px; font-size:12px; color:rgba(255,255,255,.5); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo">
                @if($__logoType === 'image' && $__logoImage)
                    <img src="{{ asset($__logoImage) }}" alt="{{ $__portalName }}" style="max-height:44px;max-width:180px;object-fit:contain;">
                @else
                    <div class="logo-icon"><i class="{{ $__logoIcon }}"></i></div>
                    <span class="logo-text">{{ $__portalName }}</span>
                @endif
            </div>
            <div class="login-subtitle">Two-Factor Verification</div>
        </div>

        <div class="login-body">
            <div class="twofa-icon">
                <i class="fas fa-shield-alt"></i>
            </div>

            <div class="twofa-info">
                <h3>Enter Verification Code</h3>
                <p>Open your authenticator app and enter the 6-digit code for <span class="admin-name">{{ $adminName }}</span>.</p>
            </div>

            @if($errors->has('code'))
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('code') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.2fa.verify') }}">
                @csrf
                <div class="form-group">
                    <label>Authentication Code</label>
                    <input type="text" name="code" class="otp-input {{ $errors->has('code') ? 'error' : '' }}"
                           maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code"
                           autofocus required placeholder="000000">
                </div>

                <button type="submit" class="btn-verify">
                    <i class="fas fa-check-circle"></i> Verify & Sign In
                </button>
            </form>

            <a href="{{ route('admin.login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

    <div class="login-footer">{!! \App\Services\HtmlSanitizer::sanitizeRichText($__footerText) !!}</div>

    <script>
        // Auto-focus and filter to digits only
        const input = document.querySelector('.otp-input');
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
        // Auto-submit when 6 digits entered
        input.addEventListener('input', function() {
            if (this.value.length === 6) {
                this.closest('form').submit();
            }
        });
    </script>
</body>
</html>
