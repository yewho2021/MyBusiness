<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefIndustry;
use App\Models\RefIndustrySubcategory;
use Illuminate\Http\Request;

class RefIndustryController extends Controller
{
    public function index()
    {
        $industries = RefIndustry::withCount('subcategories')->ordered()->get();

        return view('admin.pages.ref-industries.index', [
            'industries' => $industries,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tbl_ref_industry,name',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        RefIndustry::create([
            'name' => $request->name,
            'sort_order' => $request->sort_order ?? 0,
            'status' => 'active',
        ]);

        return redirect()->route('admin.ref-industries.index')->with('success', 'Industry added successfully.');
    }

    public function edit($id)
    {
        $industry = RefIndustry::with(['subcategories' => fn($q) => $q->ordered()])->findOrFail($id);

        return view('admin.pages.ref-industries.edit', [
            'industry' => $industry,
        ]);
    }

    public function update(Request $request, $id)
    {
        $industry = RefIndustry::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:tbl_ref_industry,name,' . $id,
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $industry->name = $request->name;
        $industry->sort_order = $request->sort_order ?? 0;
        $industry->save();

        return redirect()->route('admin.ref-industries.index')->with('success', 'Industry updated successfully.');
    }

    public function destroy($id)
    {
        $industry = RefIndustry::withCount('subcategories')->findOrFail($id);

        if ($industry->subcategories_count > 0) {
            return redirect()->route('admin.ref-industries.index')
                ->with('error', 'Cannot delete industry. It has ' . $industry->subcategories_count . ' subcategorie(s). Remove them first.');
        }

        $industry->delete();

        return redirect()->route('admin.ref-industries.index')->with('success', 'Industry deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $industry = RefIndustry::findOrFail($id);
        $industry->status = $industry->status === 'active' ? 'inactive' : 'active';
        $industry->save();

        $status = $industry->status === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('admin.ref-industries.index')->with('success', "Industry {$status} successfully.");
    }

    // ── Subcategory CRUD ─────────────────────────

    public function storeSubcategory(Request $request, $industryId)
    {
        $industry = RefIndustry::findOrFail($industryId);

        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $industry->subcategories()->create([
            'name' => $request->name,
            'sort_order' => $request->sort_order ?? 0,
            'status' => 'active',
        ]);

        return redirect()->route('admin.ref-industries.edit', $industryId)->with('success', 'Subcategory added successfully.');
    }

    public function updateSubcategory(Request $request, $id)
    {
        $sub = RefIndustrySubcategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $sub->name = $request->name;
        $sub->sort_order = $request->sort_order ?? 0;
        $sub->save();

        return redirect()->route('admin.ref-industries.edit', $sub->industry_id)->with('success', 'Subcategory updated successfully.');
    }

    public function destroySubcategory($id)
    {
        $sub = RefIndustrySubcategory::findOrFail($id);
        $industryId = $sub->industry_id;
        $sub->delete();

        return redirect()->route('admin.ref-industries.edit', $industryId)->with('success', 'Subcategory deleted successfully.');
    }

    public function toggleSubcategoryStatus($id)
    {
        $sub = RefIndustrySubcategory::findOrFail($id);
        $sub->status = $sub->status === 'active' ? 'inactive' : 'active';
        $sub->save();

        $status = $sub->status === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('admin.ref-industries.edit', $sub->industry_id)->with('success', "Subcategory {$status} successfully.");
    }
}
