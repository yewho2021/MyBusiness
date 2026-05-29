<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\FileManagerController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\QueryController;
use App\Http\Controllers\Admin\DatabaseExportController;
use App\Http\Controllers\Admin\DatabaseConnectionController;
use App\Http\Controllers\Admin\ChangelogController;
use App\Http\Controllers\Admin\FileStructureController;
use App\Http\Controllers\Admin\SystemPatchController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\ChartSampleController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ImageToolController;
use App\Http\Controllers\Admin\PdfToolController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\PdfSuiteController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\Admin\TelegramController;
use App\Http\Controllers\Admin\TelegramReportController;
use App\Http\Controllers\Admin\RefBankController;
use App\Http\Controllers\Admin\RefIndustryController;
use App\Http\Controllers\Admin\CompanyAgreementController;
use App\Http\Controllers\Admin\CompanyController;

// ─── Health Check (no auth required — for monitoring) ───
Route::get('health', function () {
    $checks = [];
    try { \DB::connection()->getPdo(); $checks['database'] = 'ok'; }
    catch (\Exception $e) { $checks['database'] = 'fail'; }

    $checks['storage_writable'] = is_writable(storage_path()) ? 'ok' : 'fail';
    $checks['cache_writable'] = is_writable(storage_path('framework/cache')) ? 'ok' : 'fail';
    $checks['php_version'] = PHP_VERSION;
    $checks['php_sapi'] = PHP_SAPI;
    $checks['laravel_version'] = app()->version();

    // OPcache
    $checks['opcache'] = (function_exists('opcache_get_status') && @opcache_get_status(false)['opcache_enabled'] ?? false) ? 'enabled' : 'disabled';

    // Disk space
    $free = @disk_free_space(base_path());
    $total = @disk_total_space(base_path());
    $checks['disk_free'] = $free ? round($free / 1073741824, 2) . ' GB' : 'unknown';
    $checks['disk_usage'] = ($free && $total) ? round((1 - $free / $total) * 100, 1) . '%' : 'unknown';

    // Config table accessible
    try { $configCount = \DB::table('tbl_configuration')->count(); $checks['config_table'] = $configCount . ' keys'; }
    catch (\Exception $e) { $checks['config_table'] = 'fail'; }

    // Last backup age
    try {
        $lastBackup = \DB::table('tbl_backup_runs')->where('status', 'completed')->orderBy('completed_at', 'desc')->first();
        $checks['last_backup'] = $lastBackup ? \Carbon\Carbon::parse($lastBackup->completed_at)->diffForHumans() : 'never';
    } catch (\Exception $e) { $checks['last_backup'] = 'unknown'; }

    // Active admin sessions
    try { $checks['active_sessions'] = \DB::table('tbl_admin_log')->where('status', 'active')->count(); }
    catch (\Exception $e) { $checks['active_sessions'] = 'unknown'; }

    // Latest patch version
    try {
        $latest = \DB::table('tbl_versions')->orderBy('version_code', 'desc')->first();
        $checks['latest_patch'] = $latest ? 'v' . $latest->version_code : 'none';
    } catch (\Exception $e) { $checks['latest_patch'] = 'unknown'; }

    $checks['timestamp'] = now()->toIso8601String();

    $allOk = !in_array('fail', array_values($checks));
    return response()->json($checks, $allOk ? 200 : 503);
})->name('admin.health');

// ─── Login-related routes ───
Route::middleware('login.access')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('admin.login');
        Route::post('login', [LoginController::class, 'login'])->name('admin.login.submit')->middleware('throttle:5,1');
    });
    Route::get('login/verify-2fa', [LoginController::class, 'show2faForm'])->name('admin.login.2fa');
    Route::post('login/verify-2fa', [LoginController::class, 'verify2fa'])->name('admin.login.2fa.verify')->middleware('throttle:5,1');
});

