-- =============================================
-- Theme: Blue — fix all remaining red + login curve fix
-- Created: 2026-05-19
-- =============================================

-- Core brand colors → blue
UPDATE `tbl_configuration` SET `value` = '#2563eb' WHERE `key` = 'primary' AND `group` = 'colors';
UPDATE `tbl_configuration` SET `value` = '#1d4ed8' WHERE `key` = 'primary_hover' AND `group` = 'colors';

-- Login page → dark navy gradient
UPDATE `tbl_configuration` SET `value` = 'gradient' WHERE `key` = 'login_bg_type';
UPDATE `tbl_configuration` SET `value` = '#0f172a' WHERE `key` = 'login_bg_color';
UPDATE `tbl_configuration` SET `value` = '#1e293b' WHERE `key` = 'login_bg_gradient_end';
UPDATE `tbl_configuration` SET `value` = '#1e3a5f' WHERE `key` = 'login_header_bg';
UPDATE `tbl_configuration` SET `value` = '#1e293b' WHERE `key` = 'login_header_bg_end';

-- Sidebar → blue
UPDATE `tbl_configuration` SET `value` = '#2563eb' WHERE `key` = 'sidebar_active_bg';
UPDATE `tbl_configuration` SET `value` = '#ffffff' WHERE `key` = 'sidebar_active_text';
UPDATE `tbl_configuration` SET `value` = 'rgba(37,99,235,0.1)' WHERE `key` = 'sidebar_hover_bg';
UPDATE `tbl_configuration` SET `value` = '#2563eb' WHERE `key` = 'sidebar_logo_bg';

-- Header → blue
UPDATE `tbl_configuration` SET `value` = '#2563eb' WHERE `key` = 'header_avatar_bg';

-- Catch ANY remaining dc2626 (red) in config and switch to blue
-- (skip danger/error colors which should stay red)
UPDATE `tbl_configuration` SET `value` = REPLACE(`value`, '#dc2626', '#2563eb')
WHERE `value` LIKE '%#dc2626%'
AND `key` NOT LIKE '%danger%'
AND `key` NOT LIKE '%error%';

-- Also catch 991b1b (dark red used in gradients)
UPDATE `tbl_configuration` SET `value` = REPLACE(`value`, '#991b1b', '#1e40af')
WHERE `value` LIKE '%#991b1b%'
AND `key` NOT LIKE '%danger%';

-- Flush ALL cache
DELETE FROM `cache`;

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.5.2', 'Theme: Blue — complete red removal + login curve fix',
    'Fixed login header curve overlapping title text. Comprehensive red→blue sweep: all #dc2626→#2563eb, #991b1b→#1e40af across config values (excluding danger/error colors which remain red for UX). PHP fallbacks updated to blue defaults.',
    '{"files_changed": 1, "type": "theme"}', NOW()
);
