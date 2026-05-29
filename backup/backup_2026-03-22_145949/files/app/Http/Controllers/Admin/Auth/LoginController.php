<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminLog;
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

        $admin = Admin::with('role')
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        // Failed: user not found
        if (!$admin) {
            AdminLog::record($request, 'failed_not_found', null, $username, 'User not found');
            return back()->withErrors([
                'username' => 'User not found.',
            ])->withInput($request->only('username'));
        }

        // Failed: account inactive
        if ($admin->is_active != 1) {
            AdminLog::record($request, 'failed_inactive', $admin, $username, 'Account deactivated');
            return back()->withErrors([
                'username' => 'Account deactivated.',
            ])->withInput($request->only('username'));
        }

        // Failed: wrong password
        if (!Hash::check($password, $admin->password)) {
            AdminLog::record($request, 'failed_password', $admin, $username, 'Incorrect password');
            return back()->withErrors([
                'username' => 'Wrong password.',
            ])->withInput($request->only('username'));
        }

        // Success — record session
        $admin->datetime_lastlogin = now();
        $admin->save();

        $sessionId = AdminLog::generateSessionId();
        AdminLog::record($request, 'active', $admin, $username, null, $sessionId);

        $cookie  = Cookie::make('admin_id', $admin->id, 10080, '/', null, false, true);
        $sCookie = Cookie::make('admin_session_id', $sessionId, 10080, '/', null, false, true);

        return redirect()->route('admin.dashboard')
            ->withCookie($cookie)
            ->withCookie($sCookie);
    }

    public function logout(Request $request)
    {
        // Close the active session log entry
        $sessionId = $request->cookie('admin_session_id');
        if ($sessionId) {
            $log = AdminLog::where('session_id', $sessionId)
                ->where('status', 'active')
                ->first();
            if ($log) {
                $log->closeSession('manual');
            }
        }

        $cookie  = Cookie::forget('admin_id');
        $sCookie = Cookie::forget('admin_session_id');

        return redirect()->route('admin.login')
            ->withCookie($cookie)
            ->withCookie($sCookie);
    }
}
