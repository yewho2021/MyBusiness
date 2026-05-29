<?php

namespace App\Traits;

use App\Models\DatabaseConnection;
use Illuminate\Support\Facades\DB;

/**
 * Shared database manager methods used by DatabaseController,
 * QueryController, and DatabaseExportController.
 */
trait DatabaseHelpers
{
    /**
     * Get the isolated DB Manager connection.
     */
    protected function db()
    {
        $connId = session('db_connection_id');

        if ($connId) {
            $saved = DatabaseConnection::find($connId);
            if ($saved) {
                config([
                    'database.connections.mysql_dbmanager.host'     => $saved->dbhost,
                    'database.connections.mysql_dbmanager.port'     => $saved->dbport ?: '3306',
                    'database.connections.mysql_dbmanager.database' => $saved->dbname,
                    'database.connections.mysql_dbmanager.username' => $saved->dbusername,
                    'database.connections.mysql_dbmanager.password' => $saved->dbpassword,
                ]);
                DB::purge('mysql_dbmanager');
            }
        }

        return DB::connection('mysql_dbmanager');
    }

    /**
     * Get the active connection info for views.
     */
    protected function getActiveConnection(): ?DatabaseConnection
    {
        $connId = session('db_connection_id');
        return $connId ? DatabaseConnection::find($connId) : null;
    }

    /**
     * Get the active database name — from saved connection or .env.
     */
    protected function getActiveDbName(): string
    {
        $active = $this->getActiveConnection();
        return $active ? $active->dbname : config('database.connections.mysql.database');
    }

    /**
     * Validate a table name against SQL injection.
     */
    protected function validateTableName(string $t): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $t)) {
            abort(400, 'Invalid table name.');
        }
    }

    /**
     * Format bytes to human-readable size.
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Administrator-only middleware for database manager access.
     */
    protected static function adminOnlyMiddleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware(function ($request, $next) {
                $admin = $request->attributes->get('admin');
                if (!$admin || !$admin->isAdministrator()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Only administrators can access the Database Manager.'], 403);
                    }
                    return redirect()->route('admin.dashboard')->with('error', 'Only administrators can access the Database Manager.');
                }
                return $next($request);
            }),
        ];
    }
}
