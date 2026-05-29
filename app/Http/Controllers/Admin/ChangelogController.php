<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Version;
use App\Models\VersionCode;
use Illuminate\Http\Request;

class ChangelogController extends Controller
{
    /**
     * Changelog page — now powered by tbl_versions (unified with version system).
     */
    public function index(Request $request)
    {
        $query = Version::with('files')
            ->where('status', 'success')
            ->orderBy('version_code', 'desc');

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('q') && trim($request->q)) {
            $q = '%' . trim($request->q) . '%';
            $query->where(function ($qb) use ($q) {
                $qb->where('file_name', 'LIKE', $q)
                    ->orWhere('description', 'LIKE', $q)
                    ->orWhere('version_code', 'LIKE', $q);
            });
        }

        $versions = $query->paginate(20)->appends($request->query());

        return view('admin.pages.changelog.index', compact('versions'));
    }

    /**
     * AJAX: View file content (before/after) from version code.
     */
    public function viewFile(Request $request)
    {
        $fileId = $request->input('file_id');
        $file = VersionCode::findOrFail($fileId);

        return response()->json([
            'path' => $file->file_path,
            'action' => $file->action,
            'before' => $file->getContentBefore(),
            'after' => $file->getContentAfter(),
            'size_before' => $file->size_before,
            'size_after' => $file->size_after,
        ]);
    }
}
