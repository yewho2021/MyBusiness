-- ============================================================
-- HOTFIX: Fix dbpassword column for encrypted storage
-- Date:  2026-03-21
-- Run this if tbl_database already exists
-- ============================================================

ALTER TABLE `tbl_database` MODIFY COLUMN `dbpassword` TEXT NOT NULL;
