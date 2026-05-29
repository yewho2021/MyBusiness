<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::group([], base_path('routes/admin.php'));

// API routes (Item #24)
Route::prefix('api')->group(base_path('routes/api.php'));

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

// NOTE: File manager custom routes (get-content, create-file) moved to routes/admin.php
// inside the authenticated + RBAC middleware group for proper access control.
