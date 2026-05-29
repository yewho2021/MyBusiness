-- =============================================
-- P6: System Status Page
-- Created: 2026-03-29
-- =============================================

-- ── Menu entry for System Status ──────────────
-- Added under SYSTEM group (group_id = 4), after System Patch

INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES
    (4, NULL, 1, 'System Status', 'fas fa-heartbeat', 'admin.system-status.index', 'system_status', 19, 1, NOW(), NOW());

-- ── Role access for administrator (role_id = 1) ──
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.system-status.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- ── Clear menu caches ──
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

-- ── Changelog ──────────────────────────────────

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.7.0', 'System Status Page',
    '**New: System Status Dashboard**\n• Comprehensive server diagnostics page accessible from System menu\n• Administrator-only access (enforced at controller level)\n\n**Sections:**\n• PHP — version, SAPI, memory limit, extensions check (9 required extensions)\n• MySQL — version, charset, buffer pool, uptime, connection status\n• Disk Usage — total/used/free with color-coded progress bar (healthy/warning/critical)\n• Laravel — version, environment, debug mode, cache status, session/cache/queue drivers\n• OPcache — memory usage, hit rate, cached scripts, reset availability\n• Sessions & Backup — active sessions, login stats, last backup age with staleness warning\n• Database Tables — all tables ranked by size with row counts and proportion bars\n• Storage Breakdown — directory-by-directory size analysis (views, cache, sessions, logs, backups, exports)\n• Recent Errors — last 5 error/warning lines from log file',
    '{"features":["system-status-page"],"files_new":["app/Http/Controllers/Admin/SystemStatusController.php","resources/views/admin/pages/system/status.blade.php"]}',
    NOW()
);
