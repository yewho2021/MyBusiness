-- =============================================
-- Activity Log Module
-- Created: 2026-03-24
-- Package: spatie/laravel-activitylog
-- =============================================

-- Create activity log table (matches Spatie's schema with tbl_ prefix)
CREATE TABLE IF NOT EXISTS `tbl_activity_log` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `log_name`        VARCHAR(255)    NULL DEFAULT NULL,
    `description`     TEXT            NOT NULL,
    `subject_type`    VARCHAR(255)    NULL DEFAULT NULL,
    `subject_id`      BIGINT UNSIGNED NULL DEFAULT NULL,
    `event`           VARCHAR(255)    NULL DEFAULT NULL,
    `causer_type`     VARCHAR(255)    NULL DEFAULT NULL,
    `causer_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
    `properties`      JSON            NULL DEFAULT NULL,
    `batch_uuid`      CHAR(36)        NULL DEFAULT NULL,
    `created_at`      TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_log_name`    (`log_name`),
    KEY `idx_subject`     (`subject_type`, `subject_id`),
    KEY `idx_causer`      (`causer_type`, `causer_id`),
    KEY `idx_event`       (`event`),
    KEY `idx_created_at`  (`created_at`),
    KEY `idx_batch_uuid`  (`batch_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu entry
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES
    (2, NULL, 1, 'Activity Log', 'fas fa-shoe-prints', 'admin.activity-log.index', 'activity_log', 7, 1, NOW(), NOW());

-- Role access (administrator = role_id 1)
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.activity-log.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- Changelog entry
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '1.14.0',
    'Activity Log Module',
    'Full audit trail for all data changes across the portal.\n- Tracks create, update, delete on all key models\n- Old vs new value comparison with JSON diff\n- Filter by model, admin, action type, date range\n- Export to Excel (.xlsx) and PDF\n- Purge old entries\n- Stats dashboard (today, this week, most active admin)\n- Powered by spatie/laravel-activitylog',
    '{"package":"spatie/laravel-activitylog@4.12","table":"tbl_activity_log","models_tracked":["Admin","AdminRole","AdminMenu","AdminMenuGroup","Configuration","BackupJob","DatabaseConnection"]}',
    NOW()
);
