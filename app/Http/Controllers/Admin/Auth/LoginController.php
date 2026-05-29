<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Services\TwoFactorService;
use App\Traits\DecryptsCookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    use DecryptsCookie;
    /**
     * Max login attempts before lockout.
     */
    protected int $maxAttempts = 5;
    protected int $decayMinutes = 2;

    public function showLoginForm(Request $request)
    {
        $adminId = $this->decryptCookie($request->cookie('admin_id'));
        if ($adminId) {
            $admin = Admin::find($adminId);
            if ($admin && $admin->is_active) {
                return redirect()->route('admin.dashboard');
            }
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // ── Rate limiting ──────────────────────────────
        $throttleKey = 'login:' . Str::lower($request->input('username')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AdminLog::record($request, 'failed_password', null, $request->input('username'), 'Rate limited');
            return back()->withErrors([
                'username' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->withInput($request->only('username'));
        }

        $username = $request->input('username');
        $password = $request->input('password');

        $admin = Admin::with('role')
            ->where(function ($query) use ($username) {
                $query->where('username', $username)
                      ->orWhere('email', $username);
            })
            ->first();

        // Failed: user not found
        if (!$admin) {
            RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
            AdminLog::record($request, 'failed_not_found', null, $username, 'User not found');
            return back()->withErrors([
                'username' => 'Invalid credentials.',
            ])->withInput($request->only('username'));
        }

        // Failed: account inactive
        if (!$admin->is_active) {
            RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
            $reason = $admin->isLocked() ? 'Account locked due to too many failed attempts.' : 'Account deactivated. Contact your administrator.';
            AdminLog::record($request, $admin->isLocked() ? 'failed_locked' : 'failed_inactive', $admin, $username, $reason);
            return back()->withErrors([
                'username' => $reason,
            ])->withInput($request->only('username'));
        }

        // Failed: wrong password
        if (!Hash::check($password, $admin->password)) {
            RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
            $admin->recordFailedLogin();
            AdminLog::record($request, 'failed_password', $admin, $username, 'Incorrect password (attempt ' . $admin->fresh()->failed_login_count . ')');
            return back()->withErrors([
                'username' => 'Invalid credentials.',
            ])->withInput($request->only('username'));
        }

        // ── Credentials OK — clear rate limiter ────────
        RateLimiter::clear($throttleKey);
        $admin->resetFailedLogins();

        // ── 2FA check ──────────────────────────────────
        if ($admin->twofa_enabled && $admin->twofa_secret) {
            $pendingCookie = Cookie::make(
                'admin_2fa_pending',
                encrypt($admin->id),
                5,        // 5 minutes
                '/',
                null,
                $request->secure(), // match HTTPS state
                true,     // httpOnly
                false,    // raw
                'strict'  // sameSite
            );

            return redirect()->route('admin.login.2fa')
                ->withCookie($pendingCookie);
        }

        // ── No 2FA — complete login ────────────────────
        return $this->completeLogin($request, $admin);
    }

    /**
     * Show the 2FA verification form.
     */
    public function show2faForm(Request $request)
    {
        $adminId = $this->getPendingAdminId($request);
        if (!$adminId) {
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'Session expired. Please log in again.']);
        }

        $admin = Admin::find($adminId);
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.verify-2fa', ['adminName' => $admin->name]);
    }

    /**
     * Verify the 2FA code and complete login.
     */
    public function verify2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        // Rate limit 2FA attempts (prevent brute-force of 6-digit code)
        $throttleKey = '2fa:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['code' => "Too many attempts. Try again in {$seconds} seconds."]);
        }

        $adminId = $this->getPendingAdminId($request);
        if (!$adminId) {
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'Session expired. Please log in again.']);
        }

        $admin = Admin::with('role')->find($adminId);
        if (!$admin || !$admin->twofa_enabled || !$admin->twofa_secret) {
            return redirect()->route('admin.login');
        }

        $twofa = app(TwoFactorService::class);
        $code = $request->input('code');

        if (!$twofa->verifyCode($admin->twofa_secret, $code)) {
            RateLimiter::hit($throttleKey, 120);
            AdminLog::record($request, 'failed_password', $admin, $admin->username, '2FA code incorrect');
            return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
        }

        // ── 2FA passed — clear limiter and complete login ──
        RateLimiter::clear($throttleKey);
        $forgetPending = Cookie::forget('admin_2fa_pending');

        return $this->completeLogin($request, $admin)
            ->withCookie($forgetPending);
    }

    /**
     * Complete the login — set encrypted cookies, record log, redirect.
     */
    protected function completeLogin(Request $request, Admin $admin)
    {
        // Regenerate session ID to prevent session fixation attacks
        $request->session()->regenerate();

        $admin->datetime_lastlogin = now();
        $admin->save();

        $sessionId = AdminLog::generateSessionId();

        // Check for new IP — trigger notification if enabled
        $this->checkNewIpLogin($request, $admin);

        AdminLog::record($request, 'active', $admin, $admin->username, null, $sessionId);

        $cookieMinutes = (int) \App\Models\Configuration::get('admin_cookie_lifetime_minutes', 480);
        $secure = $request->secure(); // true on HTTPS, false on HTTP (local dev)

        // Encrypted admin_id cookie
        $cookie  = Cookie::make('admin_id', encrypt($admin->id), $cookieMinutes, '/', null, $secure, true, false, 'strict');
        $sCookie = Cookie::make('admin_session_id', $sessionId, $cookieMinutes, '/', null, $secure, true, false, 'strict');

        return redirect()->route('admin.dashboard')
            ->withCookie($cookie)
            ->withCookie($sCookie);
    }

    /**
     * Check if this IP is new for this admin and send notification.
     * (Item #20 — email on new IP)
     */
    protected function checkNewIpLogin(Request $request, Admin $admin): void
    {
        try {
            $currentIp = $request->ip();
            $knownIps = AdminLog::where('admin_id', $admin->id)
                ->whereIn('status', ['active', 'success', 'expired'])
                ->distinct()
                ->pluck('ip_address')
                ->toArray();

            if (!empty($knownIps) && !in_array($currentIp, $knownIps)) {
                $emailEnabled = \App\Models\Configuration::get('login_new_ip_notify', 'disabled');
                if ($emailEnabled === 'enabled' && $admin->email) {
                    $geo = AdminLog::resolveIpGeo($currentIp);
                    $ua = AdminLog::parseUserAgent($request->userAgent());
                    $portalName = \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal'));

                    \Illuminate\Support\Facades\Mail::raw(
                        "New login detected for your account on {$portalName}.\n\n" .
                        "Time: " . now()->format('Y-m-d H:i:s T') . "\n" .
                        "IP: {$currentIp}\n" .
                        "Location: " . ($geo['ip_city'] ?? 'Unknown') . ", " . ($geo['ip_country'] ?? 'Unknown') . "\n" .
                        "Browser: " . ($ua['browser'] ?? 'Unknown') . "\n" .
                        "Platform: " . ($ua['platform'] ?? 'Unknown') . "\n\n" .
                        "If this wasn't you, please change your password immediately.",
                        function ($message) use ($admin, $portalName) {
                            $message->to($admin->email)
                                ->subject("[{$portalName}] New login from unfamiliar IP");
                        }
                    );
                }
            }
        } catch (\Exception $e) {
            // Silent fail — notification is best-effort
        }
    }

    /**
     * Decrypt the pending admin ID from the 2FA cookie.
     */
    protected function getPendingAdminId(Request $request): ?int
    {
        $encrypted = $request->cookie('admin_2fa_pending');
        if (!$encrypted) {
            return null;
        }

        try {
            return (int) decrypt($encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function logout(Request $request)
    {
        $sessionId = $request->cookie('admin_session_id');
        if ($sessionId) {
            $log = AdminLog::where('session_id', $sessionId)
                ->where('status', 'active')
                ->first();
            if ($log) {
                $log->closeSession('manual');
            }
        }

        $cookie  = Cookie::forget('admin_id');
        $sCookie = Cookie::forget('admin_session_id');
        $pCookie = Cookie::forget('admin_2fa_pending');

        return redirect()->route('admin.login')
            ->withCookie($cookie)
            ->withCookie($sCookie)
            ->withCookie($pCookie);
    }
}
