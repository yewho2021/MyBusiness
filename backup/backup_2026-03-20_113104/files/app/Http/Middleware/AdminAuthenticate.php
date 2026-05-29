<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->cookie('admin_id');
        
        if (!$adminId) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find($adminId);
        
        if (!$admin || $admin->is_active != 1) {
            return redirect()->route('admin.login')->withCookie(\Cookie::forget('admin_id'));
        }

        return $next($request);
    }
}
