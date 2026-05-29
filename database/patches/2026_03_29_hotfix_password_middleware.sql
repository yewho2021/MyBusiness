-- =============================================
-- Hotfix: admin.password middleware crash
-- Created: 2026-03-29
-- =============================================

-- Clear all stale caches (force fresh reload)
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
DELETE FROM `cache` WHERE `key` = 'config_all';

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.3.0-hotfix1', 'Hotfix: Middleware Crash',
    '**Fix:** Resolved HTTP 500 error on all pages caused by admin.password middleware alias not loading from OPcache on shared hosting.\n\n**Solution:** Password expiry check integrated directly into AdminAuthenticate middleware instead of using a separate middleware alias. This avoids OPcache stale-class issues on shared hosting where opcache_reset() may be disabled.\n\n**Files:**\n• bootstrap/app.php (removed admin.password alias)\n• routes/admin.php (removed admin.password from route group)\n• app/Http/Middleware/AdminAuthenticate.php (integrated expiry check)',
    '{"type":"hotfix","cause":"opcache_stale_bootstrap","fix":"inline_password_expiry"}',
    NOW()
);
