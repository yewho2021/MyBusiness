-- =============================================
-- Remove SupportPal Module (Complete Cleanup)
-- Created: 2026-05-27
-- =============================================

-- ── 1. Remove menu items under SupportPal group (group_id = 8) ──
DELETE FROM `tbl_admin_menus` WHERE `group_id` = 8;

-- ── 2. Remove any menu items referencing supportpal routes ──
DELETE FROM `tbl_admin_menus`
WHERE `route_name` LIKE '%supportpal%'
   OR `url` LIKE '%supportpal%'
   OR `title` LIKE '%SupportPal%';

-- ── 3. Remove the SUPPORTPAL menu group itself ──
DELETE FROM `tbl_admin_menu_groups`
WHERE `slug` = 'supportpal' OR `title` = 'SUPPORTPAL';

-- ── 4. Remove orphaned role-menu access records ──
DELETE FROM `tbl_admin_role_menu_access`
WHERE `menu_id` NOT IN (SELECT `id` FROM `tbl_admin_menus`);

-- ── 5. Remove SupportPal configuration keys (6 rows) ──
DELETE FROM `tbl_configuration` WHERE `group` = 'supportpal';

-- ── 6. Remove SupportPal changelog entries ──
DELETE FROM `tbl_changelog`
WHERE `title` LIKE '%SupportPal%'
   OR `details` LIKE '%SupportPal%';

-- ── 7. Changelog entry for this patch ──
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '5.1.0', 'Remove SupportPal Module',
    'Complete removal of the SupportPal integration module.\n\n**Problem:** Route [admin.supportpal.dashboard] not defined — crashing the entire sidebar on every page load. The SupportPal menu group (id=8) and menu items existed in the database, but no matching route, controller, or blade views were present in the codebase.\n\n**Removed:**\n- SUPPORTPAL menu group (tbl_admin_menu_groups, id=8)\n- All SupportPal menu items (tbl_admin_menus, group_id=8 + route_name LIKE supportpal)\n- Orphaned role-menu access records (tbl_admin_role_menu_access)\n- 6 configuration keys: sp_db_host, sp_db_port, sp_db_name, sp_db_username, sp_db_password, sp_url (tbl_configuration, group=supportpal)\n- All SupportPal changelog entries\n\n**Hardened:**\n- menu_left.blade.php now uses a $safeRoute() helper that wraps route() in try-catch. If any future menu item references an undefined route, the sidebar gracefully falls back to # instead of crashing the entire page.',
    '{"type":"removal","tables_affected":"tbl_admin_menus,tbl_admin_menu_groups,tbl_admin_role_menu_access,tbl_configuration,tbl_changelog","files_changed":1}',
    NOW()
);

-- ── 8. Cache clear ──
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
