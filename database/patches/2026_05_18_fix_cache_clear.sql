-- =============================================
-- Fix: Cache clear 500 error
-- Created: 2026-05-18
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.4.3', 'Fix: Cache clear 500 error',
    'Clean rebuild of ConfigurationController fixing 500 error on cache clear. OPcache and DB cache targets now have full guards (function_exists, is_array, inner try-catch). All result arrays explicitly set size_freed to prevent undefined JS values.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
