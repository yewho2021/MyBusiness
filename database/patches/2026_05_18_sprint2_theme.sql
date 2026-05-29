-- =============================================
-- Sprint 2: Zero hardcoded theme colors
-- Created: 2026-05-18
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.1.0', 'Sprint 2: Zero hardcoded theme colors',
    'Replaced all hardcoded hex theme colors across 17 blade files with CSS variables. Login, 2FA, file manager, system status, PDF suite, telegram reports, media, logs, menus, permissions, backup, database modules all now follow Configuration theme. Also re-applied default DB card fix and u365 branding fix.',
    '{"files_changed": 17, "type": "theme", "colors_fixed": 35}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
