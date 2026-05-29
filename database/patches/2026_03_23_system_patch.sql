-- =============================================
-- System Patch Module
-- Created: 2026-03-23
-- =============================================

-- Menu entry (under same group as File Structure / system tools)
-- Find the group_id for system tools — use group_id 1 (main menu)
INSERT INTO `tbl_admin_menus` (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES (1, NULL, 1, 'System Patch', 'fas fa-wrench', 'admin.system-patch.index', 'system_patch', 57, 1, NOW(), NOW());

-- Grant access to admin role (role_id = 1 = superadmin)
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `has_access`, `created_at`, `updated_at`)
SELECT 1, id, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.system-patch.index'
LIMIT 1;

-- Changelog
INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '1.13.0',
    'System Patch Module',
    'New System Patch tool under the main menu:\n- Upload a .zip patch file containing code files and SQL patches\n- Preview all changes before applying (file list, actions, SQL files)\n- One-click apply: extracts code files, overwrites targets, runs SQL patches\n- Auto-validates paths — blocks dangerous files (.env, vendor/, .htaccess)\n- Auto-backs up overwritten files to storage/app/patch_backups/\n- Auto-saves SQL files to database/patches/ for record\n- Auto-clears Blade view cache after applying\n- Detailed execution log with file-by-file and SQL statement-by-statement results\n- Filterable log (All / Errors / Success)',
    '{"controller": "SystemPatchController", "view": "system/patch.blade.php", "routes": 3, "methods": ["index","preview","apply"]}',
    NOW()
);
