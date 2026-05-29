-- =============================================
-- Fix: Cache clear error handling
-- Created: 2026-05-17
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.4.1', 'Fix: Cache clear error handling',
    'Fixed OPcache status check (guard against false return from opcache_get_status). Switched DB cache clear from TRUNCATE to DELETE for InnoDB safety. Both new cache targets now set explicit size_freed=0 to prevent undefined JS rendering.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
