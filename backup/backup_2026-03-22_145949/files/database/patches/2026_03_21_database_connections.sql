-- ============================================================
-- Patch: Database Connections Management
-- Date:  2026-03-21
-- Description: Create tbl_database for storing saved DB connections
-- ============================================================

CREATE TABLE IF NOT EXISTS `tbl_database` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COMMENT 'Friendly label for this connection',
    `dbhost` VARCHAR(255) NOT NULL DEFAULT 'localhost',
    `dbport` VARCHAR(10) NOT NULL DEFAULT '3306',
    `dbname` VARCHAR(255) NOT NULL,
    `dbusername` VARCHAR(255) NOT NULL,
    `dbpassword` TEXT NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_connected_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add changelog entry
INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES
('office', '1.7.0', 'Database Connections Manager', 'Added ability to save and manage multiple database connections. Users can now store external database credentials and browse them using the existing Database Manager tools.', '{"files_changed": ["DatabaseConnection model", "DatabaseConnectionController", "database connections view", "DatabaseController db() method updated", "admin routes updated"], "tables_created": ["tbl_database"]}', NOW());
