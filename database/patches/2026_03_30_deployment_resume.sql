-- Deployment Resume: last_activity_at column + cancelled status
ALTER TABLE `tbl_deployments` ADD COLUMN `last_activity_at` TIMESTAMP NULL AFTER `current_file`;

-- Add 'cancelled' to status enum
ALTER TABLE `tbl_deployments` MODIFY COLUMN `status` ENUM('pending','wiping','packaging','uploading','database','seeding','configuring','verifying','completed','failed','cancelled') DEFAULT 'pending';

-- Clear cache
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
