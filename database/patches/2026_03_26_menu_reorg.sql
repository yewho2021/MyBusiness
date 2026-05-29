-- =============================================
-- Menu Reorganization & Dashboard Enhancement
-- Created: 2026-03-26
--
-- IMPORTANT: Run this patch AFTER deploying the
-- updated dashboard files. Menu changes take
-- effect after sidebar cache expires (~5 min)
-- or on next login.
-- =============================================

-- ══════════════════════════════════════════════
-- STEP 1: Add new menu groups
-- ══════════════════════════════════════════════

INSERT INTO `tbl_admin_menu_groups` (`title`, `slug`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 'DATA & FILES', 'data-files', 2, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menu_groups` WHERE `slug` = 'data-files');

INSERT INTO `tbl_admin_menu_groups` (`title`, `slug`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 'MEDIA & DOCUMENTS', 'media-docs', 3, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menu_groups` WHERE `slug` = 'media-docs');

-- ══════════════════════════════════════════════
-- STEP 2: Update existing groups
-- ══════════════════════════════════════════════

-- MAIN MENU stays at sort_order 0
-- USER & ACCESS stays at sort_order 1
UPDATE `tbl_admin_menu_groups` SET `sort_order` = 1 WHERE `slug` = 'access-control';

-- SYSTEM TOOLS → SYSTEM (rename + reorder after new groups)
UPDATE `tbl_admin_menu_groups` SET `title` = 'SYSTEM', `sort_order` = 4 WHERE `slug` = 'data-tools';

-- ══════════════════════════════════════════════
-- STEP 3: Remove duplicate System Patch
-- The seeder created "System Upgrade" in group 4,
-- and the patch added "System Patch" in group 1.
-- Keep the one in group 4 (SYSTEM), remove from group 1 (MAIN).
-- ══════════════════════════════════════════════

DELETE FROM `tbl_admin_menus` WHERE `group_id` = 1 AND `route_name` = 'admin.system-patch.index';

-- Rename "System Upgrade" → "System Patch" and update icon
UPDATE `tbl_admin_menus`
SET `title` = 'System Patch', `icon` = 'fas fa-rocket', `sort_order` = 2
WHERE `route_name` = 'admin.system-patch.index' AND `group_id` = (SELECT `id` FROM `tbl_admin_menu_groups` WHERE `slug` = 'data-tools');

-- ══════════════════════════════════════════════
-- STEP 4: Move items to DATA & FILES group
-- ══════════════════════════════════════════════

UPDATE `tbl_admin_menus`
SET `group_id` = (SELECT `id` FROM `tbl_admin_menu_groups` WHERE `slug` = 'data-files'),
    `icon` = 'fas fa-database', `sort_order` = 1
WHERE `route_name` = 'admin.database.connections.index';

UPDATE `tbl_admin_menus`
SET `group_id` = (SELECT `id` FROM `tbl_admin_menu_groups` WHERE `slug` = 'data-files'),
    `sort_order` = 2
WHERE `route_name` = 'admin.filemanager.index';

UPDATE `tbl_admin_menus`
SET `group_id` = (SELECT `id` FROM `tbl_admin_menu_groups` WHERE `slug` = 'data-files'),
    `title` = 'Backup & Restore', `icon` = 'fas fa-shield-alt', `sort_order` = 3
WHERE `route_name` = 'admin.backup.index';

UPDATE `tbl_admin_menus`
SET `group_id` = (SELECT `id` FROM `tbl_admin_menu_groups` WHERE `slug` = 'data-files'),
    `sort_order` = 4
WHERE `route_name` = 'admin.export.index';

-- ══════════════════════════════════════════════
-- STEP 5: Move items to MEDIA & DOCUMENTS group
-- ══════════════════════════════════════════════

UPDATE `tbl_admin_menus`
SET `group_id` = (SELECT `id` FROM `tbl_admin_menu_groups` WHERE `slug` = 'media-docs'),
    `sort_order` = 1
WHERE `route_name` = 'admin.media.index';

UPDATE `tbl_admin_menus`
SET `group_id` = (SELECT `id` FROM `tbl_admin_menu_groups` WHERE `slug` = 'media-docs'),
    `sort_order` = 2
WHERE `route_name` = 'admin.image-tools.index';

UPDATE `tbl_admin_menus`
SET `group_id` = (SELECT `id` FROM `tbl_admin_menu_groups` WHERE `slug` = 'media-docs'),
    `sort_order` = 3
WHERE `route_name` = 'admin.pdf-tools.index';

-- ══════════════════════════════════════════════
-- STEP 6: Deactivate PDF Suite (keep PDF Tools)
-- ══════════════════════════════════════════════

UPDATE `tbl_admin_menus` SET `is_active` = 0 WHERE `route_name` = 'admin.pdf-suite.index';

-- ══════════════════════════════════════════════
-- STEP 7: Create parent menus for submenus
-- ══════════════════════════════════════════════

