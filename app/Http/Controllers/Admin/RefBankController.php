<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefBank;
use Illuminate\Http\Request;

class RefBankController extends Controller
{
    public function index()
    {
        $banks = RefBank::ordered()->get();

        return view('admin.pages.ref-banks.index', [
            'banks' => $banks,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:tbl_ref_bank,name',
            'swift_code' => 'nullable|string|max:20',
        ]);

        RefBank::create([
            'name' => $request->name,
            'swift_code' => $request->swift_code,
            'status' => 'active',
        ]);

        return redirect()->route('admin.ref-banks.index')->with('success', 'Bank added successfully.');
    }

    public function edit($id)
    {
        $bank = RefBank::findOrFail($id);

        return view('admin.pages.ref-banks.edit', [
            'bank' => $bank,
        ]);
    }

    public function update(Request $request, $id)
    {
        $bank = RefBank::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:tbl_ref_bank,name,' . $id,
            'swift_code' => 'nullable|string|max:20',
        ]);

        $bank->name = $request->name;
        $bank->swift_code = $request->swift_code;
        $bank->save();

        return redirect()->route('admin.ref-banks.index')->with('success', 'Bank updated successfully.');
    }

    public function destroy($id)
    {
        $bank = RefBank::findOrFail($id);
        $bank->delete();

        return redirect()->route('admin.ref-banks.index')->with('success', 'Bank deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $bank = RefBank::findOrFail($id);
        $bank->status = $bank->status === 'active' ? 'inactive' : 'active';
        $bank->save();

        $status = $bank->status === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('admin.ref-banks.index')->with('success', "Bank {$status} successfully.");
    }
}
