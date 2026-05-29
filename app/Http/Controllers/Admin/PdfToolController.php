<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminLog;
use App\Models\AdminRole;
use App\Models\AdminMenu;
use App\Models\AdminMenuGroup;
use App\Models\AdminRoleMenuAccess;
use App\Models\BackupRun;
use App\Models\Changelog;
use App\Models\Configuration;
use App\Models\PdfTemplate;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfToolController extends Controller
{
    public function index(Request $request)
    {
        $templates = PdfTemplate::orderBy('updated_at', 'desc')->get();

        return view('admin.pages.pdf-tools.index', compact('templates'));
    }

    /**
     * Convert raw HTML to PDF
     */
    public function htmlToPdf(Request $request)
    {
        $request->validate([
            'html'        => 'required|string',
            'paper_size'  => 'nullable|in:a4,a3,letter,legal',
            'orientation' => 'nullable|in:portrait,landscape',
        ]);

        $html = $request->input('html');
        $paperSize = $request->input('paper_size', 'a4');
        $orientation = $request->input('orientation', 'portrait');

        // Wrap in basic HTML structure if not already wrapped
        if (!str_contains(strtolower($html), '<html')) {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#1e293b;margin:20px;}</style></head><body>' . $html . '</body></html>';
        }

        $pdf = Pdf::loadHTML($html)
            ->setPaper($paperSize, $orientation);

        return $pdf->download('document_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * Preview HTML as rendered PDF (returns PDF inline)
     */
    public function htmlPreview(Request $request)
    {
        $request->validate(['html' => 'required|string']);

        $html = $request->input('html');
        $paperSize = $request->input('paper_size', 'a4');
        $orientation = $request->input('orientation', 'portrait');

        if (!str_contains(strtolower($html), '<html')) {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#1e293b;margin:20px;}</style></head><body>' . $html . '</body></html>';
        }

        $pdf = Pdf::loadHTML($html)
            ->setPaper($paperSize, $orientation);

        return $pdf->stream('preview.pdf');
    }

    /**
     * Generate a built-in report
     */
    public function reportGenerate(Request $request)
    {
        $request->validate([
            'report'      => 'required|in:admin-users,login-log,configuration,backup-summary,changelog,role-permissions',
            'orientation' => 'nullable|in:portrait,landscape',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date',
        ]);

        $report = $request->input('report');
        $orientation = $request->input('orientation', 'portrait');
        $adminId = $request->attributes->get('admin_id');
        $admin = Admin::find($adminId);
        $portalName = Configuration::get('portal_name', config('app.name', 'Admin Portal'));
        $generated = now()->format('Y-m-d H:i:s');

        $data = compact('admin', 'portalName', 'generated');

        switch ($report) {
            case 'admin-users':
                $data['users'] = Admin::with('role')->orderBy('name')->get();
                $view = 'pdf.admin-users';
                $filename = 'admin_users_report';
                break;

            case 'login-log':
                $query = AdminLog::orderBy('login_at', 'desc');
                if ($request->filled('date_from')) $query->where('login_at', '>=', $request->date_from . ' 00:00:00');
                if ($request->filled('date_to')) $query->where('login_at', '<=', $request->date_to . ' 23:59:59');
                $data['logs'] = $query->limit(500)->get();
                $data['dateFrom'] = $request->date_from;
                $data['dateTo'] = $request->date_to;
                $view = 'pdf.login-log';
                $filename = 'login_activity_report';
                $orientation = 'landscape';
                break;

            case 'configuration':
                $groups = Configuration::where('is_active', 1)
                    ->orderBy('group')
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy('group');
                $data['groups'] = $groups;
                $view = 'pdf.configuration';
                $filename = 'system_configuration';
                break;

            case 'backup-summary':
                $data['runs'] = BackupRun::orderBy('created_at', 'desc')->limit(50)->get();
                $view = 'pdf.backup-summary';
                $filename = 'backup_summary';
                break;

            case 'changelog':
                $data['entries'] = Changelog::where('app_type', 'office')->orderBy('created_at', 'desc')->get();
                $view = 'pdf.changelog';
                $filename = 'changelog_report';
                break;

            case 'role-permissions':
                $data['roles'] = AdminRole::where('is_active', 1)->orderBy('level')->get();
                $data['menuGroups'] = AdminMenuGroup::with(['menus' => function ($q) {
                    $q->where('is_active', 1)->orderBy('sort_order');
                }])->where('is_active', 1)->orderBy('sort_order')->get();
                $data['access'] = AdminRoleMenuAccess::all()->groupBy('role_id');
                $view = 'pdf.role-permissions';
                $filename = 'role_permissions_matrix';
                $orientation = 'landscape';
                break;
        }

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        return $pdf->download($filename . '_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Save a custom template (AJAX)
     */
    public function templateSave(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'html_content' => 'required|string',
            'paper_size'   => 'nullable|in:a4,a3,letter,legal',
            'orientation'  => 'nullable|in:portrait,landscape',
        ]);

        $adminId = $request->attributes->get('admin_id');

        $template = PdfTemplate::updateOrCreate(
            ['id' => $request->input('id')],
            [
                'name'         => $request->name,
                'description'  => $request->description,
                'html_content' => $request->html_content,
                'paper_size'   => $request->input('paper_size', 'a4'),
                'orientation'  => $request->input('orientation', 'portrait'),
                'created_by'   => $adminId,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully.',
            'template' => $template,
        ]);
    }

    /**
     * Load template content (AJAX)
     */
    public function templateLoad($id)
    {
        $template = PdfTemplate::find($id);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        return response()->json([
            'success'  => true,
            'template' => $template,
        ]);
    }

    /**
     * Delete template (AJAX)
     */
    public function templateDelete($id)
    {
        $template = PdfTemplate::find($id);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully.',
        ]);
    }
}
