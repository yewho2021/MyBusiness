<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Traits\DecryptsCookie;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    use DecryptsCookie;
    public function handle(Request $request, Closure $next): Response
    {
        $adminId   = $this->decryptCookie($request->cookie('admin_id'));
        $sessionId = $request->cookie('admin_session_id');

        if (!$adminId) {
            $this->expireOrphanSession($sessionId);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired.'], 401);
            }
            return redirect()->route('admin.login');
        }

        $admin = Admin::with('role')->find($adminId);

        if (!$admin || !$admin->is_active) {
            $this->expireOrphanSession($sessionId);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired.'], 401);
            }
            return redirect()->route('admin.login')
                ->withCookie(\Cookie::forget('admin_id'))
                ->withCookie(\Cookie::forget('admin_session_id'));
        }

        // ── Session validation ─────────────────────────
        // Verify the session_id cookie matches an active session in tbl_admin_log.
        // If the session was kicked, expired, or closed from another device, force logout.
        if ($sessionId) {
            $sessionActive = AdminLog::where('session_id', $sessionId)
                ->where('status', 'active')
                ->exists();

            if (!$sessionActive) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Session expired.'], 401);
                }
                return redirect()->route('admin.login')
                    ->withCookie(\Cookie::forget('admin_id'))
                    ->withCookie(\Cookie::forget('admin_session_id'));
            }
        }

        // Store admin once — accessible everywhere in this request
        $request->attributes->set('admin', $admin);
        $request->attributes->set('admin_id', $admin->id);

        // Force password change on first login (password_changed_at is null)
        // Auto-backfill for pre-existing admins who already logged in before this feature
        if (is_null($admin->password_changed_at) && $admin->datetime_lastlogin) {
            $admin->password_changed_at = $admin->datetime_lastlogin;
            $admin->saveQuietly(); // saveQuietly avoids triggering activity log
        }

        if ($admin->mustChangePassword()) {
            $currentRoute = $request->route()?->getName();
            $allowed = ['admin.profile.index', 'admin.profile.update', 'admin.logout'];
            if (!in_array($currentRoute, $allowed)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Password change required.', 'redirect' => route('admin.profile.index')], 403);
                }
                return redirect()->route('admin.profile.index')
                    ->with('warning', 'Please change your password before continuing.');
            }
        }

        // ── Password expiry policy (configurable via tbl_configuration) ──
        // Checks password_expiry_days config — 0 means disabled.
        // Integrated here instead of separate middleware for shared hosting compatibility.
        if (!$request->ajax() && !$request->wantsJson()) {
            try {
                $expiryDays = (int) \App\Models\Configuration::get('password_expiry_days', 0);
                if ($expiryDays > 0) {
                    $currentRoute = $request->route()?->getName() ?? '';
                    $skipRoutes = [
                        'admin.profile.index', 'admin.profile.update', 'admin.logout',
                    ];
                    if (!in_array($currentRoute, $skipRoutes)) {
                        $changedAt = $admin->password_changed_at;
                        $expired = is_null($changedAt) || $changedAt->diffInDays(now()) >= $expiryDays;
                        if ($expired) {
                            return redirect()->route('admin.profile.index')
                                ->with('warning', "Your password has expired. Please change it now. (Policy: every {$expiryDays} days)");
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silent fail — don't break the app if config table is missing
            }
        }

        // ── 2FA enforcement check ──
        $admin = $request->attributes->get('admin');
        if ($admin) {
            try {
                $require2fa = \App\Models\Configuration::get('require_2fa_administrator', '0');
                if ($require2fa === '1' && $admin->isAdministrator() && !$admin->twofa_enabled) {
                    // Allow access to profile page (where they can set up 2FA) and logout
                    $currentRoute = $request->route() ? $request->route()->getName() : '';
                    $allowedRoutes = ['admin.profile.index', 'admin.profile.update', 'admin.logout'];
                    if (!in_array($currentRoute, $allowedRoutes)) {
                        return redirect()->route('admin.profile.index')
                            ->with('warning', '2FA is required for Administrator accounts. Please enable Two-Factor Authentication in your profile.');
                    }
                }
            } catch (\Exception $e) {
                // Config table may not be accessible
            }
        }

        $response = $next($request);

        // Prevent Cloudflare/proxy from caching admin pages
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    /**
     * If there's an orphan active session in DB, mark it as expired.
     */
    private function expireOrphanSession(?string $sessionId): void
    {
        if (!$sessionId) return;

        $log = AdminLog::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if ($log) {
            $log->closeSession('expired');
        }
    }
}
