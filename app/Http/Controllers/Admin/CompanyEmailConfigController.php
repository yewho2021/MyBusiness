<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyEmailConfig;
use Illuminate\Http\Request;

class CompanyEmailConfigController extends Controller
{
    public function index()
    {
        $configs = CompanyEmailConfig::with('company')
            ->withCount('templates')
            ->orderByRaw('company_id IS NOT NULL, company_id')
            ->orderBy('name')
            ->get();

        return view('admin.pages.email-configs.index', [
            'configs' => $configs,
        ]);
    }

    public function create()
    {
        $companies = Company::active()->orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.pages.email-configs.create', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'nullable|exists:tbl_company,id',
            'name' => 'required|string|max:100',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'encryption' => 'required|in:tls,ssl,none',
            'from_name' => 'required|string|max:100',
            'from_email' => 'required|email|max:150',
            'reply_to' => 'nullable|email|max:150',
        ]);

        CompanyEmailConfig::create($request->only([
            'company_id', 'name', 'host', 'port', 'username', 'password',
            'encryption', 'from_name', 'from_email', 'reply_to',
        ]) + ['status' => 'active']);

        return redirect()->route('admin.email-configs.index')->with('success', 'SMTP config created successfully.');
    }

    public function edit($id)
    {
        $config = CompanyEmailConfig::findOrFail($id);
        $companies = Company::active()->orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.pages.email-configs.edit', [
            'config' => $config,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, $id)
    {
        $config = CompanyEmailConfig::findOrFail($id);

        $rules = [
            'company_id' => 'nullable|exists:tbl_company,id',
            'name' => 'required|string|max:100',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'encryption' => 'required|in:tls,ssl,none',
            'from_name' => 'required|string|max:100',
            'from_email' => 'required|email|max:150',
            'reply_to' => 'nullable|email|max:150',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|max:255';
        }

        $request->validate($rules);

        $config->fill($request->only([
            'company_id', 'name', 'host', 'port', 'username',
            'encryption', 'from_name', 'from_email', 'reply_to',
        ]));

        if ($request->filled('password')) {
            $config->password = $request->password;
        }

        $config->save();

        return redirect()->route('admin.email-configs.index')->with('success', 'SMTP config updated successfully.');
    }

    public function destroy($id)
    {
        $config = CompanyEmailConfig::withCount('templates')->findOrFail($id);

        if ($config->templates_count > 0) {
            return redirect()->route('admin.email-configs.index')
                ->with('error', "Cannot delete. {$config->templates_count} template(s) use this SMTP config.");
        }

        $config->delete();

        return redirect()->route('admin.email-configs.index')->with('success', 'SMTP config deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $config = CompanyEmailConfig::findOrFail($id);
        $config->status = $config->status === 'active' ? 'inactive' : 'active';
        $config->save();

        $status = $config->status === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('admin.email-configs.index')->with('success', "SMTP config {$status}.");
    }
}
