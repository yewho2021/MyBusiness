<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $admins = Admin::with('role')->orderBy('id', 'desc')->get();
        $roles = AdminRole::where('is_active', 1)->get();

        return view('admin.pages.users.index', [
            'admins' => $admins,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:tbl_admin,email',
            'username' => 'required|string|max:50|unique:tbl_admin,username',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:tbl_admin_roles,id',
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Admin created successfully.');
    }

    public function edit(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $roles = AdminRole::where('is_active', 1)->get();

        return view('admin.pages.users.edit', [
            'admin' => $admin,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:tbl_admin,email,' . $id,
            'username' => 'required|string|max:50|unique:tbl_admin,username,' . $id,
            'role_id' => 'required|exists:tbl_admin_roles,id',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->username = $request->username;
        $admin->role_id = $request->role_id;
        $admin->is_active = $request->has('is_active') ? 1 : 0;

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:6|confirmed',
            ]);
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->route('admin.users.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $currentAdminId = $request->cookie('admin_id');
        if ($admin->id == $currentAdminId) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }

        $admin->delete();

        return redirect()->route('admin.users.index')->with('success', 'Admin deleted successfully.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $currentAdminId = $request->cookie('admin_id');
        if ($admin->id == $currentAdminId) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot deactivate yourself.');
        }

        $admin->is_active = $admin->is_active ? 0 : 1;
        $admin->save();

        $status = $admin->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.users.index')->with('success', "Admin {$status} successfully.");
    }
}