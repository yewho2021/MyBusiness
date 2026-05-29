-- =============================================
-- Fix: Users Page Toggle Route
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.2.1', 'Fix: Users page toggle route',
    'Bugfix — /users page was throwing HTTP 500 because the activate/deactivate toggle button referenced route name admin.users.toggle instead of the correct admin.users.toggle-status.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
