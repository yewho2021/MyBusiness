-- =============================================
-- P33: Phase 4 — Auth Hardening
-- Created: 2026-03-30
-- =============================================

-- ── Account lockout columns ──────────────────

ALTER TABLE `tbl_admin`
    ADD COLUMN `failed_login_count` int(11) NOT NULL DEFAULT 0 AFTER `password_changed_at`;

ALTER TABLE `tbl_admin`
    ADD COLUMN `locked_at` timestamp NULL DEFAULT NULL AFTER `failed_login_count`;

ALTER TABLE `tbl_admin`
    ADD COLUMN `lock_reason` varchar(255) DEFAULT NULL AFTER `locked_at`;

-- ── Security config rows ─────────────────────

INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('security', 'admin_cookie_lifetime_minutes', '480', 'number',
     'Session Cookie Lifetime (minutes)', 'How long admin login cookie lasts. Default: 480 (8 hours). Set 0 for browser-session only.',
     NULL, '480', 10, 1),
    ('security', 'max_failed_logins', '10', 'number',
     'Max Failed Logins Before Lockout', 'Account locks after this many consecutive failed password attempts. Set 0 to disable lockout.',
     NULL, '10', 11, 1);

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

DELETE FROM `cache` WHERE `key` LIKE 'config_%';
