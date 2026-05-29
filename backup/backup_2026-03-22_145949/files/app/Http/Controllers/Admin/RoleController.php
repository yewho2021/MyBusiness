<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = AdminRole::withCount('admins')->orderBy('level', 'asc')->get();

        return view('admin.pages.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:50|unique:tbl_admin_roles,slug',
            'description' => 'nullable|string|max:255',
            'level' => 'required|integer|min:1|max:99',
        ]);

        AdminRole::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'level' => $request->level,
            'is_active' => $request->has('is_active') ? 1 : 0,
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

    public function update(Request $request, $id)
    {
        $role = AdminRole::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:50|unique:tbl_admin_roles,slug,' . $id,
            'description' => 'nullable|string|max:255',
            'level' => 'required|integer|min:1|max:99',
        ]);

        $role->name = $request->name;
        $role->slug = $request->slug;
        $role->description = $request->description;
        $role->level = $request->level;
        $role->is_active = $request->has('is_active') ? 1 : 0;
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

        $status = $role->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.roles.index')->with('success', "Role {$status} successfully.");
    }
}