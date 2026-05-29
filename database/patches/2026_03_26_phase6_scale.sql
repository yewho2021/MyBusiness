-- =============================================
-- Phase 6: Scale Prep
-- Safe incremental patch — NO destructive operations
-- Created: 2026-03-26
-- =============================================

-- Clear sidebar cache
DELETE FROM `cache` WHERE `key` LIKE '%sidebar_menu%';

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.9.0', 'Phase 6: Scale Prep',
    'Infrastructure and UX improvements:\n\n1. Global Search (Ctrl+K):\n- Spotlight-style search modal accessible from any page\n- Searches: menu pages, admin users, database tables, changelog entries, configuration settings\n- Keyboard navigation: Arrow Up/Down to select, Enter to go, ESC to close\n- Grouped results with type icons and color coding\n- Debounced input (250ms) for smooth performance\n- Added Ctrl+K button in header bar\n\n2. Docker support:\n- Dockerfile: PHP 8.2 + Apache + all extensions + Composer + Node 18\n- docker-compose.yml: app + MySQL 8 + phpMyAdmin\n- One command local dev: docker-compose up -d\n\nManual step required: add route to routes/admin.php (see PHASE6_SETUP.md)',
    '{"phase": "P6", "features": 2}', NOW()
);
