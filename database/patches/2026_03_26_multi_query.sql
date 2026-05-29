-- =============================================
-- Multi-Query Execution Support
-- Created: 2026-03-26
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.3.1',
    'Multi-Query Execution',
    'Database Manager now supports executing multiple SQL statements in one go:\n- Paste multiple statements separated by semicolons\n- Each statement executes individually with its own status (OK / Error)\n- Summary bar shows total success/fail count and total time\n- Per-statement: expandable result table, affected rows count, execution time\n- SQL preview shows truncated statement in header\n- Errors dont stop execution — remaining statements still run\n- Single statements work exactly as before (backward compatible)\n- Uses existing robust SQL splitter that handles quoted strings and comments',
    '{"files_changed": ["app/Http/Controllers/Admin/DatabaseController.php", "resources/views/admin/pages/database/partials/query_result_multi.blade.php"]}',
    NOW()
);
