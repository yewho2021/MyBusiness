<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Lightweight API foundation. All routes prefixed with /api/
| and return JSON responses.
|
| Authentication options:
| 1. Simple: Bearer token from tbl_configuration (api.api_token)
| 2. Full: Install Laravel Sanctum when needed
|
| Usage: Include this file from routes/web.php:
|   Route::prefix('api')->group(base_path('routes/api.php'));
|
*/

// ── Public endpoints ─────────────────────────────
Route::get('health', function () {
    return response()->json([
        'status'  => 'ok',
        'version' => \App\Models\Configuration::get('footer_version', '1.0.0'),
        'time'    => now()->toIso8601String(),
    ]);
});

// ── Authenticated API endpoints ──────────────────
// Uncomment and use when ready:
//
// Route::middleware('api.auth')->group(function () {
//     Route::get('admin/me', function (\Illuminate\Http\Request $request) {
//         return response()->json($request->attributes->get('api_admin'));
//     });
// });
