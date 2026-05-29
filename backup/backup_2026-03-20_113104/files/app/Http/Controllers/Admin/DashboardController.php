<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $adminId = $request->cookie('admin_id');
        $admin = Admin::with('role')->find($adminId);

        return view('admin.pages.dashboard', [
            'admin' => $admin,
        ]);
    }
}
