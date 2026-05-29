<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\AdminMenu;
use App\Models\Changelog;
use App\Models\BackupRun;
use App\Models\DatabaseConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $adminId = $request->cookie('admin_id');
        $admin = Admin::with('role')->find($adminId);

        $totalAdmins = Admin::count();
        $totalRoles = AdminRole::count();
        $totalMenus = AdminMenu::count();
        $totalChangelogs = Changelog::count();

        $dbName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLE STATUS');
        $totalTables = count($tables);
        $totalRows = 0;
        $totalDbSize = 0;
        foreach ($tables as $t) {
            $totalRows += $t->Rows ?? 0;
            $totalDbSize += ($t->Data_length ?? 0) + ($t->Index_length ?? 0);
        }

        $savedConnections = 0;
        try { $savedConnections = DatabaseConnection::count(); } catch (\Exception $e) {}

        $recentBackups = BackupRun::orderBy('created_at', 'desc')->limit(5)->get();
        $lastBackup = $recentBackups->first();
        $recentChangelogs = Changelog::orderBy('created_at', 'desc')->limit(5)->get();

        $phpVersion = phpversion();
        $laravelVersion = app()->version();
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

        return view('admin.pages.dashboard', compact(
            'admin', 'totalAdmins', 'totalRoles', 'totalMenus', 'totalChangelogs',
            'dbName', 'totalTables', 'totalRows', 'totalDbSize', 'savedConnections',
            'recentBackups', 'lastBackup', 'recentChangelogs',
            'phpVersion', 'laravelVersion', 'serverSoftware'
        ));
    }
}
