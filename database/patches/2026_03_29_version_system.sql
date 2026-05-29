-- =============================================
-- P7: Version System with Rollback
-- Created: 2026-03-29
-- =============================================

-- ── Create tbl_versions ──────────────────────

CREATE TABLE IF NOT EXISTS `tbl_versions` (
    `id`                    bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `version_code`          varchar(14) NOT NULL,
    `version_label`         varchar(50) DEFAULT NULL,
    `type`                  varchar(20) NOT NULL DEFAULT 'patch',
    `file_name`             varchar(255) DEFAULT NULL,
    `file_hash`             varchar(32) DEFAULT NULL,
    `description`           text DEFAULT NULL,
    `rollback_target_code`  varchar(14) DEFAULT NULL,
    `rollback_from_code`    varchar(14) DEFAULT NULL,
    `rollback_chain`        text DEFAULT NULL,
    `code_files`            int(10) unsigned NOT NULL DEFAULT 0,
    `sql_files`             int(10) unsigned NOT NULL DEFAULT 0,
    `files_created`         int(10) unsigned NOT NULL DEFAULT 0,
    `files_overwritten`     int(10) unsigned NOT NULL DEFAULT 0,
    `files_restored`        int(10) unsigned NOT NULL DEFAULT 0,
    `files_deleted`         int(10) unsigned NOT NULL DEFAULT 0,
    `sql_ok`                int(10) unsigned NOT NULL DEFAULT 0,
    `sql_err`               int(10) unsigned NOT NULL DEFAULT 0,
    `total_backup_bytes`    bigint(20) unsigned NOT NULL DEFAULT 0,
    `status`                varchar(20) NOT NULL DEFAULT 'success',
    `admin_id`              bigint(20) unsigned DEFAULT NULL,
    `admin_name`            varchar(100) DEFAULT NULL,
    `applied_at`            timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `elapsed_ms`            int(10) unsigned NOT NULL DEFAULT 0,
    `log`                   longtext DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_version_code` (`version_code`),
    INDEX `idx_type` (`type`),
    INDEX `idx_applied_at` (`applied_at`),
    INDEX `idx_rollback_target` (`rollback_target_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Create tbl_version_code ──────────────────

CREATE TABLE IF NOT EXISTS `tbl_version_code` (
    `id`                bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `version_id`        bigint(20) unsigned NOT NULL,
    `file_path`         varchar(500) NOT NULL,
    `action`            varchar(20) NOT NULL DEFAULT 'overwrite',
    `content_before`    longblob DEFAULT NULL,
    `content_after`     longblob DEFAULT NULL,
    `size_before`       int(10) unsigned DEFAULT NULL,
    `size_after`        int(10) unsigned DEFAULT NULL,
    `hash_before`       varchar(32) DEFAULT NULL,
    `hash_after`        varchar(32) DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_version_id` (`version_id`),
    INDEX `idx_file_path` (`file_path`(191)),
    CONSTRAINT `fk_vc_version` FOREIGN KEY (`version_id`)
        REFERENCES `tbl_versions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Drop old patch tracking table ────────────

DROP TABLE IF EXISTS `tbl_patches_applied`;

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

DELETE FROM `cache` WHERE `key` = 'config_all';

-- ── Changelog ────────────────────────────────

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.0.0', 'Version System with Rollback',
    'Every patch now creates a versioned snapshot with full file backup. Supports one-click rollback to any previous version. New tables: tbl_versions + tbl_version_code. Replaces tbl_patches_applied.',
    '{"features":["version-system","rollback","file-viewer"]}',
    NOW()
);
