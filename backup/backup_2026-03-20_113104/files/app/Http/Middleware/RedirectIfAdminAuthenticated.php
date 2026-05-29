<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->cookie('admin_id');
        
        if ($adminId) {
            $admin = Admin::find($adminId);
            if ($admin && $admin->is_active == 1) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
