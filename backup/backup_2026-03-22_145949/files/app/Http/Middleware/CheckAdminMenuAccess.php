<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\AdminMenu;
use App\Models\AdminRoleMenuAccess;

class CheckAdminMenuAccess
{
    public function handle(Request $request, Closure $next)
    {
        $adminId = $request->cookie('admin_id');
        
        if (!$adminId) {
            return redirect()->route('admin.login');
        }
        
        $admin = Admin::with('role')->find($adminId);
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }
        
        // Administrator has full access
        if ($admin->role && $admin->role->slug === 'administrator') {
            return $next($request);
        }
        
        // Get current route name
        $currentRoute = $request->route()->getName();
        
        // Find menu by route name
        $menu = AdminMenu::where('route_name', $currentRoute)->first();
        
        // If no menu found for this route, allow access (might be a system route)
        if (!$menu) {
            return $next($request);
        }
        
        // Check permission
        $permission = AdminRoleMenuAccess::where('role_id', $admin->role_id)
            ->where('menu_id', $menu->id)
            ->where('can_view', 1)
            ->first();
        
        if (!$permission) {
            // No permission - redirect to dashboard with error
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access that page.');
        }
        
        return $next($request);
    }
}