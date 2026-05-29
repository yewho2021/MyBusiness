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
use App\Http\Controllers\Admin\ChangelogController;

Route::middleware('admin.guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [LoginController::class, 'login'])->name('admin.login.submit');
});

Route::middleware(['admin.auth', 'admin.access'])->group(function () {

    Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Admin Users CRUD
    Route::get('users', [AdminController::class, 'index'])->name('admin.users.index');
    Route::post('users', [AdminController::class, 'store'])->name('admin.users.store');
    Route::get('users/{id}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
    Route::put('users/{id}', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('users/{id}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admin.users.toggle-status');

    // Roles CRUD
    Route::get('roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
    Route::put('roles/{id}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
    Route::post('roles/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->name('admin.roles.toggle-status');

    // Menus & Menu Groups
    Route::get('menus', [MenuController::class, 'index'])->name('admin.menus.index');
    Route::post('menus', [MenuController::class, 'storeMenu'])->name('admin.menus.store');
    Route::put('menus/{id}', [MenuController::class, 'updateMenu'])->name('admin.menus.update');
    Route::delete('menus/{id}', [MenuController::class, 'destroyMenu'])->name('admin.menus.destroy.menu');
    Route::post('menus/update-order', [MenuController::class, 'updateOrder'])->name('admin.menus.update-order');

    // Menu Groups
    Route::post('menus/groups', [MenuController::class, 'storeGroup'])->name('admin.menus.groups.store');
    Route::put('menus/groups/{id}', [MenuController::class, 'updateGroup'])->name('admin.menus.groups.update');
    Route::delete('menus/groups/{id}', [MenuController::class, 'destroyGroup'])->name('admin.menus.groups.destroy');

    // Permissions
    Route::get('permissions', [MenuController::class, 'permissions'])->name('admin.permissions.index');
    Route::post('permissions', [MenuController::class, 'updatePermissions'])->name('admin.permissions.update');

    // File Manager
    Route::get('filemanager', [FileManagerController::class, 'index'])->name('admin.filemanager.index');

    Route::get('activity-log', function () {
        return view('admin.pages.activity-log');
    })->name('admin.activity-log');

    Route::get('reports/sales', function () {
        return view('admin.pages.reports.sales');
    })->name('admin.reports.sales');

    Route::get('reports/analytics', function () {
        return view('admin.pages.reports.analytics');
    })->name('admin.reports.analytics');

    Route::get('settings/general', function () {
        return view('admin.pages.settings.general');
    })->name('admin.settings.general');

    Route::get('settings/security', function () {
        return view('admin.pages.settings.security');
    })->name('admin.settings.security');

    // Backup Module
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

    // System Change Log
    Route::get('changelog', [ChangelogController::class, 'index'])->name('admin.changelog.index');

    // Database Manager
    Route::get('database', [DatabaseController::class, 'index'])->name('admin.database.index');
    Route::get('database/table/{table}', [DatabaseController::class, 'viewTable'])->name('admin.database.table');
    Route::match(['get', 'post'], 'database/query', [DatabaseController::class, 'query'])->name('admin.database.query');
    Route::get('database/history', [DatabaseController::class, 'getHistory'])->name('admin.database.history');
    Route::post('database/bookmark', [DatabaseController::class, 'addBookmark'])->name('admin.database.bookmark.add');
    Route::match(['get', 'post'], 'database/export', [DatabaseController::class, 'export'])->name('admin.database.export');
    Route::match(['get', 'post'], 'database/import', [DatabaseController::class, 'import'])->name('admin.database.import');
    Route::delete('database/table/{table}/drop', [DatabaseController::class, 'dropTable'])->name('admin.database.drop');
    Route::post('database/table/{table}/truncate', [DatabaseController::class, 'truncateTable'])->name('admin.database.truncate');
    Route::post('database/table/{table}/delete-row', [DatabaseController::class, 'deleteRow'])->name('admin.database.delete-row');
    Route::post('database/table/{table}/update-cell', [DatabaseController::class, 'updateCell'])->name('admin.database.update-cell');
});