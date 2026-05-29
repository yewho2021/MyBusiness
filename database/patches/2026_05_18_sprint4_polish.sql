-- =============================================
-- Sprint 4: Mobile, Accessibility, 2FA Enforcement
-- Created: 2026-05-18
-- =============================================

-- 2FA enforcement config key
INSERT INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 'security', 'require_2fa_administrator', '0', 'boolean', 'Require 2FA for Administrators',
    'When enabled, Administrator-role users must set up Two-Factor Authentication. They will be redirected to their profile page until 2FA is enabled.',
    NULL, '0', 10, 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'require_2fa_administrator');

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.3.0', 'Sprint 4: Mobile, Accessibility, 2FA Enforcement',
    'Accessibility: skip-to-content link, ARIA landmarks (role=main, role=dialog on modals, aria-label on icon buttons, aria-live toast container). Mobile: global table overflow CSS, responsive stats grid. Security: 2FA enforcement toggle in Configuration → Security — redirects administrators without 2FA to profile page. Re-applied no-cache headers in AdminAuthenticate.',
    '{"files_changed": 2, "type": "sprint", "config_keys_added": 1, "aria_features": 5}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
