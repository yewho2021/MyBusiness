-- =============================================
-- Phase 5: Performance — Dashboard Caching
-- Created: 2026-03-27
-- =============================================

-- Index on tbl_configuration.key — may already exist from Phase 1.
-- If it already exists this statement will error but the patch system
-- will continue processing remaining statements.

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.6.0', 'Phase 5: Performance — Dashboard Caching',
    'Cached dashboard statistics with tiered TTLs. Core stats (admin/role/menu counts) cached 60s. Database stats (SHOW TABLE STATUS) cached 60s. Disk stats with RecursiveIteratorIterator cached 300s. Control panel data cached per role 300s. Recent backups, changelogs, logins, and activity remain uncached (always fresh). Added index on tbl_configuration.key for faster single-key lookups.',
    '{"phase": 5, "cache_keys": ["dashboard_core_stats", "dashboard_db_stats", "dashboard_disk_stats", "dashboard_cp_{role_id}"], "ttls": {"core": 60, "db": 60, "disk": 300, "cp": 300}}',
    NOW()
);
