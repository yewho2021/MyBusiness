-- =============================================
-- Database Manager Enhancement — Full (Phase 1-4)
-- Created: 2026-03-26
-- =============================================

-- Phase 1+2: Export + Context Menu + Sidebar + Overview
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.2.0',
    'Database Manager Enhancement',
    'Major Database Manager upgrade with AJAX export, enhanced context menu, and sidebar improvements:\n\nExport Tab Redesign:\n- AJAX-powered export with real-time phase-level log\n- Format selector: .sql or .zip (compressed)\n- Per-table row count, size, and relative size bar\n- Table filter input with Select All / Deselect All\n- Log panel with phase timing and download button\n\nEnhanced Context Menu (14 items, 6 groups):\n- Browse: View Data, SELECT TOP 100\n- Inspect: DESCRIBE, Show CREATE TABLE, Show Indexes, Table Status\n- Quick Stats: Count Rows\n- Operations: Truncate Table (confirm), Drop Table (type name to confirm)\n- Copy: Table Name, SELECT *, INSERT Template (auto-fetches columns)\n- Export: Export single table\n\nSidebar: Sort buttons (Name/Rows/Size)\nOverview: Checkboxes, batch toolbar, size bars, right-click context menu\nController: Fixed Laravel 11 HasMiddleware, new exportAjax/exportDownload',
    '{"phase": "1+2", "files_changed": ["DatabaseController.php", "query.blade.php", "export_tab.blade.php", "database_summary.blade.php", "admin.php"]}',
    NOW()
);

-- Phase 3: Query Editor Polish
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.2.1',
    'Query Editor Polish',
    'Phase 3 — query editor productivity improvements:\n- Column auto-complete: type table_name. to trigger column suggestions (cached per session)\n- Explain plan button: opens EXPLAIN in new tab, auto-executes\n- Query templates dropdown: 9 presets (SELECT, JOIN, INSERT, UPDATE, DELETE, CREATE TABLE, ALTER)\n- Export results as SQL INSERT statements\n- Pin results: save current results to a persistent tab',
    '{"phase": "3", "files_changed": ["query.blade.php", "query_tab.blade.php", "query_result.blade.php"]}',
    NOW()
);

-- Phase 4: ER Diagram
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.3.0',
    'Visual ER Diagram',
    'Phase 4 — interactive Entity-Relationship diagram:\n- Auto-generated from database schema (SHOW FULL COLUMNS + information_schema)\n- Detects explicit foreign keys from KEY_COLUMN_USAGE\n- Infers relationships from naming conventions (column_id → table.id)\n- Interactive pan (drag) and zoom (scroll wheel/buttons)\n- Auto-fit layout on load with Fit button\n- Grid-based auto-layout sorted by connection count\n- Color-coded: PRI keys red, FK/MUL keys blue\n- Solid red lines for explicit FKs, dashed gray for inferred\n- Bezier curve connectors between related columns\n- Double-click table box to open data view\n- Legend and connection count badges\n- Dot-grid background for visual reference\n- New sidebar button: ER Diagram (pink accent)',
    '{"phase": "4", "files_changed": ["DatabaseController.php", "query.blade.php", "admin.php"], "new_routes": ["admin.database.er-diagram"]}',
    NOW()
);
