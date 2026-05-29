-- =============================================
-- Admin Portal v2.1 — Part 1: Schema Changes
-- Run FIRST — adds columns/indexes to tbl_admin
-- Safe to re-run (errors on existing columns are skipped)
-- =============================================

ALTER TABLE `tbl_admin` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

ALTER TABLE `tbl_admin` ADD KEY `idx_deleted_at` (`deleted_at`);

ALTER TABLE `tbl_admin` ADD COLUMN `timezone` VARCHAR(50) NULL DEFAULT NULL AFTER `is_active`;

ALTER TABLE `tbl_admin` ADD COLUMN `password_changed_at` TIMESTAMP NULL DEFAULT NULL AFTER `datetime_lastlogin`;
