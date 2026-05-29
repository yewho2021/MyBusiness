-- =============================================
-- Configurable sidebar header padding
-- Created: 2026-05-19
-- =============================================

INSERT INTO `tbl_configuration` (`group`, `key`, `value`, `type`, `label`, `description`, `default_value`, `sort_order`, `is_active`)
SELECT 'sidebar', 'sidebar_header_padding', '20', 'number', 'Logo Area Padding (px)', 'Padding around the sidebar logo/header area. Increase for more space, decrease for compact.', '20', 2, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_configuration` WHERE `key` = 'sidebar_header_padding');

INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES ('office', '4.5.4', 'Configurable sidebar header padding', 'Added Logo Area Padding setting in Configuration → Sidebar. Controls the spacing around the sidebar logo area. Default 20px.', '{"files_changed": 1, "config_keys_added": 1}', NOW());

DELETE FROM `cache`;
