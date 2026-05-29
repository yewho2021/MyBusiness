-- =============================================
-- P5: Global Search + Error Pages
-- Created: 2026-03-29
-- =============================================

-- ── Changelog ──────────────────────────────────

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.6.0', 'Global Search Activated + Enhanced Error Pages',
    '**Global Search (Ctrl+K)**\n• The spotlight search that was already built into the header is now fully functional\n• Added the missing route that connects the search UI to GlobalSearchController\n• Searches across: menu pages, admin users, database tables, changelog entries, and configuration keys\n• Added to system routes so all authenticated users can search regardless of role permissions\n• Use Ctrl+K (or Cmd+K on Mac) or click the search button in the header\n\n**Enhanced Error Pages**\n• 403, 404, and 500 error pages now use portal branding (name, colors, font) from Configuration\n• Large error code watermark for instant recognition\n• 500 page includes Retry button and timestamp for support reference\n\n**RBAC Update**\n• admin.global-search added to system routes whitelist in CheckAdminMenuAccess',
    '{"features":["global-search-route","branded-error-pages"]}',
    NOW()
);
