-- =============================================
-- Fix: Version History Silent Failure
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.2.2', 'Fix: Version History silent failure in System Patch',
    'The System Patch index page had a silent try-catch that swallowed all query errors, causing the version history table to vanish without any error message. Replaced with error-aware fallback that shows the actual error to administrators and attempts a raw query fallback to bypass Eloquent cast issues.',
    '{"files_changed": 2, "type": "bugfix"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
