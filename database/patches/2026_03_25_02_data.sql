-- =============================================
-- Admin Portal v2.1 — Part 2: Data Changes
-- Run AFTER Part 1 (schema changes)
-- Backfills password_changed_at, adds config keys, changelog
-- =============================================

-- ── Backfill note ──
-- password_changed_at is auto-backfilled by AdminAuthenticate middleware
-- on the first request after this patch. No SQL UPDATE needed.
-- Existing admins who have logged in before will NOT be forced to change password.

-- ── Configuration keys ──

INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'login_access', 'login_new_ip_notify', 'disabled', 'select', 'New IP Notification', 'Email admin when login detected from unfamiliar IP address', '["disabled","enabled"]', 'disabled', 4, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'login_new_ip_notify');

INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'advanced', 'default_timezone', 'UTC', 'text', 'Default Timezone', 'Fallback timezone for the portal (e.g. Asia/Kuala_Lumpur, UTC)', NULL, 'UTC', 6, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'default_timezone');

INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'advanced', 'items_per_page', '20', 'number', 'Items Per Page', 'Default pagination count for tables', NULL, '20', 7, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'items_per_page');

-- ── Changelog entries ──

INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT 'office', '2.0.0', 'Base Template — Portable + Secure + Production-Ready',
'Portability: all hardcoded brand names removed, dynamic portal name everywhere.\nSecurity: encrypted cookies, rate limiting, security headers, password policy, DB Manager guard, file manager auth, session encryption.\nPerformance: admin singleton per request, sidebar menu cached per role.\nNew: profile page, error pages, artisan commands, install seeder, API health, soft deletes, timezone support.\nCode: FormRequest classes, closure replaces global function, 13 controllers migrated to singleton.',
'{"files_changed": 49, "type": "base_upgrade"}', NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '2.0.0' AND `app_type` = 'office');

INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT 'office', '2.1.0', 'Security + RBAC + Performance Hardening',
'Security: CSP header, Media model mass assignment protection, force password change on first login.\nRBAC: can_create/can_edit/can_delete enforced via HTTP method mapping.\nPerformance: Configuration cross-request cache (5-min TTL).\nArchitecture: DecryptsCookie trait replaces 4 duplicates.\nDX: admin:list command, conditional HTTPS, 20 test methods.',
'{"files_changed": 17, "type": "hardening"}', NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '2.1.0' AND `app_type` = 'office');
