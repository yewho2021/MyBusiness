<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Check cookie
        $adminId = $request->cookie('admin_id');
        if ($adminId) {
            $admin = Admin::find($adminId);
            if ($admin && $admin->is_active == 1) {
                return redirect()->route('admin.dashboard');
            }
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        $admin = Admin::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$admin) {
            return back()->withErrors([
                'username' => 'User not found.',
            ])->withInput($request->only('username'));
        }

        if ($admin->is_active != 1) {
            return back()->withErrors([
                'username' => 'Account deactivated.',
            ])->withInput($request->only('username'));
        }

        if (!Hash::check($password, $admin->password)) {
            return back()->withErrors([
                'username' => 'Wrong password.',
            ])->withInput($request->only('username'));
        }

        // Update last login
        $admin->datetime_lastlogin = now();
        $admin->save();

        // Set cookie for 7 days (10080 minutes)
        $cookie = Cookie::make('admin_id', $admin->id, 10080, '/', null, false, true);

        return redirect()->route('admin.dashboard')->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $cookie = Cookie::forget('admin_id');
        return redirect()->route('admin.login')->withCookie($cookie);
    }
}
