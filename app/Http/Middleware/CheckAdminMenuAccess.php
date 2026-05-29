<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\AdminMenu;
use App\Models\AdminRoleMenuAccess;
use App\Traits\DecryptsCookie;

class CheckAdminMenuAccess
{
    use DecryptsCookie;

    /**
     * Map HTTP methods to permission columns.
     */
    protected array $methodPermissionMap = [
        'GET'    => 'can_view',
        'HEAD'   => 'can_view',
        'POST'   => 'can_create',
        'PUT'    => 'can_edit',
        'PATCH'  => 'can_edit',
        'DELETE' => 'can_delete',
    ];

    /**
     * System routes that all authenticated roles can access
     * (no menu entry needed for these).
     */
    protected array $systemRoutes = [
        'admin.dashboard',
        'admin.profile.index',
        'admin.profile.update',
        'admin.profile.update-password',
        'admin.profile.enable-2fa',
        'admin.profile.disable-2fa',
        'admin.global-search',
        'admin.logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $admin = $request->attributes->get('admin');

        if (!$admin) {
            $adminId = $this->decryptCookie($request->cookie('admin_id'));
            if (!$adminId) {
                return redirect()->route('admin.login');
            }
            $admin = Admin::with('role')->find($adminId);
            if (!$admin) {
                return redirect()->route('admin.login');
            }
            $request->attributes->set('admin', $admin);
        }

        // Administrator has full access
        if ($admin->role && $admin->role->slug === 'administrator') {
            return $next($request);
        }

        $currentRoute = $request->route()->getName();

        // Find menu by exact route name first
        $menu = AdminMenu::where('route_name', $currentRoute)->first();

        // If not found, try the parent module route (e.g. admin.users.store → admin.users.index)
        if (!$menu && $currentRoute) {
            $parts = explode('.', $currentRoute);
            if (count($parts) >= 3) {
                $parentRoute = $parts[0] . '.' . $parts[1] . '.index';
                $menu = AdminMenu::where('route_name', $parentRoute)->first();
            }
        }

        // If no menu found: allow system routes, deny everything else
        if (!$menu) {
            if (in_array($currentRoute, $this->systemRoutes)) {
                return $next($request);
            }
            return $this->deny($request, 'You do not have permission to access that page.');
        }

        // Determine required permission based on HTTP method
        $method = strtoupper($request->method());
        $requiredPerm = $this->methodPermissionMap[$method] ?? 'can_view';

        $access = AdminRoleMenuAccess::where('role_id', $admin->role_id)
            ->where('menu_id', $menu->id)
            ->first();

        // Check user-level override (takes priority over role)
        $userOverride = \App\Models\AdminUserMenuAccess::where('admin_id', $admin->id)
            ->where('menu_id', $menu->id)
            ->first();

        $effectiveAccess = $userOverride ?? $access;

        if (!$effectiveAccess || !$effectiveAccess->can_view) {
            return $this->deny($request, 'You do not have permission to access that page.');
        }

        if ($requiredPerm !== 'can_view' && !$effectiveAccess->{$requiredPerm}) {
            $action = str_replace('can_', '', $requiredPerm);
            return $this->deny($request, "You do not have permission to {$action} in this module.");
        }

        // Store permission record so controllers can check granular access
        $request->attributes->set('menu_access', $effectiveAccess);

        return $next($request);
    }

    protected function deny(Request $request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }
        return redirect()->route('admin.dashboard')->with('error', $message);
    }
}
