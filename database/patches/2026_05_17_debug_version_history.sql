-- =============================================
-- Debug: Version History Connection Diagnostics
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.2.3', 'Debug: Version History connection diagnostics',
    'Added Debug DB Connection button to System Patch page. Shows: ENV values, default connection config, live PDO database, Version model connection, raw DB::table query results vs Eloquent query results. Helps diagnose why newly applied patches may not appear in version history.',
    '{"files_changed": 2, "type": "diagnostic"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
