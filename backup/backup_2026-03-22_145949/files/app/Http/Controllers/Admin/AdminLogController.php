<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Models\AdminRole;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminLog::query()->orderBy('login_at', 'desc');

        // ── Filters ──

        // Status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'failed') {
                $query->where('status', 'like', 'failed_%');
            } else {
                $query->where('status', $status);
            }
        }

        // Admin user
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // Role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // IP address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->where('login_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('login_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Device type
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Free-text search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('admin_username', 'like', "%{$s}%")
                  ->orWhere('admin_name', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhere('browser', 'like', "%{$s}%")
                  ->orWhere('ip_country', 'like', "%{$s}%")
                  ->orWhere('ip_city', 'like', "%{$s}%");
            });
        }

        $logs = $query->paginate(25)->appends($request->query());

        // Filter dropdown data
        $admins = Admin::select('id', 'name', 'username')->orderBy('name')->get();
        $roles  = AdminRole::select('id', 'name')->orderBy('name')->get();

        // ── Summary stats (respect active filters) ──
        $statsBase = AdminLog::query();
        if ($request->filled('date_from'))  $statsBase->where('login_at', '>=', $request->date_from . ' 00:00:00');
        if ($request->filled('date_to'))    $statsBase->where('login_at', '<=', $request->date_to . ' 23:59:59');
        if ($request->filled('admin_id'))   $statsBase->where('admin_id', $request->admin_id);
        if ($request->filled('role_id'))    $statsBase->where('role_id', $request->role_id);

        $stats = [
            'total'      => (clone $statsBase)->count(),
            'success'    => (clone $statsBase)->whereIn('status', ['success', 'active', 'expired'])->count(),
            'failed'     => (clone $statsBase)->where('status', 'like', 'failed_%')->count(),
            'active_now' => AdminLog::where('status', 'active')->count(),
            'unique_ips' => (clone $statsBase)->distinct('ip_address')->count('ip_address'),
        ];

        return view('admin.pages.admin-log.index', compact('logs', 'admins', 'roles', 'stats'));
    }

    /**
     * AJAX: session detail for modal
     */
    public function show(Request $request, $id)
    {
        $log = AdminLog::findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id'              => $log->id,
                'session_id'      => $log->session_id,
                'admin_name'      => $log->admin_name,
                'admin_username'  => $log->admin_username,
                'role_name'       => $log->role_name,
                'status'          => $log->status,
                'ip_address'      => $log->ip_address,
                'ip_country'      => $log->ip_country,
                'ip_city'         => $log->ip_city,
                'ip_isp'          => $log->ip_isp,
                'user_agent'      => $log->user_agent,
                'browser'         => $log->browser,
                'platform'        => $log->platform,
                'device_type'     => $log->device_type,
                'login_at'        => $log->login_at?->format('d M Y, H:i:s'),
                'logout_at'       => $log->logout_at?->format('d M Y, H:i:s'),
                'duration'        => $log->formatted_duration,
                'logout_type'     => $log->logout_type,
                'fail_reason'     => $log->fail_reason,
            ]);
        }

        return redirect()->route('admin.admin-log.index');
    }

    /**
     * Force-close an active session (kick user)
     */
    public function kick(Request $request, $id)
    {
        $log = AdminLog::where('id', $id)->where('status', 'active')->firstOrFail();
        $log->closeSession('kicked');

        return redirect()->route('admin.admin-log.index')
            ->with('success', "Session for {$log->admin_name} has been terminated.");
    }

    /**
     * Export filtered logs as CSV
     */
    public function export(Request $request)
    {
        $query = AdminLog::query()->orderBy('login_at', 'desc');

        // Apply same filters
        if ($request->filled('status')) {
            $status = $request->status;
            $status === 'failed'
                ? $query->where('status', 'like', 'failed_%')
                : $query->where('status', $status);
        }
        if ($request->filled('admin_id'))    $query->where('admin_id', $request->admin_id);
        if ($request->filled('role_id'))     $query->where('role_id', $request->role_id);
        if ($request->filled('ip_address'))  $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        if ($request->filled('date_from'))   $query->where('login_at', '>=', $request->date_from . ' 00:00:00');
        if ($request->filled('date_to'))     $query->where('login_at', '<=', $request->date_to . ' 23:59:59');
        if ($request->filled('device_type')) $query->where('device_type', $request->device_type);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('admin_username', 'like', "%{$s}%")
                  ->orWhere('admin_name', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        $logs = $query->limit(5000)->get();

        $filename = 'admin_login_log_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Session ID', 'Admin', 'Username', 'Role', 'Status',
                'IP', 'Country', 'City', 'ISP',
                'Browser', 'Platform', 'Device',
                'Login At', 'Logout At', 'Duration', 'Logout Type', 'Fail Reason',
            ]);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id, $log->session_id,
                    $log->admin_name, $log->admin_username, $log->role_name,
                    $log->status, $log->ip_address, $log->ip_country,
                    $log->ip_city, $log->ip_isp, $log->browser,
                    $log->platform, $log->device_type,
                    $log->login_at?->format('Y-m-d H:i:s'),
                    $log->logout_at?->format('Y-m-d H:i:s'),
                    $log->formatted_duration, $log->logout_type,
                    $log->fail_reason,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Purge old log entries
     */
    public function purge(Request $request)
    {
        $request->validate(['days' => 'required|integer|min:30']);

        $cutoff  = now()->subDays($request->days);
        $deleted = AdminLog::where('login_at', '<', $cutoff)
            ->where('status', '!=', 'active')
            ->delete();

        return redirect()->route('admin.admin-log.index')
            ->with('success', "Purged {$deleted} log entries older than {$request->days} days.");
    }
}
