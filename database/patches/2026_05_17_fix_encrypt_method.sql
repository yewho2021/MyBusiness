-- =============================================
-- Fix: Missing encryptIfSensitive() on Configuration model
-- Created: 2026-05-17
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.4.2', 'Fix: Missing encryptIfSensitive() on Configuration model',
    'Added the missing encryptIfSensitive() static method to Configuration model. ConfigurationController called this method on save and import but it was never defined, causing 500 errors on any config save operation.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
