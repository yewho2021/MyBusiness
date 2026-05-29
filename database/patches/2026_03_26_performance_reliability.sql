-- =============================================
-- Phase 2: Performance & Reliability
-- Created: 2026-03-26
-- =============================================

-- Clear sidebar cache to pick up any role changes
DELETE FROM `cache` WHERE `key` LIKE '%sidebar_menu%';

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.5.1',
    'Phase 2: Performance & Reliability',
    'Performance improvements and reliability hardening:\n\n1. Eager Loading (N+1 fix):\n- AdminController: all findOrFail() calls now use ->with(role) — eliminates N+1 on user edit/view/delete/toggle pages\n- ProfileController: profile page loads role in single query\n\n2. Database Transactions:\n- Permissions bulk update (MenuController::updatePermissions) now wrapped in DB::transaction() — if any permission save fails, all roll back\n\n3. Server-Side Pagination:\n- ChangelogController changed from ->get() (loads ALL records) to ->paginate(25)\n- Changelog view adds Laravel pagination links below table\n- DataTables client-side paging disabled (server handles it now)\n\n4. Sidebar Cache Invalidation:\n- RoleController now clears sidebar cache on role delete and toggle status\n- Prevents stale menu visibility when roles are disabled/deleted\n- Uses same clearMenuCache() pattern as MenuController\n\n5. Connection Timeout:\n- mysql_dbmanager config now has PDO::ATTR_TIMEOUT = 10 seconds\n- Prevents hanging on unreachable external database connections',
    '{"files_changed": ["AdminController.php", "ProfileController.php", "MenuController.php", "ChangelogController.php", "RoleController.php", "config/database.php", "changelog/index.blade.php"]}',
    NOW()
);
