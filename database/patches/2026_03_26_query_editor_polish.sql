-- =============================================
-- Database Manager Phase 3 — Query Editor Polish
-- Created: 2026-03-26
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.2.1',
    'Query Editor Polish',
    'Phase 3 of the Database Manager enhancement — query editor productivity improvements:\n\nColumn Auto-complete:\n- Type table_name. (with dot) to trigger column name suggestions\n- Columns fetched via DESCRIBE and cached for the session\n- Works alongside existing table name and SQL keyword completion\n\nExplain Plan:\n- New \"Explain\" button in toolbar\n- Opens a new query tab with EXPLAIN prepended to current query\n- Auto-executes immediately to show the execution plan\n\nQuery Templates:\n- Dropdown with 9 preset SQL templates across 3 groups\n- Select: SELECT basic, SELECT with JOIN, COUNT + GROUP BY\n- Modify: INSERT INTO, UPDATE WHERE, DELETE WHERE\n- Structure: CREATE TABLE, ALTER ADD COLUMN, ALTER ADD INDEX\n- Inserts at cursor if editor has content, or replaces empty editor\n\nResult Export as SQL INSERT:\n- New \"SQL\" button in results toolbar alongside JSON and CSV\n- Generates INSERT INTO statements from current result set\n- Downloads as .sql file\n\nPin Results:\n- \"Pin\" button saves current results to a persistent tab\n- Pinned tab stays open while running new queries\n- Yellow banner indicates pinned state',
    '{"files_changed": ["resources/views/admin/pages/database/query.blade.php", "resources/views/admin/pages/database/partials/query_tab.blade.php", "resources/views/admin/pages/database/partials/query_result.blade.php"]}',
    NOW()
);
