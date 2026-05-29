<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Exports\ActivityLogExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * List activity log with filters and stats
     */
    public function index(Request $request)
    {
        $query = Activity::query()
            ->orderBy('created_at', 'desc');

        // ── Filters ──

        // Model type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Action / event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Causer (admin)
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        // Log name
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Search (description or properties)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('properties', 'like', "%{$s}%")
                  ->orWhere('subject_type', 'like', "%{$s}%");
            });
        }

        $logs = $query->paginate(25)->appends($request->query());

        // ── Stats ──
        $stats = [
            'total'        => Activity::count(),
            'today'        => Activity::whereDate('created_at', today())->count(),
            'this_week'    => Activity::where('created_at', '>=', now()->startOfWeek())->count(),
            'creates'      => Activity::where('event', 'created')->count(),
            'updates'      => Activity::where('event', 'updated')->count(),
            'deletes'      => Activity::where('event', 'deleted')->count(),
        ];

        // Most active admin (top causer)
        $topCauser = Activity::selectRaw('causer_id, COUNT(*) as cnt')
            ->whereNotNull('causer_id')
            ->groupBy('causer_id')
            ->orderByDesc('cnt')
            ->first();

        $stats['top_admin'] = null;
        if ($topCauser && $topCauser->causer_id) {
            $admin = Admin::find($topCauser->causer_id);
            $stats['top_admin'] = $admin ? $admin->name : 'Unknown';
            $stats['top_admin_count'] = $topCauser->cnt;
        }

        // Available subject types for filter dropdown
        $subjectTypes = Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                return [
                    'full'  => $type,
                    'short' => class_basename($type),
                ];
            });

        // Available log names
        $logNames = Activity::select('log_name')
            ->distinct()
            ->whereNotNull('log_name')
            ->pluck('log_name');

        // Admin list for filter
        $admins = Admin::select('id', 'name', 'username')->orderBy('name')->get();

        return view('admin.pages.activity-log.index', compact(
            'logs', 'stats', 'subjectTypes', 'logNames', 'admins'
        ));
    }

    /**
     * AJAX: Get detail of a single activity (old vs new diff)
     */
    public function show(Request $request, $id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        $properties = $activity->properties->toArray();
        $old = $properties['old'] ?? [];
        $attributes = $properties['attributes'] ?? [];

        // Build diff
        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($old), array_keys($attributes)));
        sort($allKeys);

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $attributes[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changes[] = [
                    'field' => $key,
                    'old'   => $this->formatValue($oldVal),
                    'new'   => $this->formatValue($newVal),
                ];
            }
        }

        // Causer info
        $causer = null;
        if ($activity->causer_id) {
            $admin = Admin::find($activity->causer_id);
            $causer = $admin ? ['name' => $admin->name, 'username' => $admin->username] : null;
        }

        return response()->json([
            'success'      => true,
            'id'           => $activity->id,
            'log_name'     => $activity->log_name,
            'description'  => $activity->description,
            'event'        => $activity->event,
            'subject_type' => $activity->subject_type ? class_basename($activity->subject_type) : null,
            'subject_id'   => $activity->subject_id,
            'causer'       => $causer,
            'changes'      => $changes,
            'properties'   => $properties,
            'batch_uuid'   => $activity->batch_uuid,
            'created_at'   => $activity->created_at?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Export to Excel
     */
    public function export(Request $request)
    {
        $filename = 'activity_log_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new ActivityLogExport($request), $filename);
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $logs = $query->limit(500)->get();

        $adminId = $request->attributes->get('admin_id');
        $admin = $request->attributes->get('admin');

        $pdf = Pdf::loadView('admin.pages.activity-log.pdf', [
            'logs'       => $logs,
            'admin'      => $admin,
            'filters'    => $request->only(['subject_type', 'event', 'causer_id', 'date_from', 'date_to']),
            'generated'  => now()->format('Y-m-d H:i:s'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('activity_log_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * Purge old activity log entries
     */
    public function purge(Request $request)
    {
        $request->validate(['days' => 'required|integer|min:30']);

        $cutoff = now()->subDays($request->days);
        $deleted = Activity::where('created_at', '<', $cutoff)->delete();

        return redirect()->route('admin.activity-log.index')
            ->with('success', "Purged {$deleted} activity log entries older than {$request->days} days.");
    }

    /**
     * Build filtered query (reusable for export)
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = Activity::query()->orderBy('created_at', 'desc');

        if ($request->filled('subject_type')) $query->where('subject_type', $request->subject_type);
        if ($request->filled('event'))        $query->where('event', $request->event);
        if ($request->filled('causer_id'))    $query->where('causer_id', $request->causer_id);
        if ($request->filled('log_name'))     $query->where('log_name', $request->log_name);
        if ($request->filled('date_from'))    $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        if ($request->filled('date_to'))      $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('properties', 'like', "%{$s}%");
            });
        }

        return $query;
    }

    /**
     * Format a value for display in the diff view
     */
    private function formatValue($value): string
    {
        if (is_null($value)) return '—';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_array($value) || is_object($value)) return json_encode($value, JSON_PRETTY_PRINT);
        return (string) $value;
    }
}
