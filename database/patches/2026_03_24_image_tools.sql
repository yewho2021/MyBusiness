-- =============================================
-- Image Tools Module
-- Created: 2026-03-24
-- Package: intervention/image
-- =============================================

-- Menu entry (skip if already exists from earlier attempt)
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 4, NULL, 1, 'Image Tools', 'fas fa-image', 'admin.image-tools.index', 'image_tools', 14, 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menus` WHERE `route_name` = 'admin.image-tools.index');

-- Role access (administrator = role_id 1)
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.image-tools.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- Changelog (skip if already exists)
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT
    'office', '1.16.0', 'Image Tools Module',
    'Built-in image editing toolkit.\n- Resize with fit/scale/exact modes\n- Crop with exact dimensions\n- Convert between JPEG, PNG, WEBP, GIF, BMP\n- Compress with quality slider\n- Text watermark with position, color, opacity\n- Rotate (90/180/270) and flip\n- Live preview and instant download\n- Powered by intervention/image',
    '{"package":"intervention/image@3.11"}', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '1.16.0' AND `app_type` = 'office');
