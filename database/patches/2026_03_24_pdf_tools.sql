-- =============================================
-- PDF Tools Module
-- Created: 2026-03-24
-- Package: barryvdh/laravel-dompdf
-- =============================================

-- Saved templates table
CREATE TABLE IF NOT EXISTS `tbl_pdf_templates` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(255)    NOT NULL,
    `description`   TEXT            NULL DEFAULT NULL,
    `html_content`  LONGTEXT        NOT NULL,
    `paper_size`    VARCHAR(20)     NOT NULL DEFAULT 'a4',
    `orientation`   VARCHAR(20)     NOT NULL DEFAULT 'portrait',
    `margins`       JSON            NULL DEFAULT NULL,
    `created_by`    BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at`    TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu entry
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 4, NULL, 1, 'PDF Tools', 'fas fa-file-pdf', 'admin.pdf-tools.index', 'pdf_tools', 15, 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menus` WHERE `route_name` = 'admin.pdf-tools.index');

-- Role access (administrator = role_id 1)
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.pdf-tools.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT
    'office', '1.17.0', 'PDF Tools Module',
    'PDF generation toolkit for the admin portal.\n- HTML to PDF converter with live preview\n- 6 pre-built report templates (Admin Users, Login Log, Configuration, Backup Summary, Changelog, Role Permissions)\n- Save and reuse custom HTML templates\n- Paper size, orientation, and margin controls\n- Powered by barryvdh/laravel-dompdf',
    '{"package":"barryvdh/laravel-dompdf@3.1","table":"tbl_pdf_templates"}', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '1.17.0' AND `app_type` = 'office');
