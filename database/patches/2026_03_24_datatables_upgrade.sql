-- =============================================
-- DataTables Upgrade
-- Created: 2026-03-24
-- Package: yajra/laravel-datatables
-- =============================================

-- No new tables needed — this is an enhancement to existing pages

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT
    'office', '1.19.0', 'DataTables Upgrade',
    'Upgraded portal tables to server-side DataTables.\n- Admin Users: server-side search, sort, pagination via AJAX\n- Changelog: client-side DataTables with instant search and sort\n- Backup History: client-side DataTables with status filtering\n- All tables: column visibility toggle, export buttons, responsive columns\n- Handles thousands of rows efficiently\n- Powered by yajra/laravel-datatables',
    '{"package":"yajra/laravel-datatables@11.0","pages_upgraded":["users/index","changelog/index","backup/history"]}', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '1.19.0' AND `app_type` = 'office');
