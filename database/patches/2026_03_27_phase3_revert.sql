-- =============================================
-- Phase 3 Revert: Restore original view files
-- Created: 2026-03-27
-- =============================================
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.4.1', 'Phase 3 Revert: Restore per-page CSS',
    'Reverted bulk CSS extraction. Original view files restored with their per-page style blocks intact. The centralised approach caused class name conflicts between modules. Phase 2 pilot views (roles, users, changelog) kept with sc-* components. modules.css emptied.',
    '{"phase": "3-revert", "reason": "CSS class conflicts between modules when merged into single file"}',
    NOW()
);
