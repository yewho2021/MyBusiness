-- =============================================
-- Admin Portal Base Upgrade v2.0.0
-- Created: 2026-03-25
-- Combines: Dynamic Branding (v1.21) + Security/DX Upgrade (v1.22)
-- Note: ALTER TABLE will skip gracefully if column already exists
--       (System Patch catches errors per statement)
-- =============================================

-- ── Soft deletes for tbl_admin ──
ALTER TABLE `tbl_admin` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;
ALTER TABLE `tbl_admin` ADD KEY `idx_deleted_at` (`deleted_at`);

-- ── Timezone per admin ──
ALTER TABLE `tbl_admin` ADD COLUMN `timezone` VARCHAR(50) NULL DEFAULT NULL AFTER `is_active`;

-- ── New IP login notification config ──
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'login_access', 'login_new_ip_notify', 'disabled', 'select', 'New IP Notification', 'Email admin when login detected from unfamiliar IP address', '["disabled","enabled"]', 'disabled', 4, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'login_new_ip_notify');

-- ── Default timezone config ──
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'advanced', 'default_timezone', 'UTC', 'text', 'Default Timezone', 'Fallback timezone for the portal (e.g. Asia/Kuala_Lumpur, UTC)', NULL, 'UTC', 6, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'default_timezone');

-- ── Items per page config ──
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'advanced', 'items_per_page', '20', 'number', 'Items Per Page', 'Default pagination count for tables', NULL, '20', 7, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'items_per_page');

-- ── Changelog ──
INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.0.0',
    'Base Template Upgrade — Portable + Secure + Production-Ready',
    'Major upgrade transforming the portal into a reusable, portable base template.\n\nPortability:\n- All hardcoded brand names removed — portal name from Configuration\n- AI export, PDF reports, watermark — all dynamic\n- Live URL reads from config(app.url)\n\nSecurity:\n- Auth cookies encrypted with Laravel encrypt()\n- Login rate limiting (5 attempts / 2-min lockout)\n- 2FA verification rate limited\n- Security headers on every response\n- Password policy: min 8, uppercase + digit\n- DB Manager restricted to Administrator role\n- File manager routes require auth\n- Session encryption enabled by default\n\nPerformance:\n- Admin singleton per request — eliminates duplicate queries\n- Sidebar menu cached per role (5-min TTL)\n\nNew features:\n- My Profile page with password change + timezone\n- Email notification on new IP login\n- Soft deletes on admin users\n- Artisan commands: admin:create, admin:reset-password\n- Error pages (403, 404, 500)\n- Pagination component + shared CSS component\n- Install seeder + INSTALL.md + .env.example\n- API foundation (/api/health)\n\nCode quality:\n- Form Request classes for validation\n- All controllers use singleton pattern\n- Menu_left closure replaces global function\n- Feature tests for auth flow',
    '{"files_changed": 49, "type": "base_upgrade", "breaking_changes": "admin_id cookie now encrypted — existing sessions auto-migrate on next login"}',
    NOW()
);
