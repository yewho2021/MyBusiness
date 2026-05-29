-- =============================================
-- Encrypted User IDs + Per-User Permission Overrides
-- Created: 2026-05-27
-- =============================================

-- ── 1. Create per-user menu access override table ──
CREATE TABLE IF NOT EXISTS `tbl_admin_user_menu_access` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `admin_id` bigint(20) unsigned NOT NULL,
    `menu_id` bigint(20) unsigned NOT NULL,
    `can_view` tinyint(1) NOT NULL DEFAULT 0,
    `can_create` tinyint(1) NOT NULL DEFAULT 0,
    `can_edit` tinyint(1) NOT NULL DEFAULT 0,
    `can_delete` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_admin_menu` (`admin_id`, `menu_id`),
    KEY `idx_admin_id` (`admin_id`),
    KEY `idx_menu_id` (`menu_id`),
    CONSTRAINT `fk_user_menu_admin` FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_menu_menu` FOREIGN KEY (`menu_id`) REFERENCES `tbl_admin_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Cache clear ──
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'config_%';
