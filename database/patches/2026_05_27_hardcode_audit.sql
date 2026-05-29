-- =============================================
-- Hardcode Audit: Domain Portability Fixes
-- Created: 2026-05-27
-- =============================================

-- ── 1. Add login_blocked_redirect config key ──
INSERT INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'login_access', 'login_blocked_redirect', 'https://google.com', 'text', 'Blocked Redirect URL',
    'Where to redirect blocked IPs (e.g. https://google.com or your company website)', NULL, 'https://google.com', 5, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'login_blocked_redirect');

-- ── 2. Changelog ──
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '5.1.2', 'Hardcode Audit: Domain Portability',
    'Full audit and removal of hardcoded branding, timezones, and domain-specific values across the codebase. The portal is now fully portable — move to any domain by changing .env and Configuration settings only.\n\n**Fixed (6 files):**\n\n1. **TelegramReportBuilder.php** — Replaced 8× hardcoded ''Asia/Kuala_Lumpur'' with new `defaultTimezone()` helper that reads from Configuration → default_timezone.\n\n2. **TelegramCron.php** — Replaced hardcoded timezone fallback with Configuration::get(''default_timezone'').\n\n3. **telegram/index.blade.php** — Test message JS now uses `portalName` and `portalTimezone` injected from PHP Configuration instead of hardcoded ''Admin Portal'', ''en-MY'', ''Asia/Kuala_Lumpur''.\n\n4. **CheckLoginAccess.php** — Blocked-IP redirect changed from hardcoded ''https://google.com'' to configurable `login_blocked_redirect` config key.\n\n5. **AppInstallCommand.php** — Removed ''SyncOffice'' branding from installer description and banner.\n\n6. **file-structure.blade.php** — AI guide prompt now uses dynamic portal_name from Configuration.\n\n**New config key:** `login_blocked_redirect` (group: login_access) — controls where blocked IPs are sent.\n\n**Verified clean:** All Blade layouts, error pages (403/404/500), TelegramController messages, config/app.php, bot tokens, and domain references already use Configuration or .env correctly.',
    '{"type":"audit","files_changed":6,"config_keys_added":1}',
    NOW()
);

-- ── 3. Cache clear ──
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'config_%';
