-- P7 Enhancement: UI Polish + Changelog Menu
-- Safe to re-run (idempotent)

INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 4, NULL, 1, 'Changelog', 'fas fa-scroll', 'admin.changelog.index', 'changelog', 16, 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menus` WHERE `route_name` = 'admin.changelog.index');

INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.changelog.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
