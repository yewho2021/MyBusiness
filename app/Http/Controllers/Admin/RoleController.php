<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    /**
     * Clear sidebar menu cache for all roles.
     */
    protected function clearMenuCache(): void
    {
        $roleIds = AdminRole::pluck('id');
        foreach ($roleIds as $id) {
            Cache::forget('sidebar_menu_' . $id);
        }
        Cache::forget('sidebar_menu_guest');
    }
    public function index()
    {
        $roles = AdminRole::withCount('admins')->orderBy('level', 'asc')->get();

        return view('admin.pages.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        AdminRole::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'level' => $request->level,
            'is_active' => $request->boolean('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $role = AdminRole::findOrFail($id);

        return view('admin.pages.roles.edit', [
            'role' => $role,
        ]);
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $role = AdminRole::findOrFail($id);

        $role->name = $request->name;
        $role->slug = $request->slug;
        $role->description = $request->description;
        $role->level = $request->level;
        $role->is_active = $request->boolean('is_active') ? 1 : 0;
        $role->save();

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = AdminRole::withCount('admins')->findOrFail($id);

        if ($role->admins_count > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Cannot delete role. There are ' . $role->admins_count . ' admin(s) using this role.');
        }

        if ($role->slug === 'administrator') {
            return redirect()->route('admin.roles.index')->with('error', 'Cannot delete Administrator role.');
        }

        $role->delete();
        $this->clearMenuCache();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $role = AdminRole::findOrFail($id);

        if ($role->slug === 'administrator') {
            return redirect()->route('admin.roles.index')->with('error', 'Cannot deactivate Administrator role.');
        }

        $role->is_active = $role->is_active ? 0 : 1;
        $role->save();
        $this->clearMenuCache();

        $status = $role->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.roles.index')->with('success', "Role {$status} successfully.");
    }
}