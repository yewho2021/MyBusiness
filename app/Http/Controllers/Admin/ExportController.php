<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\GenericExport;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Models\AdminRole;
use App\Models\BackupRun;
use App\Models\Changelog;
use App\Models\Configuration;
use App\Models\ExportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export Center dashboard
     */
    public function index(Request $request)
    {
        $history = ExportHistory::orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $sources = $this->getSourceList();

        return view('admin.pages.export.index', compact('history', 'sources'));
    }

    /**
     * Preview data (AJAX — returns first 10 rows)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'source' => 'required|string',
        ]);

        try {
            $result = $this->querySource($request->source, $request->all(), 10);
            return response()->json([
                'success'  => true,
                'headings' => $result['headings'],
                'rows'     => $result['data']->toArray(),
                'total'    => $result['total'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generate export (Excel, CSV, or PDF)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'source' => 'required|string',
            'format' => 'required|in:xlsx,csv,pdf',
        ]);

        $source = $request->source;
        $format = $request->format;
        $adminId = $request->attributes->get('admin_id');

        try {
            $result = $this->querySource($source, $request->all(), 5000);
            $data = $result['data'];
            $headings = $result['headings'];
            $title = $result['title'];
            $total = $result['total'];

            $filename = str_replace(' ', '_', strtolower($title)) . '_' . date('Y-m-d_His');

            if ($format === 'pdf') {
                $pdf = Pdf::loadView('admin.pages.export.pdf', [
                    'title'      => $title,
                    'headings'   => $headings,
                    'rows'       => $data,
                    'total'      => $total,
                    'filters'    => $request->except(['_token', 'source', 'format']),
                    'generated'  => now()->format('Y-m-d H:i:s'),
                    'portalName' => Configuration::get('portal_name', config('app.name', 'Admin Portal')),
                ])->setPaper('a4', count($headings) > 6 ? 'landscape' : 'portrait');

                // Save to storage for history
                $pdfContent = $pdf->output();
                $filePath = 'exports/' . $filename . '.pdf';
                $fullPath = storage_path('app/' . $filePath);
                if (!is_dir(dirname($fullPath))) mkdir(dirname($fullPath), 0775, true);
                $written = file_put_contents($fullPath, $pdfContent);
                if ($written === false) {
                    return back()->with('error', 'Failed to save export file to disk.');
                }

                // Log history
                ExportHistory::create([
                    'source'    => $source,
                    'format'    => 'pdf',
                    'file_path' => $filePath,
                    'file_name' => $filename . '.pdf',
                    'file_size' => strlen($pdfContent),
                    'filters'   => $request->except(['_token', 'source', 'format']),
                    'row_count' => $data->count(),
                    'admin_id'  => $adminId,
                ]);

                return $pdf->download($filename . '.pdf');
            }

            // Excel or CSV
            $ext = $format === 'csv' ? 'csv' : 'xlsx';
            $export = new GenericExport($data, $headings, $title);

            // Save to storage for history
            $filePath = 'exports/' . $filename . '.' . $ext;
            $fullPath = storage_path('app/' . $filePath);
            if (!is_dir(dirname($fullPath))) mkdir(dirname($fullPath), 0775, true);

            Excel::store($export, $filePath, 'local');

            $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;

            ExportHistory::create([
                'source'    => $source,
                'format'    => $format,
                'file_path' => $filePath,
                'file_name' => $filename . '.' . $ext,
                'file_size' => $fileSize,
                'filters'   => $request->except(['_token', 'source', 'format']),
                'row_count' => $data->count(),
                'admin_id'  => $adminId,
            ]);

            return Excel::download($export, $filename . '.' . $ext);

        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Re-download a previous export
     */
    public function download($id)
    {
        $export = ExportHistory::findOrFail($id);
        $path = storage_path('app/' . $export->file_path);

        if (!file_exists($path)) {
            return back()->with('error', 'Export file no longer exists on disk.');
        }

        return response()->download($path, $export->file_name);
    }

    /**
     * Delete an export history entry + file
     */
    public function destroy($id)
    {
        $export = ExportHistory::findOrFail($id);

        $path = storage_path('app/' . $export->file_path);
        if (file_exists($path)) {
            @unlink($path);
        }

        $export->delete();

        return response()->json([
            'success' => true,
            'message' => 'Export deleted.',
        ]);
    }

    /**
     * Clear all export history
     */
    public function clearHistory(Request $request)
    {
        ExportHistory::chunkById(200, function ($exports) {
            foreach ($exports as $export) {
                $path = storage_path('app/' . $export->file_path);
                if (file_exists($path)) @unlink($path);
            }
        });
        ExportHistory::truncate();

        return redirect()->route('admin.export.index')
            ->with('success', 'Export history cleared.');
    }

    // ══════════════════════════════════════════════════
    // DATA SOURCES
    // ══════════════════════════════════════════════════

    private function getSourceList(): array
    {
        return [
            'admin_users'    => ['label' => 'Admin Users', 'icon' => 'fa-users', 'color' => 'blue', 'desc' => 'All admin accounts with roles and status'],
            'login_log'      => ['label' => 'Login Log', 'icon' => 'fa-user-shield', 'color' => 'green', 'desc' => 'Login activity with IP, browser, duration'],
            'activity_log'   => ['label' => 'Activity Log', 'icon' => 'fa-shoe-prints', 'color' => 'purple', 'desc' => 'Model changes with old/new values'],
            'configuration'  => ['label' => 'Configuration', 'icon' => 'fa-cogs', 'color' => 'amber', 'desc' => 'All system configuration keys and values'],
            'backup_history' => ['label' => 'Backup History', 'icon' => 'fa-database', 'color' => 'red', 'desc' => 'Backup runs with status and sizes'],
            'changelog'      => ['label' => 'Changelog', 'icon' => 'fa-history', 'color' => 'slate', 'desc' => 'Version history and release notes'],
            'custom_query'   => ['label' => 'Custom SQL', 'icon' => 'fa-terminal', 'color' => 'dark', 'desc' => 'Run a SELECT query and export results'],
        ];
    }

    private function querySource(string $source, array $params, int $limit = 5000): array
    {
        return match ($source) {
            'admin_users'    => $this->queryAdminUsers($params, $limit),
            'login_log'      => $this->queryLoginLog($params, $limit),
            'activity_log'   => $this->queryActivityLog($params, $limit),
            'configuration'  => $this->queryConfiguration($params, $limit),
            'backup_history' => $this->queryBackupHistory($params, $limit),
            'changelog'      => $this->queryChangelog($params, $limit),
            'custom_query'   => $this->queryCustom($params, $limit),
            default          => throw new \Exception("Unknown source: {$source}"),
        };
    }

    private function queryAdminUsers(array $params, int $limit): array
    {
        $query = Admin::with('role')->orderBy('name');
        if (!empty($params['role_id'])) $query->where('role_id', $params['role_id']);
        if (isset($params['status']) && $params['status'] !== '') $query->where('is_active', $params['status']);

        $total = $query->count();
        $rows = $query->limit($limit)->get();

        $data = $rows->map(fn($u) => [
            $u->id, $u->name, $u->username, $u->email,
            $u->role->name ?? '—', $u->twofa_enabled ? 'Yes' : 'No',
            $u->is_active ? 'Active' : 'Inactive',
            $u->datetime_lastlogin?->format('Y-m-d H:i:s') ?? '—',
        ]);

        return [
            'headings' => ['ID', 'Name', 'Username', 'Email', 'Role', '2FA', 'Status', 'Last Login'],
            'data'     => $data,
            'title'    => 'Admin Users',
            'total'    => $total,
        ];
    }

    private function queryLoginLog(array $params, int $limit): array
    {
        $query = AdminLog::orderBy('login_at', 'desc');
        if (!empty($params['status'])) {
            $params['status'] === 'failed'
                ? $query->where('status', 'like', 'failed_%')
                : $query->where('status', $params['status']);
        }
        if (!empty($params['admin_id'])) $query->where('admin_id', $params['admin_id']);
        if (!empty($params['date_from'])) $query->where('login_at', '>=', $params['date_from'] . ' 00:00:00');
        if (!empty($params['date_to'])) $query->where('login_at', '<=', $params['date_to'] . ' 23:59:59');

        $total = $query->count();
        $rows = $query->limit($limit)->get();

        $data = $rows->map(fn($l) => [
            $l->id, $l->admin_name ?? '—', $l->admin_username ?? '—',
            $l->role_name ?? '—', $l->status, $l->ip_address,
            $l->ip_country ?? '—', $l->browser ?? '—', $l->device_type ?? '—',
            $l->login_at?->format('Y-m-d H:i:s'), $l->logout_at?->format('Y-m-d H:i:s'),
        ]);

        return [
            'headings' => ['ID', 'Admin', 'Username', 'Role', 'Status', 'IP', 'Country', 'Browser', 'Device', 'Login At', 'Logout At'],
            'data'     => $data,
            'title'    => 'Login Activity Log',
            'total'    => $total,
        ];
    }

    private function queryActivityLog(array $params, int $limit): array
    {
        $query = DB::table('tbl_activity_log')->orderBy('created_at', 'desc');
        if (!empty($params['event'])) $query->where('event', $params['event']);
        if (!empty($params['date_from'])) $query->where('created_at', '>=', $params['date_from'] . ' 00:00:00');
        if (!empty($params['date_to'])) $query->where('created_at', '<=', $params['date_to'] . ' 23:59:59');

        $total = $query->count();
        $rows = $query->limit($limit)->get();

        $data = collect($rows)->map(fn($a) => [
            $a->id, $a->log_name ?? '—', $a->event ?? '—',
            $a->subject_type ? class_basename($a->subject_type) : '—',
            $a->subject_id ?? '—', $a->description ?? '—',
            $a->causer_id ?? '—',
            $a->created_at,
        ]);

        return [
            'headings' => ['ID', 'Log', 'Event', 'Model', 'Model ID', 'Description', 'Admin ID', 'Date'],
            'data'     => $data,
            'title'    => 'Activity Log',
            'total'    => $total,
        ];
    }

    private function queryConfiguration(array $params, int $limit): array
    {
        $query = Configuration::where('is_active', 1)->orderBy('group')->orderBy('sort_order');
        if (!empty($params['group'])) $query->where('group', $params['group']);

        $total = $query->count();
        $rows = $query->limit($limit)->get();

        $data = $rows->map(fn($c) => [
            $c->id, $c->group, $c->key, $c->label,
            $c->type, \Illuminate\Support\Str::limit($c->value ?? $c->default_value, 100),
            \Illuminate\Support\Str::limit($c->default_value, 60),
        ]);

        return [
            'headings' => ['ID', 'Group', 'Key', 'Label', 'Type', 'Value', 'Default'],
            'data'     => $data,
            'title'    => 'System Configuration',
            'total'    => $total,
        ];
    }

    private function queryBackupHistory(array $params, int $limit): array
    {
        $query = BackupRun::orderBy('created_at', 'desc');
        if (!empty($params['status'])) $query->where('status', $params['status']);
        if (!empty($params['date_from'])) $query->where('created_at', '>=', $params['date_from'] . ' 00:00:00');
        if (!empty($params['date_to'])) $query->where('created_at', '<=', $params['date_to'] . ' 23:59:59');

        $total = $query->count();
        $rows = $query->limit($limit)->get();

        $data = $rows->map(function ($r) {
            $sz = $r->total_size;
            if ($sz >= 1048576) $sizeStr = round($sz / 1048576, 1) . ' MB';
            elseif ($sz >= 1024) $sizeStr = round($sz / 1024, 1) . ' KB';
            else $sizeStr = $sz . ' B';
            return [
                $r->id, $r->folder_name ?? '—', $r->status,
                $r->processed_files . '/' . $r->total_files, $sizeStr,
                $r->include_database ? 'Yes' : 'No',
                $r->started_at?->format('Y-m-d H:i'), $r->completed_at?->format('Y-m-d H:i'),
            ];
        });

        return [
            'headings' => ['ID', 'Folder', 'Status', 'Files', 'Size', 'DB Included', 'Started', 'Completed'],
            'data'     => $data,
            'title'    => 'Backup History',
            'total'    => $total,
        ];
    }

    private function queryChangelog(array $params, int $limit): array
    {
        $query = Changelog::where('app_type', 'office')->orderBy('created_at', 'desc');

        $total = $query->count();
        $rows = $query->limit($limit)->get();

        $data = $rows->map(fn($c) => [
            $c->id, $c->version, $c->title,
            \Illuminate\Support\Str::limit($c->details, 150),
            is_string($c->created_at) ? $c->created_at : $c->created_at?->format('Y-m-d'),
        ]);

        return [
            'headings' => ['ID', 'Version', 'Title', 'Details', 'Date'],
            'data'     => $data,
            'title'    => 'Changelog',
            'total'    => $total,
        ];
    }

    private function queryCustom(array $params, int $limit): array
    {
        if (empty($params['sql'])) throw new \Exception('SQL query is required.');

        $sql = trim($params['sql']);

        // Safety: only allow SELECT
        if (!preg_match('/^\s*SELECT\s/i', $sql)) {
            throw new \Exception('Only SELECT queries are allowed for export.');
        }

        // Block dangerous keywords
        $blocked = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'TRUNCATE', 'CREATE', 'GRANT', 'REVOKE'];
        foreach ($blocked as $kw) {
            if (preg_match('/\b' . $kw . '\b/i', $sql)) {
                throw new \Exception("Query contains blocked keyword: {$kw}");
            }
        }

        // Add LIMIT if not present
        if (!preg_match('/\bLIMIT\b/i', $sql)) {
            $sql .= " LIMIT {$limit}";
        }

        $rows = collect(DB::select($sql));
        $total = $rows->count();

        if ($rows->isEmpty()) {
            return ['headings' => ['No Data'], 'data' => collect(), 'title' => 'Custom Query', 'total' => 0];
        }

        $headings = array_keys((array) $rows->first());
        $data = $rows->map(fn($row) => array_values((array) $row));

        return [
            'headings' => $headings,
            'data'     => $data,
            'title'    => 'Custom Query',
            'total'    => $total,
        ];
    }
}
