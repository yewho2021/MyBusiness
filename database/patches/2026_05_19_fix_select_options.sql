-- =============================================
-- Fix: Select fields with key-value options
-- Created: 2026-05-19
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.3.4', 'Fix: Select fields with key-value options',
    'Fixed select field rendering in Configuration. Key-value options like {"left":"Left","center":"Center"} now correctly use keys as form values and labels as display text. Backward compatible with array-style options. Re-applied: Clear SMTP button, OPcache/DB Cache cards, ccClearAll.',
    '{"files_changed": 1, "type": "bugfix"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
