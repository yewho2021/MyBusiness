-- =============================================
-- Combined Patch: P1 Security + P2 Performance + P3 Install + P4 Quality
-- Safe incremental changes ONLY — no DROP TABLE, no schema wipe
-- Run in: Database Manager or phpMyAdmin
-- Created: 2026-03-26
-- =============================================

-- ─── P1: Add admin_id to query_history ────────────────
ALTER TABLE `tbl_query_history`
    ADD COLUMN `admin_id` BIGINT UNSIGNED NULL AFTER `id`;

ALTER TABLE `tbl_query_history`
    ADD INDEX `idx_admin_id` (`admin_id`);

-- ─── Clear stale sidebar cache ────────────────────────
DELETE FROM `cache` WHERE `key` LIKE '%sidebar_menu%';

-- ─── P1 Changelog ────────────────────────────────────
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.5.0', 'Phase 1: Security Hardening',
    'Critical security fixes:\n- SQL injection fix in deleteRow (parameterized query)\n- Session regeneration after login\n- Password policy min:8 enforced in HTML\n- Legacy plain-numeric cookie fallback removed\n- admin_id added to query_history (audit trail)\n- file_put_contents error checks added (SystemPatch, Export, Database)\n- Hardcoded timezone removed (now uses app timezone)',
    '{"phase": "P1", "fixes": 7}', NOW()
);

-- ─── P2 Changelog ────────────────────────────────────
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.5.1', 'Phase 2: Performance & Reliability',
    'Performance improvements:\n- Eager loading with(role) on Admin queries (N+1 fix)\n- DB::transaction on permissions bulk update\n- Changelog paginated (25/page instead of load-all)\n- RoleController clears sidebar cache on delete/toggle\n- mysql_dbmanager connection timeout (10s)',
    '{"phase": "P2", "fixes": 5}', NOW()
);

-- ─── P3 Changelog ────────────────────────────────────
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.6.0', 'Phase 3: Install & Documentation',
    'Install and documentation package:\n- .env.example with all required variables\n- INSTALL.md step-by-step guide\n- README.md project overview\n- database/schema.sql for fresh installs\n- php artisan app:install command\n- Default admin password updated to Admin@1234 in seeder',
    '{"phase": "P3", "files": 6}', NOW()
);

-- ─── P4 Changelog ────────────────────────────────────
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.7.0', 'Phase 4: Code Quality & Testing',
    'Testing and quality tooling:\n- 38 tests across 5 files (Auth, Dashboard, AdminCrud, Security, AdminModel)\n- AdminAuthHelper trait for cookie-based test auth\n- phpunit.xml with array drivers for fast tests\n- pint.json code formatting config\n- phpstan.neon Larastan level 5 static analysis',
    '{"phase": "P4", "tests": 38}', NOW()
);
