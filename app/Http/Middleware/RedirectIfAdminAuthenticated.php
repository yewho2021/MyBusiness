<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Traits\DecryptsCookie;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminAuthenticated
{
    use DecryptsCookie;
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $this->decryptCookie($request->cookie('admin_id'));

        if ($adminId) {
            $admin = Admin::find($adminId);
            if ($admin && $admin->is_active) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
