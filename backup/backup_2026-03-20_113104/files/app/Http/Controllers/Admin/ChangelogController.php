<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Changelog;
use Illuminate\Http\Request;

class ChangelogController extends Controller
{
    public function index(Request $request)
    {
        $query = Changelog::orderBy('created_at', 'desc');

        // Filter by app type if requested
        if ($request->has('app_type')) {
            $query->where('app_type', $request->app_type);
        }

        $logs = $query->paginate(50);

        return view('admin.pages.changelog.index', compact('logs'));
    }
}
