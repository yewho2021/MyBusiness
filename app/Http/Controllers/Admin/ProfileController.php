<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Admin;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->attributes->get('admin');
        $recentLogins = AdminLog::where('admin_id', $admin->id)
            ->whereIn('status', ['active', 'success', 'expired'])
            ->orderBy('login_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.pages.profile.index', compact('admin', 'recentLogins'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $admin = $request->attributes->get('admin');
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Re-fetch to avoid stale state
        $admin = Admin::with('role')->findOrFail($admin->id);

        $admin->name  = $request->name;
        $admin->email = $request->email;

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->with('error', 'Current password is incorrect.');
            }
            $admin->password = Hash::make($request->password);
            $admin->password_changed_at = now();
        }

        if ($request->has('timezone')) {
            $admin->timezone = $request->input('timezone') ?: null; // empty = use portal default
        }

        $admin->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
