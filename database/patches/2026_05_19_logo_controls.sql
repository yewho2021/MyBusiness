-- =============================================
-- Logo positioning controls
-- Created: 2026-05-19
-- =============================================

-- Logo Height
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'brand', 'logo_height', '36', 'number', 'Logo Height (px)', 'Height of the sidebar logo in pixels.', NULL, '36', 5, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'logo_height');

-- Logo Alignment
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'brand', 'logo_align', 'left', 'select', 'Logo Alignment', 'Position the logo in the sidebar header.', '{"left":"Left","center":"Center","right":"Right"}', 'left', 6, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'logo_align');

-- Logo Padding
INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
SELECT 'brand', 'logo_padding', '12', 'number', 'Logo Padding (px)', 'Padding around the logo area.', NULL, '12', 7, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'logo_padding');

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.3.3', 'Logo positioning controls',
    'Added 3 new settings in Configuration → Brand: Logo Height (px), Logo Alignment (left/center/right), Logo Padding (px). Logo display modes (icon/image/both) now all respect these settings. Fixed both mode to show image + portal name.',
    '{"files_changed": 1, "type": "enhancement", "config_keys_added": 3}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
