-- =============================================
-- System Patch: File Write Verification + Cache Hardening + Debug Panel
-- Created: 2026-05-17
-- =============================================

-- Remove diagnostic test version
DELETE FROM `tbl_versions` WHERE `version_code` = '20260517999999';

-- Remove diagnostic changelog entry
DELETE FROM `tbl_changelog` WHERE `version` = 'diag-1' AND `app_type` = 'office';

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.3.0', 'System Patch: File write verification + cache hardening + debug panel',
    'Fixed silent file write failures (file_put_contents return check + post-write hash verification + per-file opcache_invalidate). Improved cache clearing with DB cache table flush. Added Debug DB Connection panel showing ENV, connection config, live PDO, model info, and query diagnostics. Fixed silent query error in version history index. Added no-cache response headers to prevent LiteSpeed/proxy stale page caching.',
    '{"files_changed": 2, "type": "enhancement", "bugs_fixed": 3}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
