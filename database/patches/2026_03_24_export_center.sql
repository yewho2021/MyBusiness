-- =============================================
-- Export Center Module
-- Created: 2026-03-24
-- Package: maatwebsite/excel + barryvdh/laravel-dompdf
-- =============================================

-- Export history table
CREATE TABLE IF NOT EXISTS `tbl_export_history` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `source`        VARCHAR(100)    NOT NULL,
    `format`        ENUM('xlsx','csv','pdf') NOT NULL DEFAULT 'xlsx',
    `file_path`     VARCHAR(500)    NULL DEFAULT NULL,
    `file_name`     VARCHAR(255)    NULL DEFAULT NULL,
    `file_size`     BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `filters`       JSON            NULL DEFAULT NULL,
    `row_count`     INT UNSIGNED    NOT NULL DEFAULT 0,
    `admin_id`      BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at`    TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_source` (`source`),
    KEY `idx_admin_id` (`admin_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu entry
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 4, NULL, 1, 'Export Center', 'fas fa-download', 'admin.export.index', 'export_center', 16, 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menus` WHERE `route_name` = 'admin.export.index');

-- Role access (administrator = role_id 1)
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.export.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT
    'office', '1.18.0', 'Export Center Module',
    'Unified data export hub for the admin portal.\n- Export any portal data to Excel, CSV, or PDF\n- 7 data sources: Admin Users, Login Log, Activity Log, Configuration, Backup History, Changelog, Custom SQL\n- Step-by-step wizard (select source → apply filters → choose format)\n- Preview first 10 rows before exporting\n- Export history with re-download\n- Styled Excel with headers, colors, auto-width\n- Powered by maatwebsite/excel + barryvdh/laravel-dompdf',
    '{"packages":"maatwebsite/excel@3.1, barryvdh/laravel-dompdf@3.1","table":"tbl_export_history"}', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '1.18.0' AND `app_type` = 'office');
