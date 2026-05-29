-- =============================================
-- Phase 3: Full UI CSS Centralisation (All Batches)
-- Created: 2026-03-27
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.4.0', 'Phase 3: Full UI CSS Centralisation',
    'Consolidated 4,491 lines of per-page CSS from 39 Blade views into public/css/modules.css. Every per-page <style> block has been removed. All legacy class names now load from one central file using CSS variables from Configuration::cssVariables(). Modules covered: database manager (index, table, query, export, import, connections, history), backup (dashboard, history, restore, jobs, logs), permissions matrix, file manager, admin log, activity log, media library, menus, dashboard, configuration, users edit, roles edit, profile, system patch, file structure, export center, charts, PDF tools, PDF suite, image tools. Updated layout to load modules.css alongside components.css.',
    '{"phase": 3, "css_lines_removed": 4491, "files_migrated": 39, "style_blocks_remaining": 0, "central_css": "public/css/modules.css"}',
    NOW()
);
