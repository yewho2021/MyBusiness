-- =============================================
-- SyncOffice Admin Portal - Database Schema
-- Auto-generated from production database
-- Generated: 2026-03-26
-- =============================================

SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET time_zone='+00:00';
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- Table: sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_activity_log
DROP TABLE IF EXISTS `tbl_activity_log`;
CREATE TABLE `tbl_activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_log_name` (`log_name`),
  KEY `idx_subject` (`subject_type`,`subject_id`),
  KEY `idx_causer` (`causer_type`,`causer_id`),
  KEY `idx_event` (`event`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_batch_uuid` (`batch_uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_admin
DROP TABLE IF EXISTS `tbl_admin`;
CREATE TABLE `tbl_admin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `twofa_secret` text DEFAULT NULL,
  `twofa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `role_id` bigint(20) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `timezone` varchar(50) DEFAULT NULL,
  `datetime_lastlogin` timestamp NULL DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tbl_admin_email_unique` (`email`),
  UNIQUE KEY `tbl_admin_username_unique` (`username`),
  KEY `tbl_admin_role_id_foreign` (`role_id`),
  KEY `idx_deleted_at` (`deleted_at`),
  CONSTRAINT `tbl_admin_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `tbl_admin_roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_admin_log
DROP TABLE IF EXISTS `tbl_admin_log`;
CREATE TABLE `tbl_admin_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(64) NOT NULL,
  `admin_id` bigint(20) unsigned DEFAULT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `admin_username` varchar(50) DEFAULT NULL,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `role_name` varchar(50) DEFAULT NULL,
  `status` enum('success','failed_password','failed_not_found','failed_inactive','expired','active') NOT NULL DEFAULT 'active',
  `ip_address` varchar(45) NOT NULL,
  `ip_country` varchar(100) DEFAULT NULL,
  `ip_city` varchar(100) DEFAULT NULL,
  `ip_isp` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','unknown') NOT NULL DEFAULT 'unknown',
  `login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_at` timestamp NULL DEFAULT NULL,
  `duration_seconds` int(10) unsigned DEFAULT NULL,
  `logout_type` enum('manual','expired','kicked','system') DEFAULT NULL,
  `fail_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_session_id` (`session_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_status` (`status`),
  KEY `idx_login_at` (`login_at`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_admin_menu_groups
DROP TABLE IF EXISTS `tbl_admin_menu_groups`;
CREATE TABLE `tbl_admin_menu_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tbl_admin_menu_groups_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_admin_menus
DROP TABLE IF EXISTS `tbl_admin_menus`;
CREATE TABLE `tbl_admin_menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `title` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `route_name` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `permission_key` varchar(100) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tbl_admin_menus_group_id_foreign` (`group_id`),
  KEY `tbl_admin_menus_parent_id_foreign` (`parent_id`),
  CONSTRAINT `tbl_admin_menus_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `tbl_admin_menu_groups` (`id`),
  CONSTRAINT `tbl_admin_menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `tbl_admin_menus` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_admin_role_menu_access
DROP TABLE IF EXISTS `tbl_admin_role_menu_access`;
CREATE TABLE `tbl_admin_role_menu_access` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `menu_id` bigint(20) unsigned NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_menu_unique` (`role_id`,`menu_id`),
  KEY `tbl_admin_role_menu_access_menu_id_foreign` (`menu_id`),
  CONSTRAINT `tbl_admin_role_menu_access_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `tbl_admin_menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_admin_role_menu_access_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `tbl_admin_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_admin_roles
DROP TABLE IF EXISTS `tbl_admin_roles`;
CREATE TABLE `tbl_admin_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 99,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tbl_admin_roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_backup_jobs
DROP TABLE IF EXISTS `tbl_backup_jobs`;
CREATE TABLE `tbl_backup_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `frequency` enum('daily','weekly','monthly','custom') NOT NULL DEFAULT 'daily',
  `cron_expression` varchar(50) DEFAULT NULL,
  `destination_path` varchar(255) DEFAULT NULL,
  `include_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`include_paths`)),
  `exclude_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exclude_paths`)),
  `exclude_extensions` text DEFAULT NULL,
  `include_database` tinyint(1) NOT NULL DEFAULT 1,
  `retention_count` int(11) NOT NULL DEFAULT 10,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_backup_logs
