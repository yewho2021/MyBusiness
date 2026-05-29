-- =============================================
-- Fix: Logo sizing and display modes
-- Created: 2026-05-19
-- =============================================

-- Add logo_height config key
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `default_value`, `sort_order`, `is_active`)
SELECT 'brand', 'logo_height', '36', 'number', 'Logo Height (px)', 'Height of the sidebar logo image in pixels. Adjust if logo appears too small or too large.', '36', 5, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'logo_height');

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.3.2', 'Fix: Logo sizing and display modes',
    'Logo height now configurable via Configuration → Brand → Logo Height (default 36px). Fixed both mode to show image + portal name together. Logo max-width adapts to sidebar width. Removed hardcoded 40px max-height.',
    '{"files_changed": 1, "type": "bugfix", "config_keys_added": 1}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
