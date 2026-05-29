<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyEmailConfig;
use App\Models\CompanyEmailTemplate;
use Illuminate\Http\Request;

class CompanyEmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = CompanyEmailTemplate::with(['company', 'smtp']);

        if ($request->filled('scope')) {
            if ($request->scope === 'global') {
                $query->global();
            } else {
                $query->where('company_id', $request->scope);
            }
        }

        $templates = $query->orderByRaw('company_id IS NOT NULL, company_id')
            ->orderBy('slug')
            ->get();

        $companies = Company::active()->orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.pages.email-templates.index', [
            'templates' => $templates,
            'companies' => $companies,
            'filters' => $request->only(['scope']),
        ]);
    }

    public function create()
    {
        $companies = Company::active()->orderBy('company_name')->get(['id', 'company_name']);
        $smtpConfigs = CompanyEmailConfig::active()->orderBy('name')->get(['id', 'name', 'company_id']);

        return view('admin.pages.email-templates.create', [
            'companies' => $companies,
            'smtpConfigs' => $smtpConfigs,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'nullable|exists:tbl_company,id',
            'smtp_id' => 'nullable|exists:tbl_company_email_config,id',
            'slug' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'email_to' => 'nullable|string|max:255',
            'email_cc' => 'nullable|string|max:255',
            'email_bcc' => 'nullable|string|max:255',
        ]);

        CompanyEmailTemplate::create($request->only([
            'company_id', 'smtp_id', 'slug', 'name', 'subject', 'content',
            'email_to', 'email_cc', 'email_bcc',
        ]) + ['status' => 'active']);

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template created successfully.');
    }

    public function edit($id)
    {
        $template = CompanyEmailTemplate::findOrFail($id);
        $companies = Company::active()->orderBy('company_name')->get(['id', 'company_name']);
        $smtpConfigs = CompanyEmailConfig::active()->orderBy('name')->get(['id', 'name', 'company_id']);

        return view('admin.pages.email-templates.edit', [
            'template' => $template,
            'companies' => $companies,
            'smtpConfigs' => $smtpConfigs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = CompanyEmailTemplate::findOrFail($id);

        $request->validate([
            'company_id' => 'nullable|exists:tbl_company,id',
            'smtp_id' => 'nullable|exists:tbl_company_email_config,id',
            'slug' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'email_to' => 'nullable|string|max:255',
            'email_cc' => 'nullable|string|max:255',
            'email_bcc' => 'nullable|string|max:255',
        ]);

        $template->fill($request->only([
            'company_id', 'smtp_id', 'slug', 'name', 'subject', 'content',
            'email_to', 'email_cc', 'email_bcc',
        ]));
        $template->save();

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template updated successfully.');
    }

    public function destroy($id)
    {
        $template = CompanyEmailTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $template = CompanyEmailTemplate::findOrFail($id);
        $template->status = $template->status === 'active' ? 'inactive' : 'active';
        $template->save();

        $status = $template->status === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('admin.email-templates.index')->with('success', "Template {$status}.");
    }

    public function preview($id)
    {
        $template = CompanyEmailTemplate::with(['company', 'smtp'])->findOrFail($id);

        return view('admin.pages.email-templates.preview', [
            'template' => $template,
        ]);
    }
}
