-- =============================================
-- Fix: Logo positioning applied dynamically
-- Created: 2026-05-19
-- =============================================

-- Force clear ALL cache so config values are re-read fresh
DELETE FROM `cache`;

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.3.5', 'Fix: Logo positioning applied dynamically',
    'Sidebar logo now reads logo_height, logo_align, logo_padding from Configuration and applies dynamically. Fixed both mode to show image + portal name. Cleared config cache to apply current values.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);
