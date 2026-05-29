-- =============================================
-- Phase 5: Feature Enhancements
-- Safe incremental patch — NO destructive operations
-- Created: 2026-03-26
-- =============================================

-- Add password expiry config
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `default_value`, `sort_order`, `is_active`, `updated_at`)
SELECT 'security', 'password_expiry_days', '0', 'number', 'Password Expiry (days)', 'Force password change after N days. Set 0 to disable.', '0', 10, 1, NOW()
FROM DUAL WHERE NOT EXISTS (
    SELECT 1 FROM `tbl_configuration` WHERE `key` = 'password_expiry_days'
);

-- Clear sidebar cache
DELETE FROM `cache` WHERE `key` LIKE '%sidebar_menu%';

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.8.0', 'Phase 5: Feature Enhancements',
    'New features:\n\n1. make:admin-module command — scaffolds controller, view, route, SQL patch\n2. Dashboard widgets — disk usage, recent logins, activity timeline\n3. Password expiry middleware — configurable via Configuration > security',
    '{"phase": "P5", "features": 3}', NOW()
);
