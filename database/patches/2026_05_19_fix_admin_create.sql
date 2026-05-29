-- =============================================
-- Fix: Admin create validation feedback
-- Created: 2026-05-19
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.4.1', 'Fix: Admin create validation feedback',
    'Validation errors from Create Admin form now display as a red alert above the users table. Modal auto-reopens on validation failure so user can fix input. Form fields preserve old values via built-in old() support. Re-applied toggle-status route fix.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