// ─── Authenticated admin routes ─────────────────────────────────────
Route::middleware(['admin.auth', 'admin.access'])->group(function () {

    Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');

    // Profile (self-service)
    Route::get('profile', [ProfileController::class, 'index'])->name('admin.profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('admin.profile.update');

    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Global Search (Ctrl+K spotlight — already wired in header)
    Route::get('global-search', [GlobalSearchController::class, 'search'])->name('admin.global-search');

    // Admin Users
    Route::get('users', [AdminController::class, 'index'])->name('admin.users.index');
    Route::post('users', [AdminController::class, 'store'])->name('admin.users.store');
    Route::get('users/{id}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
    Route::put('users/{id}', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('users/{id}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admin.users.toggle-status');

    // 2FA
    Route::post('users/{id}/2fa/setup', [TwoFactorController::class, 'setup'])->name('admin.users.2fa.setup');
    Route::post('users/{id}/2fa/verify', [TwoFactorController::class, 'verify'])->name('admin.users.2fa.verify');
    Route::post('users/{id}/2fa/disable', [TwoFactorController::class, 'disable'])->name('admin.users.2fa.disable');

    // Roles
    Route::get('roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
    Route::put('roles/{id}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
    Route::post('roles/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->name('admin.roles.toggle-status');

    // Menus
    Route::get('menus', [MenuController::class, 'index'])->name('admin.menus.index');
    Route::post('menus', [MenuController::class, 'storeMenu'])->name('admin.menus.store');
    Route::put('menus/{id}', [MenuController::class, 'updateMenu'])->name('admin.menus.update');
    Route::delete('menus/{id}', [MenuController::class, 'destroyMenu'])->name('admin.menus.destroy.menu');
    Route::post('menus/update-order', [MenuController::class, 'updateOrder'])->name('admin.menus.update-order');
    Route::post('menus/groups', [MenuController::class, 'storeGroup'])->name('admin.menus.groups.store');
    Route::put('menus/groups/{id}', [MenuController::class, 'updateGroup'])->name('admin.menus.groups.update');
    Route::delete('menus/groups/{id}', [MenuController::class, 'destroyGroup'])->name('admin.menus.groups.destroy');

    // Permissions
    Route::get('permissions', [MenuController::class, 'permissions'])->name('admin.permissions.index');
    Route::post('permissions', [MenuController::class, 'updatePermissions'])->name('admin.permissions.update');

    // File Manager
    Route::get('filemanager', [FileManagerController::class, 'index'])->name('admin.filemanager.index');

    // File Manager: custom routes (moved from web.php for RBAC coverage)
    Route::get('filemanager/get-content', function () {
        $disk = request('disk', 'home');
        $path = request('path');
        if (!$path) {
            return response()->json(['error' => 'Path required'], 400);
        }
        // Block path traversal attempts
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return response()->json(['error' => 'Invalid path'], 403);
        }
        try {
            $storage = \Illuminate\Support\Facades\Storage::disk($disk);
            if (!$storage->exists($path)) {
                return response()->json(['error' => 'File not found: ' . $path], 404);
            }
            return response()->json(['content' => $storage->get($path), 'path' => $path]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('admin.filemanager.get-content');

    Route::post('filemanager/create-file', function () {
        $disk = request('disk', 'home');
        $path = request('path');
        if (!$path) {
            return response()->json(['success' => false, 'message' => 'Path required'], 400);
        }
        // Block path traversal attempts
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return response()->json(['success' => false, 'message' => 'Invalid path'], 403);
        }
        try {
            $storage = \Illuminate\Support\Facades\Storage::disk($disk);
            if ($storage->exists($path)) {
                return response()->json(['success' => false, 'message' => 'File already exists: ' . $path], 409);
            }
            $storage->put($path, '');
            return response()->json(['success' => true, 'message' => 'File created', 'path' => $path]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    })->name('admin.filemanager.create-file');

    // Activity Log
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log.index');
    Route::get('activity-log/export', [ActivityLogController::class, 'export'])->name('admin.activity-log.export');
    Route::get('activity-log/export-pdf', [ActivityLogController::class, 'exportPdf'])->name('admin.activity-log.export-pdf');
    Route::post('activity-log/purge', [ActivityLogController::class, 'purge'])->middleware('throttle:5,1')->name('admin.activity-log.purge');
    Route::get('activity-log/{id}', [ActivityLogController::class, 'show'])->name('admin.activity-log.show');

    // Media Library
    Route::get('media', [MediaController::class, 'index'])->name('admin.media.index');
    Route::post('media/upload', [MediaController::class, 'upload'])->name('admin.media.upload');
    Route::post('media/bulk-delete', [MediaController::class, 'bulkDelete'])->name('admin.media.bulk-delete');
    Route::get('media/{id}/show', [MediaController::class, 'show'])->name('admin.media.show');
    Route::get('media/{id}/download', [MediaController::class, 'download'])->name('admin.media.download');
    Route::put('media/{id}', [MediaController::class, 'update'])->name('admin.media.update');
    Route::delete('media/{id}', [MediaController::class, 'destroy'])->name('admin.media.destroy');

    // Image Tools
    Route::get('image-tools', [ImageToolController::class, 'index'])->name('admin.image-tools.index');
    Route::post('image-tools/resize', [ImageToolController::class, 'resize'])->name('admin.image-tools.resize');
    Route::post('image-tools/crop', [ImageToolController::class, 'crop'])->name('admin.image-tools.crop');
    Route::post('image-tools/convert', [ImageToolController::class, 'convert'])->name('admin.image-tools.convert');
    Route::post('image-tools/compress', [ImageToolController::class, 'compress'])->name('admin.image-tools.compress');
    Route::post('image-tools/watermark', [ImageToolController::class, 'watermark'])->name('admin.image-tools.watermark');
    Route::post('image-tools/rotate', [ImageToolController::class, 'rotate'])->name('admin.image-tools.rotate');
    Route::post('image-tools/info', [ImageToolController::class, 'info'])->name('admin.image-tools.info');

    // PDF Tools
    Route::get('pdf-tools', [PdfToolController::class, 'index'])->name('admin.pdf-tools.index');
    Route::post('pdf-tools/html-to-pdf', [PdfToolController::class, 'htmlToPdf'])->name('admin.pdf-tools.html-to-pdf');
    Route::post('pdf-tools/html-preview', [PdfToolController::class, 'htmlPreview'])->name('admin.pdf-tools.html-preview');
    Route::post('pdf-tools/report', [PdfToolController::class, 'reportGenerate'])->name('admin.pdf-tools.report');
    Route::post('pdf-tools/templates', [PdfToolController::class, 'templateSave'])->name('admin.pdf-tools.template.save');
    Route::get('pdf-tools/templates/{id}', [PdfToolController::class, 'templateLoad'])->name('admin.pdf-tools.template.load');
    Route::delete('pdf-tools/templates/{id}', [PdfToolController::class, 'templateDelete'])->name('admin.pdf-tools.template.delete');

    // Export Center
    Route::get('export', [ExportController::class, 'index'])->name('admin.export.index');
    Route::post('export/preview', [ExportController::class, 'preview'])->name('admin.export.preview');
    Route::post('export/generate', [ExportController::class, 'generate'])->name('admin.export.generate');
    Route::get('export/{id}/download', [ExportController::class, 'download'])->name('admin.export.download');
    Route::delete('export/{id}', [ExportController::class, 'destroy'])->name('admin.export.destroy');
    Route::post('export/clear', [ExportController::class, 'clearHistory'])->middleware('throttle:5,1')->name('admin.export.clear');

    // PDF Suite (iLovePDF-style tools)
    Route::get('pdf-suite', [PdfSuiteController::class, 'index'])->name('admin.pdf-suite.index');
    Route::post('pdf-suite/merge', [PdfSuiteController::class, 'merge'])->name('admin.pdf-suite.merge');
    Route::post('pdf-suite/split', [PdfSuiteController::class, 'split'])->name('admin.pdf-suite.split');
    Route::post('pdf-suite/rotate', [PdfSuiteController::class, 'rotate'])->name('admin.pdf-suite.rotate');
    Route::post('pdf-suite/pagenumbers', [PdfSuiteController::class, 'pageNumbers'])->name('admin.pdf-suite.pagenumbers');
    Route::post('pdf-suite/watermark', [PdfSuiteController::class, 'watermark'])->name('admin.pdf-suite.watermark');
    Route::post('pdf-suite/jpgtopdf', [PdfSuiteController::class, 'jpgToPdf'])->name('admin.pdf-suite.jpgtopdf');
    Route::post('pdf-suite/pdftojpg', [PdfSuiteController::class, 'pdfToJpg'])->name('admin.pdf-suite.pdftojpg');
    Route::post('pdf-suite/extract', [PdfSuiteController::class, 'extractText'])->name('admin.pdf-suite.extract');
    Route::post('pdf-suite/compress', [PdfSuiteController::class, 'compress'])->name('admin.pdf-suite.compress');
    Route::post('pdf-suite/protect', [PdfSuiteController::class, 'protect'])->name('admin.pdf-suite.protect');
    Route::post('pdf-suite/unlock', [PdfSuiteController::class, 'unlock'])->name('admin.pdf-suite.unlock');
    Route::post('pdf-suite/info', [PdfSuiteController::class, 'info'])->name('admin.pdf-suite.info');

    // Placeholder pages
    Route::get('reports/sales', fn() => view('admin.pages.reports.sales'))->name('admin.reports.sales');
    Route::get('reports/analytics', fn() => view('admin.pages.reports.analytics'))->name('admin.reports.analytics');
    Route::get('settings/general', fn() => view('admin.pages.settings.general'))->name('admin.settings.general');
    Route::get('settings/security', fn() => view('admin.pages.settings.security'))->name('admin.settings.security');

    // Configuration
    Route::get('settings/configuration', [ConfigurationController::class, 'index'])->name('admin.settings.configuration');
    Route::post('settings/configuration', [ConfigurationController::class, 'update'])->name('admin.settings.configuration.update');
    Route::post('settings/configuration/upload', [ConfigurationController::class, 'uploadImage'])->name('admin.settings.configuration.upload');
    Route::post('settings/configuration/remove-image', [ConfigurationController::class, 'removeImage'])->name('admin.settings.configuration.remove-image');
    Route::post('settings/configuration/reset', [ConfigurationController::class, 'resetGroup'])->name('admin.settings.configuration.reset');
    Route::get('settings/configuration/export', [ConfigurationController::class, 'exportConfig'])->name('admin.settings.configuration.export');
    Route::post('settings/configuration/import', [ConfigurationController::class, 'importConfig'])->name('admin.settings.configuration.import');
    Route::post('settings/configuration/test-email', [ConfigurationController::class, 'testEmail'])->name('admin.settings.configuration.test-email');
    Route::post('settings/configuration/clear-cache', [ConfigurationController::class, 'clearSystemCache'])->name('admin.settings.configuration.clear-cache');

    // Backup
    Route::get('backup', [BackupController::class, 'index'])->name('admin.backup.index');
    Route::get('backup/jobs', [BackupController::class, 'jobs'])->name('admin.backup.jobs');
    Route::post('backup/jobs', [BackupController::class, 'storeJob'])->name('admin.backup.jobs.store');
    Route::put('backup/jobs/{id}', [BackupController::class, 'updateJob'])->name('admin.backup.jobs.update');
    Route::delete('backup/jobs/{id}', [BackupController::class, 'deleteJob'])->name('admin.backup.jobs.delete');
    Route::post('backup/jobs/{id}/toggle', [BackupController::class, 'toggleJob'])->name('admin.backup.jobs.toggle');
    Route::post('backup/jobs/{id}/run', [BackupController::class, 'runNow'])->name('admin.backup.run.now');
    Route::post('backup/run-manual', [BackupController::class, 'runManual'])->name('admin.backup.run.manual');
    Route::get('backup/history', [BackupController::class, 'history'])->name('admin.backup.history');
    Route::get('backup/logs/{id}', [BackupController::class, 'logs'])->name('admin.backup.logs');
    Route::get('backup/progress/{id}', [BackupController::class, 'progress'])->name('admin.backup.progress');
    Route::post('backup/execute/{id}', [BackupController::class, 'executeRun'])->name('admin.backup.execute');
    Route::post('backup/restore-ajax/{id}', [BackupController::class, 'restoreExecuteAjax'])->name('admin.backup.restore.ajax');
    Route::get('backup/restore/{id}', [BackupController::class, 'restoreConfirm'])->name('admin.backup.restore.confirm');
    Route::post('backup/restore/{id}', [BackupController::class, 'restoreExecute'])->name('admin.backup.restore.execute');
    Route::delete('backup/{id}', [BackupController::class, 'deleteBackup'])->name('admin.backup.delete');
    Route::delete('backup/{id}/logs', [BackupController::class, 'deleteLogs'])->name('admin.backup.logs.delete');
    Route::get('backup/{id}/download', [BackupController::class, 'download'])->name('admin.backup.download');

    // Changelog
    Route::get('changelog', [ChangelogController::class, 'index'])->name('admin.changelog.index');
    Route::post('changelog/view-file', [ChangelogController::class, 'viewFile'])->name('admin.changelog.view-file');

    // Database Connections (before wildcards)
    Route::get('database/connections', [DatabaseConnectionController::class, 'index'])->name('admin.database.connections.index');
    Route::post('database/connections', [DatabaseConnectionController::class, 'store'])->name('admin.database.connections.store');
    Route::put('database/connections/{id}', [DatabaseConnectionController::class, 'update'])->name('admin.database.connections.update');
    Route::delete('database/connections/{id}', [DatabaseConnectionController::class, 'destroy'])->name('admin.database.connections.destroy');
    Route::post('database/connections/{id}/toggle', [DatabaseConnectionController::class, 'toggleStatus'])->name('admin.database.connections.toggle');
    Route::post('database/connections/test', [DatabaseConnectionController::class, 'test'])->name('admin.database.connections.test');
    Route::get('database/connections/{id}/browse', [DatabaseConnectionController::class, 'browse'])->name('admin.database.connections.browse');
    Route::get('database/connections/clear', [DatabaseConnectionController::class, 'clearConnection'])->name('admin.database.connections.clear');

    // Database Manager
    Route::get('database', [DatabaseController::class, 'index'])->name('admin.database.index');
    Route::get('database/table/{table}', [DatabaseController::class, 'viewTable'])->name('admin.database.table');
    Route::match(['get', 'post'], 'database/query', [QueryController::class, 'query'])->name('admin.database.query');
    Route::get('database/history', [QueryController::class, 'getHistory'])->name('admin.database.history');
    Route::post('database/bookmark', [QueryController::class, 'addBookmark'])->name('admin.database.bookmark.add');
    Route::match(['get', 'post'], 'database/export', [DatabaseExportController::class, 'export'])->name('admin.database.export');
    Route::post('database/export-ajax', [DatabaseExportController::class, 'exportAjax'])->name('admin.database.export-ajax');
    Route::get('database/export-download', [DatabaseExportController::class, 'exportDownload'])->name('admin.database.export-download');
    Route::get('database/er-diagram', [DatabaseController::class, 'erDiagramData'])->name('admin.database.er-diagram');
    Route::match(['get', 'post'], 'database/import', [DatabaseExportController::class, 'import'])->name('admin.database.import');
    Route::delete('database/table/{table}/drop', [DatabaseController::class, 'dropTable'])->name('admin.database.drop');
    Route::post('database/table/{table}/truncate', [DatabaseController::class, 'truncateTable'])->middleware('throttle:10,1')->name('admin.database.truncate');
    Route::post('database/table/{table}/delete-row', [DatabaseController::class, 'deleteRow'])->name('admin.database.delete-row');
    Route::post('database/table/{table}/update-cell', [DatabaseController::class, 'updateCell'])->name('admin.database.update-cell');

    // Admin Login Log
    Route::get('admin-log', [AdminLogController::class, 'index'])->name('admin.admin-log.index');
    Route::get('admin-log/export', [AdminLogController::class, 'export'])->name('admin.admin-log.export');
    Route::post('admin-log/purge', [AdminLogController::class, 'purge'])->name('admin.admin-log.purge');
    Route::get('admin-log/{id}', [AdminLogController::class, 'show'])->name('admin.admin-log.show');
    Route::post('admin-log/{id}/kick', [AdminLogController::class, 'kick'])->name('admin.admin-log.kick');

    // Charts
    Route::get('charts', [ChartSampleController::class, 'index'])->name('admin.charts.index');

    // File Structure
    Route::get('file-structure', [FileStructureController::class, 'index'])->name('admin.file-structure.index');
    Route::get('file-structure/generate', [FileStructureController::class, 'generate'])->name('admin.file-structure.generate');
    Route::get('file-structure/export-db/{format}', [FileStructureController::class, 'exportDatabase'])->name('admin.file-structure.export-db')->where('format', 'sql|zip');
    Route::post('file-structure/export-ai', [FileStructureController::class, 'exportForAI'])->name('admin.file-structure.export-ai');
    Route::get('file-structure/export-ai-download', [FileStructureController::class, 'exportForAIDownload'])->name('admin.file-structure.export-ai-download');

    // System Patch
    Route::get('system-patch', [SystemPatchController::class, 'index'])->name('admin.system-patch.index');
    Route::post('system-patch/preview', [SystemPatchController::class, 'preview'])->name('admin.system-patch.preview');
    Route::post('system-patch/apply', [SystemPatchController::class, 'apply'])->middleware('throttle:3,1')->name('admin.system-patch.apply');

    // Version History & Rollback
    Route::get('system-patch/version/{code}', [SystemPatchController::class, 'versionDetail'])->name('admin.system-patch.version-detail');
    Route::get('system-patch/version/{code}/file/{fileId}', [SystemPatchController::class, 'viewFile'])->name('admin.system-patch.view-file');
    Route::get('system-patch/version/{code}/download', [SystemPatchController::class, 'downloadVersion'])->name('admin.system-patch.download');
    Route::post('system-patch/rollback/preview', [SystemPatchController::class, 'rollbackPreview'])->name('admin.system-patch.rollback-preview');
    Route::post('system-patch/rollback/execute', [SystemPatchController::class, 'rollbackExecute'])->name('admin.system-patch.rollback-execute');

    // System Status (administrator only — enforced at controller level)
    Route::get('system-status', [SystemStatusController::class, 'index'])->name('admin.system-status.index');
    Route::post('system-status/refresh', [SystemStatusController::class, 'refresh'])->name('admin.system-status.refresh');

    // ── Telegram Bot ──────────────────────────────────
    Route::get('telegram', [TelegramController::class, 'index'])->name('admin.telegram.index');
    Route::post('telegram/save', [TelegramController::class, 'save'])->name('admin.telegram.save');
    Route::post('telegram/test-connection', [TelegramController::class, 'testConnection'])->name('admin.telegram.test-connection');
    Route::post('telegram/test-send', [TelegramController::class, 'testSend'])->name('admin.telegram.test-send');
    Route::post('telegram/send-report', [TelegramController::class, 'sendReport'])->name('admin.telegram.send-report');
    Route::get('telegram/log', [TelegramController::class, 'getLog'])->name('admin.telegram.log');
    Route::get('telegram/discover', [TelegramController::class, 'discoverChats'])->name('admin.telegram.discover');
    Route::get('telegram/targets', [TelegramController::class, 'targetsList'])->name('admin.telegram.targets');
    Route::post('telegram/targets', [TelegramController::class, 'targetsStore'])->name('admin.telegram.targets.store');
    Route::put('telegram/targets/{id}', [TelegramController::class, 'targetsUpdate'])->name('admin.telegram.targets.update');
    Route::post('telegram/targets/{id}/default', [TelegramController::class, 'targetsDefault'])->name('admin.telegram.targets.default');
    Route::delete('telegram/targets/{id}', [TelegramController::class, 'targetsDelete'])->name('admin.telegram.targets.delete');

    // ── B2B Reference Data ────────────────────────────

    // Banks
    Route::get('ref-banks', [RefBankController::class, 'index'])->name('admin.ref-banks.index');
    Route::post('ref-banks', [RefBankController::class, 'store'])->name('admin.ref-banks.store');
    Route::get('ref-banks/{id}/edit', [RefBankController::class, 'edit'])->name('admin.ref-banks.edit');
    Route::put('ref-banks/{id}', [RefBankController::class, 'update'])->name('admin.ref-banks.update');
    Route::delete('ref-banks/{id}', [RefBankController::class, 'destroy'])->name('admin.ref-banks.destroy');
    Route::post('ref-banks/{id}/toggle-status', [RefBankController::class, 'toggleStatus'])->name('admin.ref-banks.toggle-status');

    // Industries
    Route::get('ref-industries', [RefIndustryController::class, 'index'])->name('admin.ref-industries.index');
    Route::post('ref-industries', [RefIndustryController::class, 'store'])->name('admin.ref-industries.store');
    Route::get('ref-industries/{id}/edit', [RefIndustryController::class, 'edit'])->name('admin.ref-industries.edit');
    Route::put('ref-industries/{id}', [RefIndustryController::class, 'update'])->name('admin.ref-industries.update');
    Route::delete('ref-industries/{id}', [RefIndustryController::class, 'destroy'])->name('admin.ref-industries.destroy');
    Route::post('ref-industries/{id}/toggle-status', [RefIndustryController::class, 'toggleStatus'])->name('admin.ref-industries.toggle-status');

    // Industry Subcategories
    Route::post('ref-industries/{id}/subcategories', [RefIndustryController::class, 'storeSubcategory'])->name('admin.ref-industries.subcategories.store');
    Route::put('ref-subcategories/{id}', [RefIndustryController::class, 'updateSubcategory'])->name('admin.ref-subcategories.update');
    Route::delete('ref-subcategories/{id}', [RefIndustryController::class, 'destroySubcategory'])->name('admin.ref-subcategories.destroy');
    Route::post('ref-subcategories/{id}/toggle-status', [RefIndustryController::class, 'toggleSubcategoryStatus'])->name('admin.ref-subcategories.toggle-status');

    // Companies
    Route::get('companies', [CompanyController::class, 'index'])->name('admin.companies.index');
    Route::get('companies/{id}', [CompanyController::class, 'show'])->name('admin.companies.show');
    Route::put('companies/{id}/status', [CompanyController::class, 'updateStatus'])->name('admin.companies.update-status');
    Route::delete('companies/{id}', [CompanyController::class, 'destroy'])->name('admin.companies.destroy');

    // Company Agreements
    Route::get('company-agreements', [CompanyAgreementController::class, 'index'])->name('admin.company-agreements.index');
    Route::get('company-agreements/create', [CompanyAgreementController::class, 'create'])->name('admin.company-agreements.create');
    Route::post('company-agreements', [CompanyAgreementController::class, 'store'])->name('admin.company-agreements.store');
    Route::get('company-agreements/{id}/edit', [CompanyAgreementController::class, 'edit'])->name('admin.company-agreements.edit');
    Route::put('company-agreements/{id}', [CompanyAgreementController::class, 'update'])->name('admin.company-agreements.update');
    Route::delete('company-agreements/{id}', [CompanyAgreementController::class, 'destroy'])->name('admin.company-agreements.destroy');
    Route::post('company-agreements/{id}/toggle-active', [CompanyAgreementController::class, 'toggleActive'])->name('admin.company-agreements.toggle-active');
    Route::get('company-agreements/{id}/preview', [CompanyAgreementController::class, 'preview'])->name('admin.company-agreements.preview');

    // ── Telegram Reports ──────────────────────────────
    Route::get('telegram/reports', [TelegramReportController::class, 'index'])->name('admin.telegram.reports');
    Route::get('telegram/reports/list', [TelegramReportController::class, 'list'])->name('admin.telegram.reports.list');
    Route::get('telegram/reports/{id}', [TelegramReportController::class, 'get'])->name('admin.telegram.reports.get')->where('id', '[0-9]+');
    Route::post('telegram/reports', [TelegramReportController::class, 'store'])->name('admin.telegram.reports.store');
    Route::put('telegram/reports/{id}', [TelegramReportController::class, 'update'])->name('admin.telegram.reports.update');
    Route::delete('telegram/reports/{id}', [TelegramReportController::class, 'destroy'])->name('admin.telegram.reports.destroy');
    Route::post('telegram/reports/{id}/clone', [TelegramReportController::class, 'clone'])->name('admin.telegram.reports.clone');
    Route::post('telegram/reports/{id}/toggle', [TelegramReportController::class, 'toggle'])->name('admin.telegram.reports.toggle');
    Route::post('telegram/reports/{id}/preview', [TelegramReportController::class, 'preview'])->name('admin.telegram.reports.preview');
    Route::post('telegram/reports/{id}/send', [TelegramReportController::class, 'send'])->name('admin.telegram.reports.send');
    Route::post('telegram/reports/send-bulk', [TelegramReportController::class, 'sendBulk'])->name('admin.telegram.reports.send-bulk');
    Route::post('telegram/reports/test-query', [TelegramReportController::class, 'testQuery'])->name('admin.telegram.reports.test-query');
    Route::get('telegram/reports/{slug}/source', [TelegramReportController::class, 'source'])->name('admin.telegram.reports.source');
    Route::post('telegram/reports/{slug}/save-source', [TelegramReportController::class, 'saveSource'])->name('admin.telegram.reports.save-source');
    Route::get('telegram/reports/{id}/diagnose', [TelegramReportController::class, 'diagnose'])->name('admin.telegram.reports.diagnose')->where('id', '[0-9]+');
    Route::get('telegram/cron-test', [TelegramReportController::class, 'cronTest'])->name('admin.telegram.cron-test');

});

// ─── Media file serving (outside auth) ───
Route::get('media-file/{id}/{filename}', [MediaController::class, 'serve'])->name('admin.media.serve');
Route::get('media-conversion/{id}/{conversion}', [MediaController::class, 'serveConversion'])->name('admin.media.serve-conversion');
