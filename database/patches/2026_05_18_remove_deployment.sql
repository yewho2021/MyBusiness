-- =============================================
-- Remove: Deployment module
-- Created: 2026-05-18
-- =============================================

-- Remove role access for deployment menu
DELETE FROM `tbl_admin_role_menu_access`
WHERE `menu_id` IN (SELECT `id` FROM `tbl_admin_menus` WHERE `route_name` = 'admin.deployment.index');

-- Remove menu entry
DELETE FROM `tbl_admin_menus` WHERE `route_name` = 'admin.deployment.index';

-- Drop deployments table
DROP TABLE IF EXISTS `tbl_deployments`;

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.5.0', 'Remove: Deployment module',
    'Fully removed the Deployment module. Routes removed (10 routes), menu entry deleted, role access cleared, tbl_deployments table dropped.',
    '{"files_changed": 1, "type": "removal", "routes_removed": 10, "tables_dropped": 1}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
