-- =============================================
-- Dashboard Enhancement
-- Created: 2026-05-27
-- =============================================

-- Cache clear (new cache keys added)
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
