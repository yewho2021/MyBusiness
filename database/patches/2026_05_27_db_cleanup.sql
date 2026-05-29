-- =============================================
-- Database Cleanup: Remove All Tenant-Specific Data
-- Created: 2026-05-27
-- =============================================

-- ── 1. Remove SupportPal changelog entries (largest noise — ~30+ entries) ──
DELETE FROM `tbl_changelog`
WHERE `title` LIKE '%SupportPal%'
   OR `details` LIKE '%SupportPal%'
   OR `title` LIKE '%supportpal%';

-- ── 2. Remove tenant-specific changelog entries ──
DELETE FROM `tbl_changelog`
WHERE `details` LIKE '%afirst%'
   OR `details` LIKE '%mybusiness%'
   OR `details` LIKE '%u365%'
   OR `details` LIKE '%office.mybusiness.com.my%'
   OR `details` LIKE '%apps.mybusiness.com.my%'
   OR `details` LIKE '%afirst_supportpal%'
   OR `title` LIKE '%u365%';

-- ── 3. Remove SupportPal configuration keys (if any remain) ──
DELETE FROM `tbl_configuration` WHERE `group` = 'supportpal';

-- ── 4. Remove SupportPal menu group (if any remain) ──
DELETE FROM `tbl_admin_menus` WHERE `group_id` NOT IN (SELECT `id` FROM `tbl_admin_menu_groups`);
DELETE FROM `tbl_admin_menu_groups` WHERE `slug` = 'supportpal' OR `title` = 'SUPPORTPAL';
DELETE FROM `tbl_admin_role_menu_access` WHERE `menu_id` NOT IN (SELECT `id` FROM `tbl_admin_menus`);

-- ── 5. Clean tenant-specific activity_log entries ──
DELETE FROM `tbl_activity_log`
WHERE `properties` LIKE '%supportpal%'
   OR `properties` LIKE '%afirst_supportpal%'
   OR `properties` LIKE '%SupportPal%';

-- ── 6. Clean backup_runs with hardcoded paths ──
-- These contain /home/mybusiness/ paths in include_paths/exclude_paths
UPDATE `tbl_backup_runs` SET `include_paths` = NULL WHERE `include_paths` LIKE '%mybusiness%';
UPDATE `tbl_backup_runs` SET `exclude_paths` = NULL WHERE `exclude_paths` LIKE '%mybusiness%';

-- ── 7. Fix tbl_telegram_reports timezone column default ──
-- Change hardcoded 'Asia/Kuala_Lumpur' default to NULL (uses Configuration at runtime)
ALTER TABLE `tbl_telegram_reports`
    ALTER COLUMN `timezone` SET DEFAULT NULL;

-- ── 8. Fix tbl_telegram_subscriptions timezone column default ──
ALTER TABLE `tbl_telegram_subscriptions`
    ALTER COLUMN `timezone` SET DEFAULT NULL;

-- ── 9. Clear the serialized cache row (contains stale portal_name 'AFirst') ──
DELETE FROM `cache` WHERE `key` LIKE 'config_%';
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

-- ── 10. Clean stale backup_runs stuck in 'running' status ──
UPDATE `tbl_backup_runs`
SET `status` = 'failed', `error_message` = 'Marked as failed during DB cleanup (was stuck in running state)'
WHERE `status` = 'running';