DROP TABLE IF EXISTS `tbl_backup_logs`;
CREATE TABLE `tbl_backup_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `run_id` bigint(20) unsigned NOT NULL,
  `level` enum('info','success','warning','error') NOT NULL DEFAULT 'info',
  `message` text NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_backup_logs_run_id` (`run_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17243 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_backup_runs
DROP TABLE IF EXISTS `tbl_backup_runs`;
CREATE TABLE `tbl_backup_runs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint(20) unsigned DEFAULT NULL,
  `folder_name` varchar(100) DEFAULT NULL,
  `destination_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','running','completed','failed','restoring','restored') NOT NULL DEFAULT 'pending',
  `total_files` int(11) NOT NULL DEFAULT 0,
  `processed_files` int(11) NOT NULL DEFAULT 0,
  `total_size` bigint(20) NOT NULL DEFAULT 0,
  `include_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`include_paths`)),
  `exclude_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exclude_paths`)),
  `exclude_extensions` text DEFAULT NULL,
  `include_database` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_backup_runs_job_id` (`job_id`),
  KEY `idx_backup_runs_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_changelog
DROP TABLE IF EXISTS `tbl_changelog`;
CREATE TABLE `tbl_changelog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `app_type` enum('office','apps') NOT NULL COMMENT 'office: Admin Portal, apps: Client Portal',
  `version` varchar(50) NOT NULL COMMENT 'e.g., 2024.02.15.1',
  `title` varchar(255) NOT NULL COMMENT 'Summary title of the change',
  `details` text NOT NULL COMMENT 'Expanded description/technical details',
  `technical_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Files modified, metadata, etc.' CHECK (json_valid(`technical_info`)),
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_app_type` (`app_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_configuration
DROP TABLE IF EXISTS `tbl_configuration`;
CREATE TABLE `tbl_configuration` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(50) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('text','textarea','color','number','boolean','select','image','code') NOT NULL DEFAULT 'text',
  `label` varchar(255) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `options` text DEFAULT NULL,
  `default_value` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_key` (`group`,`key`),
  KEY `idx_group` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_database
DROP TABLE IF EXISTS `tbl_database`;
CREATE TABLE `tbl_database` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Friendly label for this connection',
  `dbhost` varchar(255) NOT NULL DEFAULT 'localhost',
  `dbport` varchar(10) NOT NULL DEFAULT '3306',
  `dbname` varchar(255) NOT NULL,
  `dbusername` varchar(255) NOT NULL,
  `dbpassword` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_connected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_export_history
DROP TABLE IF EXISTS `tbl_export_history`;
CREATE TABLE `tbl_export_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source` varchar(100) NOT NULL,
  `format` enum('xlsx','csv','pdf') NOT NULL DEFAULT 'xlsx',
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `row_count` int(10) unsigned NOT NULL DEFAULT 0,
  `admin_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_source` (`source`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_media
DROP TABLE IF EXISTS `tbl_media`;
CREATE TABLE `tbl_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`generated_conversions`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_uuid` (`uuid`),
  KEY `idx_model` (`model_type`,`model_id`),
  KEY `idx_collection` (`collection_name`),
  KEY `idx_order` (`order_column`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_pdf_templates
DROP TABLE IF EXISTS `tbl_pdf_templates`;
CREATE TABLE `tbl_pdf_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `html_content` longtext NOT NULL,
  `paper_size` varchar(20) NOT NULL DEFAULT 'a4',
  `orientation` varchar(20) NOT NULL DEFAULT 'portrait',
  `margins` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`margins`)),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_query_bookmarks
DROP TABLE IF EXISTS `tbl_query_bookmarks`;
CREATE TABLE `tbl_query_bookmarks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `sql_query` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tbl_query_history
DROP TABLE IF EXISTS `tbl_query_history`;
CREATE TABLE `tbl_query_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sql_query` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
