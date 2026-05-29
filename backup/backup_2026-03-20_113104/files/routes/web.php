<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::group([], base_path('routes/admin.php'));

// Serve vendor assets (file-manager + monaco-editor) without using public folder
Route::get('/vendor-asset/{path}', function ($path) {
    // Sanitize path - prevent directory traversal
    if (str_contains($path, '..')) {
        abort(404);
    }
    
    // Route to correct vendor location
    if (str_starts_with($path, 'file-manager/')) {
        // Strip 'file-manager/' prefix since assets folder already contains css/ and js/ directly
        $assetPath = substr($path, strlen('file-manager/'));
        $file = base_path('vendor/alexusmai/laravel-file-manager/resources/assets/' . $assetPath);
    } elseif (str_starts_with($path, 'monaco-editor/')) {
        $file = base_path('vendor/' . $path);
    } else {
        abort(404);
    }
    
    if (!file_exists($file)) {
        abort(404);
    }
    
    // Determine mime type
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'ttf' => 'font/ttf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'map' => 'application/json',
    ];
    $mime = $mimes[$ext] ?? 'application/octet-stream';
    
    return response()->file($file, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->where('path', '.*')->name('vendor.asset');

// Custom route for file editor - get file content as text
Route::get('/file-manager/get-content', function () {
    $disk = request('disk', 'home');
    $path = request('path');
    
    if (!$path) {
        return response()->json(['error' => 'Path required'], 400);
    }
    
    try {
        $storage = Storage::disk($disk);
        
        if (!$storage->exists($path)) {
            return response()->json(['error' => 'File not found: ' . $path], 404);
        }
        
        $content = $storage->get($path);
        
        return response()->json([
            'content' => $content,
            'path' => $path
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->middleware(['web'])->name('fm.get-content');
