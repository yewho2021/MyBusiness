-- =============================================
-- Sprint 1b: Form Request Extraction
-- Created: 2026-05-18
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '4.0.1', 'Sprint 1b: Form Request extraction',
    'Extracted inline validation from 4 controllers (DatabaseConnection, Role, Menu, Backup) into 9 dedicated Form Request classes. Total Form Requests: 3 → 12. Inline validate blocks removed: 12. Validation rules now centralized and reusable.',
    '{"files_new": 9, "files_changed": 4, "type": "refactor", "form_requests_added": 9, "inline_validates_removed": 12}', NOW()
);

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
