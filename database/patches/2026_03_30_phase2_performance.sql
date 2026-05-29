-- =============================================
-- P28: Phase 2 — Performance Cleanup
-- Created: 2026-03-30
-- =============================================

-- ── Font/CDN source config ───────────────────

INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('general', 'font_source', 'google', 'select', 'Font Source',
     'Load fonts from Google CDN or locally. For local: upload .woff2 files to public/vendor/fonts/',
     'google|Google CDN,local|Local (Self-hosted)', 'google', 50, 1),
    ('general', 'fontawesome_source', 'cdn', 'select', 'Font Awesome Source',
     'Load Font Awesome from CDN or locally. For local: download FA to public/vendor/fontawesome/',
     'cdn|CDN (Cloudflare),local|Local (Self-hosted)', 'cdn', 51, 1);

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

DELETE FROM `cache` WHERE `key` LIKE 'config_%';
