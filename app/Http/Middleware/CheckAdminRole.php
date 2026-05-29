<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Traits\DecryptsCookie;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    use DecryptsCookie;

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Prefer singleton set by AdminAuthenticate
        $admin = $request->attributes->get('admin');

        if (!$admin) {
            $adminId = $this->decryptCookie($request->cookie('admin_id'));
            if (!$adminId) {
                return redirect()->route('admin.login');
            }
            $admin = Admin::with('role')->find($adminId);
        }

        if (!$admin || !$admin->role) {
            return redirect()->route('admin.login');
        }

        if (!in_array($admin->role->slug, $roles)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
            }
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
