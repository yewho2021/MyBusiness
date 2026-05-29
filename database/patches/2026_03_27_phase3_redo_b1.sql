-- =============================================
-- Phase 3 Redo Batch 1: View Component Migration
-- Created: 2026-03-27
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.4.2', 'Phase 3 Redo Batch 1: View Component Migration',
    'Rewrote 7 views to use sc-* Blade components. 5 placeholder stubs (activity-log, settings/general, settings/security, reports/analytics, reports/sales) now use x-card with zero per-page CSS. roles/edit rewritten with x-card, x-form-group, x-input, x-button, x-alert — CSS reduced from 28 to 3 lines (responsive only). profile/index rewritten with x-card, x-form-group, x-input, x-badge, x-alert, sc-table — CSS reduced from 45 to 6 lines (page-specific layout only). Approach: individual view rewrites, NOT bulk CSS extraction.',
    '{"phase": "3-redo-b1", "views_migrated": 7, "approach": "individual_rewrite"}',
    NOW()
);
