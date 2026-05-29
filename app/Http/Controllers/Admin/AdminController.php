<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Models\AdminMenu;
use App\Models\AdminMenuGroup;
use App\Models\AdminRole;
use App\Models\AdminRoleMenuAccess;
use App\Models\AdminUserMenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Yajra DataTables AJAX endpoint (kept for backward compat)
        if ($request->ajax() && $request->has('draw')) {
            $query = Admin::with('role')->select('tbl_admin.*');

            return DataTables::of($query)
                ->addColumn('role_name', fn($admin) => $admin->role->name ?? 'No Role')
                ->addColumn('role_slug', fn($admin) => $admin->role->slug ?? 'default')
                ->addColumn('status_label', fn($admin) => $admin->is_active ? 'Active' : 'Inactive')
                ->addColumn('last_login_formatted', fn($admin) => $admin->datetime_lastlogin
                    ? $admin->datetime_lastlogin->format('M d, Y H:i')
                    : 'Never')
                ->addColumn('initial', fn($admin) => strtoupper(substr($admin->name, 0, 1)))
                ->addColumn('actions', fn($admin) => $admin->id)
                ->addColumn('route_token', fn($admin) => $admin->getRouteToken())
                ->rawColumns(['actions'])
                ->make(true);
        }

        // Standard server-side pagination
        $query = Admin::with('role')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', $s)
                  ->orWhere('username', 'LIKE', $s)
                  ->orWhere('email', 'LIKE', $s);
            });
        }

        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $admins = $query->paginate(25)->appends($request->query());
        $roles = AdminRole::where('is_active', 1)->get();

        return view('admin.pages.users.index', compact('admins', 'roles'));
    }

    public function store(StoreAdminRequest $request)
    {
        Admin::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
            'role_id'   => $request->role_id,
            'is_active' => $request->boolean('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Admin created successfully.');
    }

    public function edit(Request $request, $token)
    {
        $admin = Admin::findByTokenOrFail($token);
        $roles = AdminRole::where('is_active', 1)->get();
        $activeTab = $request->get('tab', 'profile');

        $logs = collect();
        $logStats = [];

        if ($activeTab === 'log') {
            $query = AdminLog::where('admin_id', $admin->id)->orderBy('login_at', 'desc');

            if ($request->filled('status')) {
                if ($request->status === 'success') $query->whereIn('status', ['success', 'expired']);
                elseif ($request->status === 'failed') $query->where('status', 'like', 'failed_%');
                elseif ($request->status === 'active') $query->where('status', 'active');
            }
            if ($request->filled('device'))    $query->where('device_type', $request->device);
            if ($request->filled('date_from')) $query->where('login_at', '>=', $request->date_from);
            if ($request->filled('date_to'))   $query->where('login_at', '<=', $request->date_to . ' 23:59:59');
            if ($request->filled('ip'))        $query->where('ip_address', 'like', '%' . $request->ip . '%');

            $logs = $query->paginate(20)->appends($request->query());

            $base = AdminLog::where('admin_id', $admin->id);
            $logStats = [
                'total'   => (clone $base)->count(),
                'success' => (clone $base)->whereIn('status', ['success', 'expired'])->count(),
                'failed'  => (clone $base)->where('status', 'like', 'failed_%')->count(),
                'active'  => (clone $base)->where('status', 'active')->count(),
            ];
        }

        // ── Permissions tab data ──
        $userOverrides = [];
        $menuGroups = collect();
        $menus = collect();
        $rolePerms = collect();

        if ($activeTab === 'permissions') {
            $menuGroups = AdminMenuGroup::where('is_active', 1)->orderBy('sort_order')->get();
            $menus = AdminMenu::where('is_active', 1)->orderBy('group_id')->orderBy('sort_order')->get();

            $rolePerms = AdminRoleMenuAccess::where('role_id', $admin->role_id)
                ->get()
                ->keyBy('menu_id');

            $userOverrides = AdminUserMenuAccess::where('admin_id', $admin->id)
                ->get()
                ->keyBy('menu_id');
        }

        return view('admin.pages.users.edit', compact(
            'admin', 'roles', 'activeTab', 'logs', 'logStats',
            'menuGroups', 'menus', 'rolePerms', 'userOverrides'
        ));
    }

    public function update(UpdateAdminRequest $request, $token)
    {
        $admin = Admin::findByTokenOrFail($token);

        $admin->name     = $request->name;
        $admin->email    = $request->email;
        $admin->username = $request->username;
        $admin->role_id  = $request->role_id;
        $admin->is_active = $request->boolean('is_active') ? 1 : 0;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        $tab = $request->get('_tab', 'profile');
        return redirect()->route('admin.users.edit', ['token' => $admin->getRouteToken(), 'tab' => $tab])
            ->with('success', 'Admin updated successfully.');
    }

    public function destroy(Request $request, $token)
    {
        $admin = Admin::findByTokenOrFail($token);
        $currentAdmin = $request->attributes->get('admin');

        if ($admin->id == $currentAdmin->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }

        $admin->delete();

        return redirect()->route('admin.users.index')->with('success', 'Admin deleted successfully.');
    }

    public function toggleStatus(Request $request, $token)
    {
        $admin = Admin::findByTokenOrFail($token);
        $currentAdmin = $request->attributes->get('admin');

        if ($admin->id == $currentAdmin->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot deactivate yourself.');
        }

        $admin->is_active = $admin->is_active ? 0 : 1;
        $admin->save();

        $status = $admin->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.users.index')->with('success', "Admin {$status} successfully.");
    }

    /**
     * Save per-user permission overrides (AJAX).
     */
    public function saveUserPermissions(Request $request, $token)
    {
        $admin = Admin::findByTokenOrFail($token);
        $overrides = $request->input('overrides', []);

        DB::transaction(function () use ($admin, $overrides) {
            // Clear existing overrides
            AdminUserMenuAccess::where('admin_id', $admin->id)->delete();

            // Insert only menus where user has explicit overrides
            foreach ($overrides as $menuId => $perms) {
                AdminUserMenuAccess::create([
                    'admin_id'   => $admin->id,
                    'menu_id'    => (int) $menuId,
                    'can_view'   => !empty($perms['can_view']) ? 1 : 0,
                    'can_create' => !empty($perms['can_create']) ? 1 : 0,
                    'can_edit'   => !empty($perms['can_edit']) ? 1 : 0,
                    'can_delete' => !empty($perms['can_delete']) ? 1 : 0,
                ]);
            }
        });

        // Clear sidebar cache for this user
        \Illuminate\Support\Facades\Cache::forget('sidebar_menu_' . $admin->id);

        return response()->json(['success' => true, 'message' => 'User permissions saved.']);
    }

    /**
     * Reset per-user overrides back to role defaults (AJAX).
     */
    public function resetUserPermissions(Request $request, $token)
    {
        $admin = Admin::findByTokenOrFail($token);

        AdminUserMenuAccess::where('admin_id', $admin->id)->delete();

        \Illuminate\Support\Facades\Cache::forget('sidebar_menu_' . $admin->id);

        return response()->json(['success' => true, 'message' => 'Permissions reset to role defaults.']);
    }
}
