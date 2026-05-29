-- =============================================
-- UX: Auto-apply on green preview
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.3.1', 'UX: Auto-apply on green preview',
    'Removed debug panel. Preview now auto-triggers Apply when all checks pass (zero SQL errors, zero blocked files, no duplicate warning). Button renamed to Preview & Apply. Confirm dialog removed — preview validation is sufficient. Manual Apply button still shown when warnings exist for review.',
    '{"files_changed": 2, "type": "enhancement"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
