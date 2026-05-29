-- =============================================
-- Admin Login Activity Log
-- Created: 2026-03-22
-- =============================================

CREATE TABLE IF NOT EXISTS `tbl_admin_log` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `session_id`        VARCHAR(64)         NOT NULL,
    `admin_id`          BIGINT UNSIGNED     NULL     DEFAULT NULL,
    `admin_name`        VARCHAR(100)        NULL     DEFAULT NULL,
    `admin_username`    VARCHAR(50)         NULL     DEFAULT NULL,
    `role_id`           BIGINT UNSIGNED     NULL     DEFAULT NULL,
    `role_name`         VARCHAR(50)         NULL     DEFAULT NULL,
    `status`            ENUM('success','failed_password','failed_not_found','failed_inactive','expired','active')
                                            NOT NULL DEFAULT 'active',
    `ip_address`        VARCHAR(45)         NOT NULL,
    `ip_country`        VARCHAR(100)        NULL     DEFAULT NULL,
    `ip_city`           VARCHAR(100)        NULL     DEFAULT NULL,
    `ip_isp`            VARCHAR(255)        NULL     DEFAULT NULL,
    `user_agent`        TEXT                NULL     DEFAULT NULL,
    `browser`           VARCHAR(100)        NULL     DEFAULT NULL,
    `platform`          VARCHAR(100)        NULL     DEFAULT NULL,
    `device_type`       ENUM('desktop','mobile','tablet','unknown')
                                            NOT NULL DEFAULT 'unknown',
    `login_at`          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `logout_at`         TIMESTAMP           NULL     DEFAULT NULL,
    `duration_seconds`  INT UNSIGNED        NULL     DEFAULT NULL,
    `logout_type`       ENUM('manual','expired','kicked','system')
                                            NULL     DEFAULT NULL,
    `fail_reason`       VARCHAR(255)        NULL     DEFAULT NULL,
    `created_at`        TIMESTAMP           NULL     DEFAULT NULL,
    `updated_at`        TIMESTAMP           NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_session_id` (`session_id`),
    KEY `idx_admin_id` (`admin_id`),
    KEY `idx_status` (`status`),
    KEY `idx_login_at` (`login_at`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Menu entry for Login Log
-- Adjust group_id and sort_order to fit your existing menu structure
-- =============================================
INSERT INTO `tbl_admin_menus` (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES (1, NULL, 1, 'Login Log', 'fas fa-user-shield', 'admin.admin-log.index', 'admin_log', 50, 1, NOW(), NOW());

-- =============================================
-- Changelog entry
-- =============================================
INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '1.8.0',
    'Admin Login Activity Log',
    'Complete login session tracking module:\n- Records every login attempt (success + all failure types)\n- Full IP geolocation (country, city, ISP) via ip-api.com\n- User-agent parsing (browser, platform, device type)\n- Session lifecycle tracking (login → logout/expiry)\n- Session duration calculation\n- Rich filterable interface (date range, user, role, status, IP, device)\n- Active session monitoring with force-kick capability\n- CSV export with all filter combinations\n- Log purge for data retention management\n- Snapshot of admin name + role at login time for historical accuracy',
    '{"table": "tbl_admin_log", "new_files": ["app/Models/AdminLog.php", "app/Http/Controllers/Admin/AdminLogController.php", "resources/views/admin/pages/admin-log/index.blade.php"], "modified_files": ["app/Http/Controllers/Admin/Auth/LoginController.php", "app/Http/Middleware/AdminAuthenticate.php", "routes/admin.php"], "cookies_added": ["admin_session_id"], "api_used": "ip-api.com (free, 45 req/min)"}',
    NOW()
);
