<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyAgreement;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withCount(['admins', 'partners', 'products']);

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $companies = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Company::count(),
            'active' => Company::byStatus('active')->count(),
            'pending' => Company::byStatus('pending')->count(),
            'suspended' => Company::byStatus('suspended')->count(),
        ];

        return view('admin.pages.companies.index', [
            'companies' => $companies,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show($id)
    {
        $company = Company::with([
            'agreement',
            'admins.role',
            'partners' => fn($q) => $q->latest()->take(10),
            'products' => fn($q) => $q->latest()->take(10),
            'industries.industry',
            'roles',
        ])->withCount(['admins', 'partners', 'products'])->findOrFail($id);

        return view('admin.pages.companies.show', [
            'company' => $company,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,active,suspended,inactive',
        ]);

        $oldStatus = $company->status;
        $company->status = $request->status;
        $company->save();

        return redirect()->route('admin.companies.show', $id)
            ->with('success', "Company status changed from {$oldStatus} to {$request->status}.");
    }

    public function destroy($id)
    {
        $company = Company::withCount(['admins', 'partners', 'products'])->findOrFail($id);

        if ($company->partners_count > 0 || $company->products_count > 0) {
            return redirect()->route('admin.companies.show', $id)
                ->with('error', "Cannot delete company with {$company->partners_count} partner(s) and {$company->products_count} product(s). Suspend it instead.");
        }

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', "Company '{$company->company_name}' has been soft-deleted.");
    }
}
