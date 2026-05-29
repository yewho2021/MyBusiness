<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $adminId = $request->cookie('admin_id');
        $admin = Admin::with('role')->find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        if (!in_array($admin->role->slug, $roles)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
