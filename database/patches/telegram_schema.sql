-- ═══════════════════════════════════════════════════
-- Telegram Module Schema
-- ═══════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `tbl_telegram_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_telegram_targets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `chat_id` varchar(100) NOT NULL,
  `type` enum('personal','group','channel') NOT NULL DEFAULT 'group',
  `notes` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_telegram_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `report_type` varchar(20) NOT NULL DEFAULT 'code',
  `description` varchar(500) DEFAULT NULL,
  `category` varchar(30) NOT NULL DEFAULT 'general',
  `icon` varchar(10) DEFAULT '📊',
  `default_params` longtext DEFAULT NULL,
  `param_schema` longtext DEFAULT NULL,
  `query` text DEFAULT NULL,
  `template` text DEFAULT NULL,
  `computed_fields` longtext DEFAULT NULL,
  `php_code` mediumtext DEFAULT NULL,
  `target_id` int(10) unsigned DEFAULT NULL,
  `schedule_type` varchar(20) NOT NULL DEFAULT 'manual',
  `schedule_time` varchar(5) DEFAULT NULL,
  `schedule_day` tinyint(4) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'Asia/Kuala_Lumpur',
  `params` longtext DEFAULT NULL,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `last_status` varchar(20) DEFAULT NULL,
  `last_error` varchar(500) DEFAULT NULL,
  `send_count` int(10) unsigned DEFAULT 0,
  `fail_count` int(10) unsigned DEFAULT 0,
  `consecutive_fails` tinyint(3) unsigned DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `enabled` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_telegram_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'manual',
  `report_slug` varchar(50) DEFAULT NULL,
  `target` varchar(50) NOT NULL DEFAULT 'default',
  `chat_id` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'sent',
  `error` text DEFAULT NULL,
  `duration_ms` int(11) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_report_slug` (`report_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_telegram_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `report_id` int(10) unsigned NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `schedule_type` varchar(20) NOT NULL DEFAULT 'manual',
  `schedule_time` varchar(5) DEFAULT NULL,
  `schedule_day` tinyint(4) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'Asia/Kuala_Lumpur',
  `params` longtext DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `last_status` varchar(20) DEFAULT NULL,
  `last_error` varchar(500) DEFAULT NULL,
  `send_count` int(10) unsigned DEFAULT 0,
  `fail_count` int(10) unsigned DEFAULT 0,
  `consecutive_fails` tinyint(3) unsigned DEFAULT 0,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  KEY `idx_enabled_schedule` (`enabled`,`schedule_type`),
  KEY `idx_target` (`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
