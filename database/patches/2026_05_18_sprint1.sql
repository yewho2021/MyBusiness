-- =============================================
-- Sprint 1: Performance & Safety
-- Created: 2026-05-18
-- =============================================

-- 1. CLEAN ORPHAN ROWS (before FK creation)
DELETE FROM `tbl_admin` WHERE `role_id` NOT IN (SELECT `id` FROM `tbl_admin_roles`) AND `role_id` IS NOT NULL;
DELETE FROM `tbl_admin_role_menu_access` WHERE `role_id` NOT IN (SELECT `id` FROM `tbl_admin_roles`);
DELETE FROM `tbl_admin_role_menu_access` WHERE `menu_id` NOT IN (SELECT `id` FROM `tbl_admin_menus`);
UPDATE `tbl_admin_menus` SET `group_id` = NULL WHERE `group_id` NOT IN (SELECT `id` FROM `tbl_admin_menu_groups`) AND `group_id` IS NOT NULL;
DELETE FROM `tbl_backup_runs` WHERE `job_id` NOT IN (SELECT `id` FROM `tbl_backup_jobs`) AND `job_id` IS NOT NULL;
DELETE FROM `tbl_backup_logs` WHERE `run_id` NOT IN (SELECT `id` FROM `tbl_backup_runs`) AND `run_id` IS NOT NULL;
DELETE FROM `tbl_version_code` WHERE `version_id` NOT IN (SELECT `id` FROM `tbl_versions`) AND `version_id` IS NOT NULL;

-- 2. FOREIGN KEYS Phase 1 (each may fail if exists — system patch catches per stmt)
ALTER TABLE `tbl_admin` ADD CONSTRAINT `fk_admin_role` FOREIGN KEY (`role_id`) REFERENCES `tbl_admin_roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `tbl_admin_role_menu_access` ADD CONSTRAINT `fk_rma_role` FOREIGN KEY (`role_id`) REFERENCES `tbl_admin_roles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tbl_admin_role_menu_access` ADD CONSTRAINT `fk_rma_menu` FOREIGN KEY (`menu_id`) REFERENCES `tbl_admin_menus`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tbl_backup_runs` ADD CONSTRAINT `fk_run_job` FOREIGN KEY (`job_id`) REFERENCES `tbl_backup_jobs`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tbl_backup_logs` ADD CONSTRAINT `fk_log_run` FOREIGN KEY (`run_id`) REFERENCES `tbl_backup_runs`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tbl_version_code` ADD CONSTRAINT `fk_vcode_version` FOREIGN KEY (`version_id`) REFERENCES `tbl_versions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 3. PERFORMANCE INDEXES (each may fail if exists)
ALTER TABLE `tbl_admin_log` ADD INDEX `idx_admin_log_admin_login` (`admin_id`, `login_at`);
ALTER TABLE `tbl_activity_log` ADD INDEX `idx_activity_causer` (`causer_type`, `causer_id`, `created_at`);
ALTER TABLE `tbl_changelog` ADD INDEX `idx_changelog_app_date` (`app_type`, `created_at`);

-- 4. CHART PALETTE CONFIG KEYS
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 'colors', 'chart_color_6', '#8b5cf6', 'color', 'Chart Color 6', 'Extra chart series color (purple)', NULL, '#8b5cf6', 50, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'chart_color_6');

INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 'colors', 'chart_color_7', '#ec4899', 'color', 'Chart Color 7', 'Extra chart series color (pink)', NULL, '#ec4899', 51, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'chart_color_7');

INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 'colors', 'chart_color_8', '#14b8a6', 'color', 'Chart Color 8', 'Extra chart series color (teal)', NULL, '#14b8a6', 52, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'chart_color_8');

-- 5. CHANGELOG
INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES ('office', '4.0.0', 'Sprint 1: Performance & Safety',
'Security: Login + 2FA rate-limited (5/min). Telegram bot token encrypted at rest via Crypt (backward-compatible with plaintext). Health endpoint expanded (OPcache, disk, backup age, sessions, latest patch). u365 branding fully replaced. Chart palette theme-aware via CSS vars + 3 config color keys. Database: 6 FK constraints, 3 performance indexes, orphan row cleanup.',
'{"files_changed": 4, "fk_added": 6, "indexes_added": 3, "config_keys_added": 3}', NOW());

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
