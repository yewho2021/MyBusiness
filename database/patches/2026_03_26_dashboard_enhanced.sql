-- =============================================
-- Dashboard Enhancement: Control Panel + Widgets
-- Safe incremental patch — NO destructive operations
-- Created: 2026-03-26
-- =============================================

-- Clear sidebar cache
DELETE FROM `cache` WHERE `key` LIKE '%sidebar_menu%';

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.9.1', 'Dashboard: Control Panel + Widgets',
    'Enhanced dashboard with:\n\n1. Control Panel (cPanel-style icon grid):\n- Auto-generated from menu database — no hardcoding\n- Grouped by menu groups with color-coded icon backgrounds\n- Respects RBAC — only shows pages the admin has permission to access\n- Collapsible with toggle button (state persists visually)\n- Skips Dashboard itself and parent-only menu items\n\n2. Disk usage widget with progress bar and color coding\n3. Recent logins table (last 10) with status badges\n4. Activity timeline with color-coded event dots\n\nAll widgets are lightweight — single queries, no heavy processing.',
    '{"files_changed": ["DashboardController.php", "dashboard.blade.php"]}', NOW()
);
