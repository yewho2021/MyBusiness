-- =============================================
-- Fix: Favicon upload
-- Created: 2026-05-19
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.5.3', 'Fix: Favicon upload',
    'Fixed ICO file upload rejection. Changed validation from required|image|mimes to required|file|mimes — the image rule only accepts jpeg/png/gif/svg/webp, blocking ICO files. Re-applied: DB import, mode=blank, opcache/db_cache, logo auto-switch.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
