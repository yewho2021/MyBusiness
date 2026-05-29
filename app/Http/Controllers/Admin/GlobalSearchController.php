<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminMenu;
use App\Models\AdminMenuGroup;
use App\Models\Changelog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    /**
     * Search across modules: menus, admins, tables, changelogs, config.
     */
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $like = '%' . $q . '%';

        // ── 1. Menu items (navigate to pages) ────────
        $menus = AdminMenu::where('is_active', 1)
            ->where(function ($query) use ($like) {
                $query->where('title', 'LIKE', $like)
                      ->orWhere('permission_key', 'LIKE', $like);
            })
            ->whereNotNull('route_name')
            ->limit(5)
            ->get(['title', 'icon', 'route_name']);

        foreach ($menus as $menu) {
            try {
                $url = route($menu->route_name);
            } catch (\Exception $e) {
                continue;
            }
            $results[] = [
                'type'  => 'page',
                'title' => $menu->title,
                'icon'  => $menu->icon ?? 'fas fa-link',
                'url'   => $url,
                'meta'  => 'Page',
            ];
        }

        // ── 2. Admin users ───────────────────────────
        $admins = Admin::where('name', 'LIKE', $like)
            ->orWhere('username', 'LIKE', $like)
            ->orWhere('email', 'LIKE', $like)
            ->limit(5)
            ->get(['id', 'name', 'username', 'email']);

        foreach ($admins as $admin) {
            $results[] = [
                'type'  => 'admin',
                'title' => $admin->name,
                'icon'  => 'fas fa-user',
                'url'   => route('admin.users.edit', $admin->id),
                'meta'  => $admin->username,
            ];
        }

        // ── 3. Database tables ───────────────────────
        try {
            $tables = DB::select('SHOW TABLES');
            $dbKey = 'Tables_in_' . config('database.connections.mysql.database');
            foreach ($tables as $t) {
                $name = $t->$dbKey ?? reset((array) $t);
                if (stripos($name, $q) !== false) {
                    $results[] = [
                        'type'  => 'table',
                        'title' => $name,
                        'icon'  => 'fas fa-table',
                        'url'   => route('admin.database.connections.index') . '#table:' . $name,
                        'meta'  => 'Database table',
                    ];
                }
            }
        } catch (\Exception $e) {}

        // ── 4. Changelog entries ─────────────────────
        $changelogs = Changelog::where('title', 'LIKE', $like)
            ->orWhere('details', 'LIKE', $like)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get(['id', 'title', 'version', 'created_at']);

        foreach ($changelogs as $log) {
            $results[] = [
                'type'  => 'changelog',
                'title' => $log->title,
                'icon'  => 'fas fa-code-branch',
                'url'   => route('admin.changelog.index'),
                'meta'  => 'v' . $log->version,
            ];
        }

        // ── 5. Configuration keys ───────────────────
        try {
            $configs = DB::table('tbl_configuration')
                ->where('label', 'LIKE', $like)
                ->orWhere('key', 'LIKE', $like)
                ->orWhere('group', 'LIKE', $like)
                ->limit(3)
                ->get(['label', 'key', 'group']);

            foreach ($configs as $cfg) {
                $results[] = [
                    'type'  => 'config',
                    'title' => $cfg->label,
                    'icon'  => 'fas fa-cog',
                    'url'   => route('admin.settings.configuration') . '?tab=' . $cfg->group,
                    'meta'  => $cfg->group . '.' . $cfg->key,
                ];
            }
        } catch (\Exception $e) {}

        return response()->json(['results' => array_slice($results, 0, 15)]);
    }
}
