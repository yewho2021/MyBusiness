<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyAgreement;
use Illuminate\Http\Request;

class CompanyAgreementController extends Controller
{
    public function index()
    {
        $agreements = CompanyAgreement::withCount('companies')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pages.company-agreements.index', [
            'agreements' => $agreements,
        ]);
    }

    public function create()
    {
        $latestVersion = CompanyAgreement::orderBy('created_at', 'desc')->value('version');

        return view('admin.pages.company-agreements.create', [
            'latestVersion' => $latestVersion,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'version' => 'required|string|max:20|unique:tbl_company_agreement,version',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($request->boolean('is_active')) {
            CompanyAgreement::where('is_active', true)->update(['is_active' => false]);
        }

        CompanyAgreement::create([
            'version' => $request->version,
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.company-agreements.index')->with('success', 'Agreement created successfully.');
    }

    public function edit($id)
    {
        $agreement = CompanyAgreement::findOrFail($id);

        return view('admin.pages.company-agreements.edit', [
            'agreement' => $agreement,
        ]);
    }

    public function update(Request $request, $id)
    {
        $agreement = CompanyAgreement::findOrFail($id);

        $request->validate([
            'version' => 'required|string|max:20|unique:tbl_company_agreement,version,' . $id,
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($request->boolean('is_active') && !$agreement->is_active) {
            CompanyAgreement::where('is_active', true)->where('id', '!=', $id)->update(['is_active' => false]);
        }

        $agreement->version = $request->version;
        $agreement->title = $request->title;
        $agreement->content = $request->content;
        $agreement->is_active = $request->boolean('is_active');
        $agreement->save();

        return redirect()->route('admin.company-agreements.index')->with('success', 'Agreement updated successfully.');
    }

    public function destroy($id)
    {
        $agreement = CompanyAgreement::withCount('companies')->findOrFail($id);

        if ($agreement->companies_count > 0) {
            return redirect()->route('admin.company-agreements.index')
                ->with('error', 'Cannot delete agreement. ' . $agreement->companies_count . ' company(ies) have accepted this version.');
        }

        if ($agreement->is_active) {
            return redirect()->route('admin.company-agreements.index')
                ->with('error', 'Cannot delete the active agreement. Deactivate it first.');
        }

        $agreement->delete();

        return redirect()->route('admin.company-agreements.index')->with('success', 'Agreement deleted successfully.');
    }

    public function toggleActive($id)
    {
        $agreement = CompanyAgreement::findOrFail($id);

        if (!$agreement->is_active) {
            CompanyAgreement::where('is_active', true)->update(['is_active' => false]);
            $agreement->is_active = true;
        } else {
            $agreement->is_active = false;
        }

        $agreement->save();

        $status = $agreement->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.company-agreements.index')->with('success', "Agreement v{$agreement->version} {$status}.");
    }

    public function preview($id)
    {
        $agreement = CompanyAgreement::findOrFail($id);

        return view('admin.pages.company-agreements.preview', [
            'agreement' => $agreement,
        ]);
    }
}
