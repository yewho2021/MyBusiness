-- =============================================
-- Phase 3: Scheduling & Cron
-- Created: 2026-05-27
-- =============================================

-- Sync default_timezone if still UTC
UPDATE `tbl_configuration`
SET `value` = 'Asia/Kuala_Lumpur'
WHERE `key` = 'default_timezone'
  AND (`value` IS NULL OR `value` = '' OR `value` = 'UTC');

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'config_%';
