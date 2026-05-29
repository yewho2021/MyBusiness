-- =============================================
-- Redesign: Login page
-- Created: 2026-05-19
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.5.0', 'Redesign: Login page',
    'Complete visual redesign of login page. Glassmorphic logo circle, refined inputs with primary-color focus glow, fade-up animation, curved header transition, hover-lift submit button. All colors config-driven. Mobile responsive.',
    '{"files_changed": 1, "type": "redesign"}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
