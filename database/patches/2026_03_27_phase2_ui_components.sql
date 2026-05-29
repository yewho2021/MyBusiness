-- =============================================
-- Phase 2: UI Component Foundation
-- Created: 2026-03-27
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.3.0', 'Phase 2: UI Component Foundation',
    'Introduced centralised component CSS (public/css/components.css) with sc-* class system using CSS variables from Configuration. Created 10 reusable Blade components: card, modal, button, form-group, input, select, alert, badge, stat-card, confirm-modal. Added shared JS helpers (public/js/components.js) for unified modal open/close with Escape key and overlay click support. Migrated 3 pilot views (roles/index, users/index, changelog/index) to use components — removed all per-page style blocks. Updated app.blade.php layout to load component CSS and JS.',
    '{"phase": 2, "components_created": 10, "views_migrated": 3, "css_lines_removed": 234, "files_changed": 16}',
    NOW()
);
