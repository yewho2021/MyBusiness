-- Deployment Enhancement: file comparison + progress tracking
ALTER TABLE `tbl_deployments` ADD COLUMN `file_manifest` LONGTEXT NULL AFTER `log`;
ALTER TABLE `tbl_deployments` ADD COLUMN `current_file` VARCHAR(500) NULL AFTER `file_manifest`;

-- Clear cache
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
