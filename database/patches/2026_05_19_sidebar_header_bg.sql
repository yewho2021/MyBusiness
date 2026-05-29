-- =============================================
-- Configurable logo header background
-- Created: 2026-05-19
-- =============================================

-- Add sidebar_header_bg config key (empty = inherit from sidebar)
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'sidebar', 'sidebar_header_bg', '', 'color', 'Logo Header Background', 'Background color for the logo/header area. Leave empty to inherit sidebar background.', NULL, '', 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'sidebar_header_bg');

-- Flush cache so all config values apply
DELETE FROM `cache`;

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.4.0', 'Configurable logo header background',
    'Added Logo Header Background color picker in Configuration → Sidebar. Allows separate background for the logo area (e.g., white, brand color). Leave empty to inherit sidebar background. Also includes all logo positioning fixes (height, alignment, padding) and both mode fix.',
    '{"files_changed": 2, "type": "enhancement", "config_keys_added": 1}', NOW()
);
