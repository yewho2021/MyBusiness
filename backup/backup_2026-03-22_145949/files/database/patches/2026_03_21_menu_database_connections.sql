-- ============================================================
-- Patch: Update Database menu to point to Connections page
-- Date:  2026-03-21
-- Description: Change the Database Manager sidebar link from
--              /database/query to /database/connections
-- ============================================================

UPDATE tbl_admin_menus 
SET route_name = 'admin.database.connections.index' 
WHERE route_name = 'admin.database.query';

-- If the menu points to admin.database.index instead, also catch that:
UPDATE tbl_admin_menus 
SET route_name = 'admin.database.connections.index' 
WHERE route_name = 'admin.database.index';
