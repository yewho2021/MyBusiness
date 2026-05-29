-- =============================================
-- Media Library Module
-- Created: 2026-03-24
-- Package: spatie/laravel-medialibrary
-- =============================================

-- Create media table (matches Spatie's schema with tbl_ prefix)
CREATE TABLE IF NOT EXISTS `tbl_media` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `model_type`            VARCHAR(255)    NOT NULL,
    `model_id`              BIGINT UNSIGNED NOT NULL,
    `uuid`                  CHAR(36)        NULL DEFAULT NULL,
    `collection_name`       VARCHAR(255)    NOT NULL,
    `name`                  VARCHAR(255)    NOT NULL,
    `file_name`             VARCHAR(255)    NOT NULL,
    `mime_type`             VARCHAR(255)    NULL DEFAULT NULL,
    `disk`                  VARCHAR(255)    NOT NULL,
    `conversions_disk`      VARCHAR(255)    NULL DEFAULT NULL,
    `size`                  BIGINT UNSIGNED NOT NULL,
    `manipulations`         JSON            NOT NULL,
    `custom_properties`     JSON            NOT NULL,
    `generated_conversions` JSON            NOT NULL,
    `responsive_images`     JSON            NOT NULL,
    `order_column`          INT UNSIGNED    NULL DEFAULT NULL,
    `created_at`            TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`            TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_uuid` (`uuid`),
    KEY `idx_model` (`model_type`, `model_id`),
    KEY `idx_collection` (`collection_name`),
    KEY `idx_order` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Standalone media holder model row (for general uploads not tied to a specific model)
-- We use a dummy model_type + model_id=0 for orphan/general uploads
-- No extra table needed — the controller handles this.

-- Menu entry
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES
    (4, NULL, 1, 'Media Library', 'fas fa-photo-video', 'admin.media.index', 'media_library', 13, 1, NOW(), NOW());

-- Role access (administrator = role_id 1)
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.media.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- Changelog entry
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '1.15.0',
    'Media Library Module',
    'Centralized media management for the admin portal.\n- Drag & drop file upload with progress\n- Grid and list view toggle\n- Collection-based organization (General, Avatars, Documents, Logos)\n- Image preview with lightbox\n- Edit name, alt text, description, tags\n- Bulk delete\n- Auto-thumbnail generation for images\n- Disk usage stats\n- cPanel-safe file serving (no symlinks)\n- Powered by spatie/laravel-medialibrary + intervention/image',
    '{"package":"spatie/laravel-medialibrary@11.21","table":"tbl_media","storage":"storage/app/public/media"}',
    NOW()
);
