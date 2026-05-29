-- =============================================
-- Configuration: Clear SMTP + Enhanced cache clearing
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.4.0', 'Configuration: Clear SMTP + Enhanced cache clearing',
    'Added Clear SMTP Settings button on Email tab — blanks all 14 email config values in one click. Added PHP OPcache and Database Cache Table cards to cache management grid. Clear Everything now includes opcache_reset() and cache/cache_locks table truncation. Reset group endpoint now supports mode=blank for clearing to empty vs mode=defaults for restoring defaults.',
    '{"files_changed": 2, "type": "enhancement"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
