-- =============================================
-- DIAGNOSTIC: Pure SQL Version Insert Test
-- Created: 2026-05-17
-- =============================================

-- 1. Insert a test version directly via SQL (bypassing Eloquent)
--    version_code 20260517999999 will sort ABOVE everything else
INSERT INTO `tbl_versions`
    (`version_code`, `type`, `file_name`, `description`, `status`, `admin_name`, `applied_at`, `elapsed_ms`, `code_files`, `sql_files`, `files_created`, `files_overwritten`, `sql_ok`, `sql_err`, `total_backup_bytes`)
VALUES
    ('20260517999999', 'patch', '*** SQL_DIAGNOSTIC_TEST ***', 'If you see this row in Version History → DB query works fine. The issue is code file deployment, not database.', 'success', 'SQL Diagnostic', NOW(), 0, 0, 1, 0, 0, 1, 0, 0);

-- 2. Log DATABASE() and connection info to changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT
    'office', 'diag-1', 'SQL Diagnostic Result',
    CONCAT(
        'DATABASE(): ', DATABASE(), '\n',
        'USER(): ', USER(), '\n',
        'VERSION(): ', VERSION(), '\n',
        'Total rows in tbl_versions: ', (SELECT COUNT(*) FROM tbl_versions), '\n',
        'Latest version_code: ', IFNULL((SELECT MAX(version_code) FROM tbl_versions), 'NONE'), '\n',
        'Oldest version_code: ', IFNULL((SELECT MIN(version_code) FROM tbl_versions), 'NONE')
    ),
    '{"type": "diagnostic"}', NOW();

-- 3. Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
