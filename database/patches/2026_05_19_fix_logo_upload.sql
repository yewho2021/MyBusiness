-- =============================================
-- Fix: Logo not showing after upload
-- Created: 2026-05-19
-- =============================================

-- Fix current state: switch logo_type to 'image' if logo_image exists
UPDATE `tbl_configuration` SET `value` = 'image', `updated_at` = NOW()
WHERE `key` = 'logo_type' AND `value` = 'icon'
AND EXISTS (SELECT 1 FROM (SELECT `value` FROM `tbl_configuration` WHERE `key` = 'logo_image' AND `value` IS NOT NULL AND `value` != '') AS tmp);

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.3.1', 'Fix: Logo not showing after upload',
    'uploadImage() now auto-switches logo_type to image when logo is uploaded. removeImage() auto-resets to icon. Fixed sidebar showing FontAwesome icon instead of uploaded logo. Also re-applied: mode=blank resetGroup, opcache/db_cache cache targets, DB facade import.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