-- Parent: "Roles & Permissions" in USER & ACCESS
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `url`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 2, NULL, 1, 'Roles & Permissions', 'fas fa-shield-alt', NULL, '#parent-roles', NULL, 2, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menus` WHERE `url` = '#parent-roles');

-- Parent: "Logs" in USER & ACCESS
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `url`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 2, NULL, 1, 'Logs', 'fas fa-clipboard-list', NULL, '#parent-logs', NULL, 5, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menus` WHERE `url` = '#parent-logs');

-- ══════════════════════════════════════════════
-- STEP 8: Assign children to parent menus
-- ══════════════════════════════════════════════

-- Roles → child of "Roles & Permissions"
UPDATE `tbl_admin_menus`
SET `parent_id` = (SELECT `id` FROM (SELECT `id` FROM `tbl_admin_menus` WHERE `url` = '#parent-roles' LIMIT 1) AS tmp),
    `level` = 2, `sort_order` = 1, `icon` = 'fas fa-user-tag'
WHERE `route_name` = 'admin.roles.index';

-- Permissions → child of "Roles & Permissions"
UPDATE `tbl_admin_menus`
SET `parent_id` = (SELECT `id` FROM (SELECT `id` FROM `tbl_admin_menus` WHERE `url` = '#parent-roles' LIMIT 1) AS tmp),
    `level` = 2, `sort_order` = 2, `icon` = 'fas fa-lock'
WHERE `route_name` = 'admin.permissions.index';

-- Access Log → child of "Logs"
UPDATE `tbl_admin_menus`
SET `parent_id` = (SELECT `id` FROM (SELECT `id` FROM `tbl_admin_menus` WHERE `url` = '#parent-logs' LIMIT 1) AS tmp),
    `level` = 2, `sort_order` = 1
WHERE `route_name` = 'admin.admin-log.index';

-- Activity Log → child of "Logs"
UPDATE `tbl_admin_menus`
SET `parent_id` = (SELECT `id` FROM (SELECT `id` FROM `tbl_admin_menus` WHERE `url` = '#parent-logs' LIMIT 1) AS tmp),
    `level` = 2, `sort_order` = 2
WHERE `route_name` = 'admin.activity-log.index';

-- ══════════════════════════════════════════════
-- STEP 9: Reorder remaining SYSTEM items
-- ══════════════════════════════════════════════

UPDATE `tbl_admin_menus` SET `sort_order` = 1 WHERE `route_name` = 'admin.settings.configuration';
-- System Patch already set to sort_order 2 in Step 3
UPDATE `tbl_admin_menus` SET `sort_order` = 3 WHERE `route_name` = 'admin.file-structure.index';
UPDATE `tbl_admin_menus` SET `sort_order` = 4, `icon` = 'fas fa-code-branch' WHERE `route_name` = 'admin.changelog.index';
UPDATE `tbl_admin_menus` SET `sort_order` = 5 WHERE `route_name` = 'admin.charts.index';

-- Reorder USER & ACCESS top-level items
UPDATE `tbl_admin_menus` SET `sort_order` = 1 WHERE `route_name` = 'admin.users.index';
-- Roles & Permissions parent already sort_order 2
UPDATE `tbl_admin_menus` SET `sort_order` = 3 WHERE `route_name` = 'admin.menus.index';
-- Logs parent already sort_order 5

-- ══════════════════════════════════════════════
-- STEP 10: Update icons for better distinction
-- ══════════════════════════════════════════════

UPDATE `tbl_admin_menus` SET `icon` = 'fas fa-tachometer-alt' WHERE `route_name` = 'admin.dashboard';

-- ══════════════════════════════════════════════
-- STEP 11: Changelog entries
-- ══════════════════════════════════════════════

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.4.0',
    'Menu Reorganization & Dashboard Control Panel',
    'Restructured sidebar menu from 3 messy groups to 5 clean groups with submenus:\n\nMenu Changes:\n- MAIN: Dashboard only (removed duplicate System Patch)\n- USER & ACCESS: Admin Users, Roles & Permissions (submenu), Menu Management, Logs (submenu)\n- DATA & FILES (new): Database, File Manager, Backup & Restore, Export Center\n- MEDIA & DOCUMENTS (new): Media Library, Image Tools, PDF Tools\n- SYSTEM (renamed): Configuration, System Patch, File Structure, Changelog, Chart Samples\n\nCleanup:\n- Removed duplicate System Patch from MAIN MENU\n- Renamed System Upgrade → System Patch\n- Merged PDF Suite into PDF Tools (deactivated PDF Suite)\n- Better icons: Dashboard (tachometer), System Patch (rocket), Changelog (code-branch), Permissions (lock)\n- Submenus for Roles/Permissions and Access/Activity Logs\n\nDashboard:\n- Added collapsible Control Panel section showing all features as icon grid cards\n- Cards grouped by menu category with color-coded icons\n- Auto-generated from menu database — updates when new features are added\n- Collapsible with state saved to localStorage',
    '{"files_changed": ["database/patches SQL", "resources/views/admin/pages/dashboard.blade.php", "app/Http/Controllers/Admin/DashboardController.php"]}',
    NOW()
);
