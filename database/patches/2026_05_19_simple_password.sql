-- =============================================
-- Simplified password requirements
-- Created: 2026-05-19
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.4.2', 'Simplified password requirements',
    'Removed uppercase and digit requirements from admin password validation. New rule: minimum 6 characters + confirmation match. Applies to both create and update.',
    '{"files_changed": 2, "type": "enhancement"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
