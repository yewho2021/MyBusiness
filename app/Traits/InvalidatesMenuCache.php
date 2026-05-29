<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Auto-invalidate sidebar and dashboard caches when menu-related models change.
 *
 * Use this trait on: AdminMenu, AdminMenuGroup, AdminRoleMenuAccess, AdminRole.
 * When any of these models are created, updated, or deleted, all cached sidebar
 * menus and dashboard control panels are cleared so users see fresh data.
 *
 * @since 2026-03-29 P3 patch
 */
trait InvalidatesMenuCache
{
    /**
     * Boot the trait — register model events for cache invalidation.
     */
    public static function bootInvalidatesMenuCache(): void
    {
        static::saved(function () {
            static::clearMenuCaches();
        });

        static::deleted(function () {
            static::clearMenuCaches();
        });
    }

    /**
     * Clear all sidebar menu caches and dashboard control panel caches.
     *
     * Cache keys:
     *   sidebar_menu_{roleId}  — per-role sidebar menu structure
     *   dashboard_cp_{roleId}  — per-role dashboard control panel
     *   dashboard_core_stats   — admin/role/menu counts
     */
    public static function clearMenuCaches(): void
    {
        try {
            // Get all role IDs to clear per-role caches
            $roleIds = \App\Models\AdminRole::pluck('id')->toArray();
            $roleIds[] = 'guest'; // Also clear guest cache

            foreach ($roleIds as $roleId) {
                Cache::forget('sidebar_menu_' . $roleId);
                Cache::forget('dashboard_cp_' . $roleId);
            }

            // Also clear core stats (menu count may have changed)
            Cache::forget('dashboard_core_stats');
        } catch (\Exception $e) {
            // Silent fail — cache clearing is best-effort
        }
    }
}
