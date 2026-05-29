-- =============================================
-- Deployment Module — Table + Menu
-- Created: 2026-03-30
-- =============================================

-- ── Create deployments table ─────────────────

CREATE TABLE IF NOT EXISTS `tbl_deployments` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,

    -- Target info
    `portal_name` varchar(100) NOT NULL,
    `portal_url` varchar(255) NOT NULL,
    `portal_timezone` varchar(50) DEFAULT 'UTC',
    `primary_color` varchar(7) DEFAULT '#dc2626',
    `admin_email` varchar(255) DEFAULT NULL,

    -- FTP credentials (encrypted)
    `ftp_host` varchar(255) NOT NULL,
    `ftp_port` int DEFAULT 21,
    `ftp_username` text NOT NULL,
    `ftp_password` text NOT NULL,
    `ftp_path` varchar(500) DEFAULT '/public_html',
    `ftp_ssl` tinyint(1) DEFAULT 0,

    -- Pre-deploy wipe options
    `wipe_ftp` tinyint(1) DEFAULT 0,
    `wipe_database` tinyint(1) DEFAULT 0,

    -- MySQL credentials (encrypted)
    `mysql_host` varchar(255) NOT NULL,
    `mysql_port` int DEFAULT 3306,
    `mysql_database` varchar(100) NOT NULL,
    `mysql_username` text NOT NULL,
    `mysql_password` text NOT NULL,

    -- Deployment status
    `status` enum('pending','wiping','packaging','uploading','database','seeding','configuring','verifying','completed','failed') DEFAULT 'pending',
    `progress_percent` int DEFAULT 0,
    `files_total` int DEFAULT 0,
    `files_uploaded` int DEFAULT 0,
    `files_wiped` int DEFAULT 0,
    `tables_dropped` int DEFAULT 0,
    `tables_created` int DEFAULT 0,
    `error_message` text DEFAULT NULL,
    `log` json DEFAULT NULL,

    -- Tracking
    `deployed_by` int unsigned DEFAULT NULL,
    `started_at` timestamp NULL DEFAULT NULL,
    `completed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_portal_url` (`portal_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Add menu entry ───────────────────────────

INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT
    g.id, NULL, 0, 'Deployment', 'fas fa-cloud-upload-alt', 'admin.deployment.index', 'deployment', 98, 1, NOW(), NOW()
FROM `tbl_admin_menu_groups` g
WHERE g.slug = 'system'
LIMIT 1;

-- ── Grant access to Administrator role ───────

INSERT INTO `tbl_admin_role_menu_access`
    (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT
    r.id,
    m.id,
    1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_roles` r
CROSS JOIN `tbl_admin_menus` m
WHERE r.slug = 'administrator'
  AND m.route_name = 'admin.deployment.index';

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

DELETE FROM `cache` WHERE `key` LIKE 'config_%';
