-- =============================================
-- Fix: Default DB always visible on Connections page
-- Created: 2026-05-18
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.5.2', 'Fix: Default DB always visible on Connections page',
    'The default .env database card was hidden behind an isEmpty conditional that showed empty state instead. Moved the default card outside the conditional so it always renders alongside any saved connections.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
