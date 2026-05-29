<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use App\Models\Media;

class MediaController extends Controller
{
    /**
     * Browse all media with grid/list view
     */
    public function index(Request $request)
    {
        $query = Media::query()->orderBy('created_at', 'desc');

        // Filter by collection
        if ($request->filled('collection') && $request->collection !== 'all') {
            $query->where('collection_name', $request->collection);
        }

        // Filter by type
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'image':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'document':
                    $query->whereIn('mime_type', [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/plain',
                        'text/csv',
                    ]);
                    break;
                case 'other':
                    $query->where('mime_type', 'not like', 'image/%')
                          ->where('mime_type', 'not like', 'application/pdf');
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('file_name', 'like', "%{$s}%")
                  ->orWhere('mime_type', 'like', "%{$s}%");
            });
        }

        $media = $query->paginate(24)->appends($request->query());

        // Stats
        $stats = [
            'total'      => Media::count(),
            'images'     => Media::where('mime_type', 'like', 'image/%')->count(),
            'documents'  => Media::where('mime_type', 'like', 'application/%')->count(),
            'total_size' => Media::sum('size'),
        ];

        // Collections for tabs
        $collections = Media::select('collection_name')
            ->distinct()
            ->pluck('collection_name');

        $viewMode = $request->get('view', 'grid');

        return view('admin.pages.media.index', compact('media', 'stats', 'collections', 'viewMode'));
    }

    /**
     * Upload files via AJAX
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:20480', // 20MB max per file
            'collection' => 'nullable|string|max:50',
        ]);

        if (!$request->hasFile('files')) {
            return response()->json(['success' => false, 'message' => 'No files provided.'], 422);
        }

        $collection = $request->input('collection', 'general');
        $uploaded = [];

        // Get or create a holder model for general uploads
        $holder = $this->getMediaHolder();

        foreach ($request->file('files') as $file) {
            try {
                $media = $holder->addMedia($file)
                    ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    ->toMediaCollection($collection, 'media');

                $uploaded[] = [
                    'id'        => $media->id,
                    'name'      => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size'      => $media->size,
                    'url'       => route('admin.media.serve', ['id' => $media->id, 'filename' => $media->file_name]),
                    'thumb_url' => $media->hasGeneratedConversion('thumb')
                        ? route('admin.media.serve-conversion', ['id' => $media->id, 'conversion' => 'thumb'])
                        : null,
                ];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($uploaded) . ' file(s) uploaded successfully.',
            'files'   => $uploaded,
        ]);
    }

    /**
     * Update media metadata
     */
    public function update(Request $request, $id)
    {
        $media = Media::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:255',
            'collection' => 'nullable|string|max:50',
        ]);

        $media->name = $request->input('name');

        // Custom properties (alt text, description, tags)
        $custom = $media->custom_properties ?? [];
        if ($request->has('alt_text'))    $custom['alt_text']    = $request->input('alt_text', '');
        if ($request->has('description')) $custom['description'] = $request->input('description', '');
        if ($request->has('tags'))        $custom['tags']        = $request->input('tags', '');
        $media->custom_properties = $custom;

        // Move collection if changed
        if ($request->filled('collection') && $request->collection !== $media->collection_name) {
            $media->collection_name = $request->collection;
        }

        $media->save();

        return response()->json(['success' => true, 'message' => 'Media updated successfully.']);
    }

    /**
     * Delete a single media file
     */
    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        $media->delete();

        return response()->json(['success' => true, 'message' => 'File deleted successfully.']);
    }

    /**
     * Bulk delete media files
     */
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $count = 0;
        foreach ($request->ids as $id) {
            $media = Media::find($id);
            if ($media) {
                $media->delete();
                $count++;
            }
        }

        return response()->json(['success' => true, 'message' => "{$count} file(s) deleted."]);
    }

    /**
     * Download original file
     */
    public function download($id)
    {
        $media = Media::findOrFail($id);
        $path = $media->getPath();

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->download($path, $media->file_name);
    }

    /**
     * Serve original file (cPanel-safe, no symlink)
     */
    public function serve($id, $filename)
    {
        $media = Media::findOrFail($id);
        $path = $media->getPath();

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => $media->mime_type,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Serve a conversion (thumb, preview)
     */
    public function serveConversion($id, $conversion)
    {
        $media = Media::findOrFail($id);

        if (!$media->hasGeneratedConversion($conversion)) {
            // Fall back to original
            return $this->serve($id, $media->file_name);
        }

        $path = $media->getPath($conversion);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => $media->mime_type,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * AJAX: Get media info for edit modal
     */
    public function show($id)
    {
        $media = Media::findOrFail($id);
        $custom = $media->custom_properties ?? [];

        return response()->json([
            'success'     => true,
            'id'          => $media->id,
            'name'        => $media->name,
            'file_name'   => $media->file_name,
            'mime_type'   => $media->mime_type,
            'size'        => $media->size,
            'collection'  => $media->collection_name,
            'alt_text'    => $custom['alt_text'] ?? '',
            'description' => $custom['description'] ?? '',
            'tags'        => $custom['tags'] ?? '',
            'created_at'  => $media->created_at?->format('Y-m-d H:i:s'),
            'url'         => route('admin.media.serve', ['id' => $media->id, 'filename' => $media->file_name]),
        ]);
    }

    /**
     * Get or create a general media holder.
     * We use MediaItem model_id=0 as a dummy parent for standalone uploads.
     */
    private function getMediaHolder(): MediaItem
    {
        // Use a fixed ID. Since MediaItem maps to tbl_admin,
        // we just find ID=1 (the first admin) or create a virtual instance.
        $holder = new MediaItem();
        $holder->id = 0;
        $holder->exists = true;

        return $holder;
    }

    /**
     * Format bytes to human-readable
     */
    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
