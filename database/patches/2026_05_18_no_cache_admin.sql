-- =============================================
-- Fix: Prevent Cloudflare caching all admin pages
-- Created: 2026-05-18
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.5.1', 'Fix: Prevent Cloudflare caching all admin pages',
    'Added Cache-Control no-store headers to AdminAuthenticate middleware. All admin page responses now include no-cache headers, preventing Cloudflare/proxy from serving stale content. Fixes default DB not showing on connections page and other stale-page issues.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
