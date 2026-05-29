-- =============================================
-- B2B SaaS Platform — Clean Schema
-- Database: mybusiness_db
-- Generated: 2026-05-29
-- Tables: 25 (in dependency order)
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------
-- 1. Reference: Banks
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_ref_bank` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100) NOT NULL,
    `swift_code`    VARCHAR(20) NULL,
    `status`        ENUM('active','inactive') DEFAULT 'active',
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 2. Reference: Industry
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_ref_industry` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(255) NOT NULL,
    `sort_order`    INT UNSIGNED DEFAULT 0,
    `status`        ENUM('active','inactive') DEFAULT 'active',
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 3. Reference: Industry Subcategory
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_ref_industry_subcategory` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `industry_id`   INT UNSIGNED NOT NULL,
    `name`          VARCHAR(255) NOT NULL,
    `sort_order`    INT UNSIGNED DEFAULT 0,
    `status`        ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (`industry_id`) REFERENCES `tbl_ref_industry`(`id`) ON DELETE CASCADE,
    INDEX `idx_industry` (`industry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 4. Company Agreement (T&C)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_company_agreement` (
    `id`            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `version`       VARCHAR(20) NOT NULL,
    `title`         VARCHAR(255) DEFAULT 'Terms & Conditions',
    `content`       LONGTEXT NOT NULL,
    `is_active`     BOOLEAN DEFAULT TRUE,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 5. Company (Tenant)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_company` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`            VARCHAR(20) NOT NULL UNIQUE,
    `company_name`    VARCHAR(150) NOT NULL,
    `name`            VARCHAR(100) NOT NULL,
    `email`           VARCHAR(150) NOT NULL,
    `mobile_no`       VARCHAR(20) NOT NULL,
    `password`        VARCHAR(255) NULL,
    `company_info`    JSON NULL,
    `logo_path`       VARCHAR(255) NULL,
    `timezone`        VARCHAR(50) DEFAULT 'Asia/Kuala_Lumpur',
    `setup_step`      TINYINT UNSIGNED DEFAULT 1,
    `agreement_id`    BIGINT UNSIGNED NULL,
    `agreement_accepted_at` DATETIME NULL,
    `email_verified_at`     DATETIME NULL,
    `mobile_verified_at`    DATETIME NULL,
    `status`          ENUM('pending','active','suspended','inactive') DEFAULT 'pending',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      TIMESTAMP NULL,
    FOREIGN KEY (`agreement_id`) REFERENCES `tbl_company_agreement`(`id`) ON DELETE SET NULL,
    INDEX `idx_code` (`code`),
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 6. Company Role (RBAC)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_company_role` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `name`            VARCHAR(50) NOT NULL,
    `slug`            VARCHAR(50) NOT NULL,
    `permissions`     JSON NULL,
    `is_owner`        BOOLEAN DEFAULT FALSE,
    `status`          ENUM('active','inactive') DEFAULT 'active',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_company_slug` (`company_id`, `slug`),
    INDEX `idx_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 7. Company Admin (Tenant Staff)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_company_admin` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `role_id`         BIGINT UNSIGNED NOT NULL,
    `name`            VARCHAR(100) NOT NULL,
    `email`           VARCHAR(150) NOT NULL,
    `mobile_no`       VARCHAR(20) NOT NULL,
    `password`        VARCHAR(255) NOT NULL,
    `is_owner`        BOOLEAN DEFAULT FALSE,
    `email_verified_at`     DATETIME NULL,
    `mobile_verified_at`    DATETIME NULL,
    `last_login_at`   DATETIME NULL,
    `status`          ENUM('active','inactive') DEFAULT 'active',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      TIMESTAMP NULL,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `tbl_company_role`(`id`),
    UNIQUE KEY `uk_company_email` (`company_id`, `email`),
    INDEX `idx_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 8. Company ↔ Industry Pivot
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_company_industry` (
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `subcategory_id`  INT UNSIGNED NOT NULL,
    PRIMARY KEY (`company_id`, `subcategory_id`),
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subcategory_id`) REFERENCES `tbl_ref_industry_subcategory`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 9. Partner (Agent/Reseller)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_partner` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `upline_id`       BIGINT UNSIGNED NULL,
    `partner_type`    ENUM('individual','company') NOT NULL,
    `name`            VARCHAR(150) NOT NULL,
    `email`           VARCHAR(150) NOT NULL,
    `mobile_no`       VARCHAR(20) NOT NULL,
    `password`        VARCHAR(255) NOT NULL,
    `referral_code`   VARCHAR(20) NULL UNIQUE,
    `ic_number`       VARCHAR(20) NULL,
    `company_name`    VARCHAR(150) NULL,
    `registration_no` VARCHAR(50) NULL,
    `tin`             VARCHAR(30) NULL,
    `sst_no`          VARCHAR(30) NULL,
    `email_verified_at`     DATETIME NULL,
    `mobile_verified_at`    DATETIME NULL,
    `document_verified_at`  DATETIME NULL,
    `country`         VARCHAR(50) DEFAULT 'Malaysia',
    `status`          ENUM('pending','active','suspended','blacklisted') DEFAULT 'pending',
    `last_login_at`   DATETIME NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      TIMESTAMP NULL,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`upline_id`) REFERENCES `tbl_partner`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `uk_company_email` (`company_id`, `email`),
    INDEX `idx_company` (`company_id`),
    INDEX `idx_referral` (`referral_code`),
    INDEX `idx_upline` (`upline_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 10. Partner Bank Account
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_partner_bank_account` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `partner_id`      BIGINT UNSIGNED NOT NULL,
    `bank_id`         INT UNSIGNED NOT NULL,
    `account_name`    VARCHAR(150) NOT NULL,
    `account_number`  VARCHAR(50) NOT NULL,
    `is_primary`      BOOLEAN DEFAULT FALSE,
    `status`          ENUM('pending','verified','rejected') DEFAULT 'pending',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`partner_id`) REFERENCES `tbl_partner`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bank_id`) REFERENCES `tbl_ref_bank`(`id`),
    INDEX `idx_company` (`company_id`),
    INDEX `idx_partner` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 11. Partner Document (KYC)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_partner_document` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `partner_id`      BIGINT UNSIGNED NOT NULL,
    `document_type`   VARCHAR(50) NOT NULL,
    `file_path`       VARCHAR(500) NOT NULL,
    `file_size`       BIGINT UNSIGNED NULL,
    `remarks`         TEXT NULL,
    `status`          ENUM('pending','approved','rejected') DEFAULT 'pending',
    `reviewed_by`     BIGINT UNSIGNED NULL,
    `reviewed_at`     DATETIME NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`partner_id`) REFERENCES `tbl_partner`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `tbl_company_admin`(`id`) ON DELETE SET NULL,
    INDEX `idx_partner` (`partner_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 12. Product Category (Hierarchical)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_product_category` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `parent_id`       BIGINT UNSIGNED NULL,
    `name`            VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(255) NOT NULL,
    `description`     TEXT NULL,
    `image_path`      VARCHAR(500) NULL,
    `sort_order`      INT DEFAULT 0,
    `status`          ENUM('active','inactive') DEFAULT 'active',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `tbl_product_category`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `uk_company_slug` (`company_id`, `slug`),
    INDEX `idx_company` (`company_id`),
    INDEX `idx_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 13. Product
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_product` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `type`            ENUM('simple','variable') NOT NULL DEFAULT 'simple',
    `name`            VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(255) NOT NULL,
    `sku`             VARCHAR(100) NULL,
    `description`     TEXT NULL,
    `short_description` TEXT NULL,
    `base_price`      DECIMAL(12,2) NOT NULL DEFAULT 0,
    `cost_price`      DECIMAL(12,2) NULL,
    `sale_price`      DECIMAL(12,2) NULL,
    `manage_stock`    BOOLEAN DEFAULT TRUE,
    `stock_quantity`  INT DEFAULT 0,
    `stock_status`    ENUM('in_stock','out_of_stock','on_backorder') DEFAULT 'in_stock',
    `weight`          DECIMAL(8,2) NULL,
    `length`          DECIMAL(8,2) NULL,
    `width`           DECIMAL(8,2) NULL,
    `height`          DECIMAL(8,2) NULL,
    `tax_status`      ENUM('taxable','exempt') DEFAULT 'taxable',
    `tax_class`       VARCHAR(50) NULL,
    `featured_image`  VARCHAR(500) NULL,
    `is_featured`     BOOLEAN DEFAULT FALSE,
    `status`          ENUM('draft','active','archived') DEFAULT 'draft',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      TIMESTAMP NULL,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_company_slug` (`company_id`, `slug`),
    INDEX `idx_company` (`company_id`),
    INDEX `idx_sku` (`sku`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 14. Product ↔ Category Pivot
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_product_category_pivot` (
    `product_id`      BIGINT UNSIGNED NOT NULL,
    `category_id`     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`product_id`, `category_id`),
    FOREIGN KEY (`product_id`) REFERENCES `tbl_product`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `tbl_product_category`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 15. Attribute
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_attribute` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `name`            VARCHAR(100) NOT NULL,
    `slug`            VARCHAR(100) NOT NULL,
    `type`            ENUM('select','button','color') DEFAULT 'select',
    `sort_order`      INT DEFAULT 0,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_company_slug` (`company_id`, `slug`),
    INDEX `idx_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 16. Attribute Term
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_attribute_term` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `attribute_id`    BIGINT UNSIGNED NOT NULL,
    `name`            VARCHAR(100) NOT NULL,
    `slug`            VARCHAR(100) NOT NULL,
    `color_code`      VARCHAR(7) NULL,
    `sort_order`      INT DEFAULT 0,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`attribute_id`) REFERENCES `tbl_attribute`(`id`) ON DELETE CASCADE,
    INDEX `idx_attribute` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 17. Product Variation
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_product_variation` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`      BIGINT UNSIGNED NOT NULL,
    `sku`             VARCHAR(100) NULL,
    `price`           DECIMAL(12,2) NOT NULL,
    `sale_price`      DECIMAL(12,2) NULL,
    `cost_price`      DECIMAL(12,2) NULL,
    `manage_stock`    BOOLEAN DEFAULT TRUE,
    `stock_quantity`  INT DEFAULT 0,
    `stock_status`    ENUM('in_stock','out_of_stock') DEFAULT 'in_stock',
    `image_path`      VARCHAR(500) NULL,
    `sort_order`      INT DEFAULT 0,
    `status`          ENUM('active','inactive') DEFAULT 'active',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      TIMESTAMP NULL,
    FOREIGN KEY (`product_id`) REFERENCES `tbl_product`(`id`) ON DELETE CASCADE,
    INDEX `idx_product` (`product_id`),
    INDEX `idx_sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 18. Variation ↔ Attribute Pivot
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_variation_attribute` (
    `variation_id`    BIGINT UNSIGNED NOT NULL,
    `attribute_id`    BIGINT UNSIGNED NOT NULL,
    `term_id`         BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`variation_id`, `attribute_id`),
    FOREIGN KEY (`variation_id`) REFERENCES `tbl_product_variation`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`attribute_id`) REFERENCES `tbl_attribute`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`term_id`) REFERENCES `tbl_attribute_term`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 19. Product Image
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_product_image` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`      BIGINT UNSIGNED NOT NULL,
    `path`            VARCHAR(500) NOT NULL,
    `alt_text`        VARCHAR(255) NULL,
    `sort_order`      INT DEFAULT 0,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `tbl_product`(`id`) ON DELETE CASCADE,
    INDEX `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 20. Stock Movement
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_stock_movement` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `product_id`      BIGINT UNSIGNED NULL,
    `variation_id`    BIGINT UNSIGNED NULL,
    `type`            ENUM('receipt','sale','adjustment','return','transfer') NOT NULL,
    `quantity`        INT NOT NULL,
    `reference_type`  VARCHAR(50) NULL,
    `reference_id`    BIGINT UNSIGNED NULL,
    `remarks`         TEXT NULL,
    `created_by`      BIGINT UNSIGNED NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `tbl_product`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`variation_id`) REFERENCES `tbl_product_variation`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `tbl_company_admin`(`id`) ON DELETE SET NULL,
    INDEX `idx_company` (`company_id`),
    INDEX `idx_product` (`product_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 21. Email SMTP Configuration
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_email_config` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NULL,
    `name`            VARCHAR(100) NOT NULL,
    `host`            VARCHAR(255) NOT NULL,
    `port`            SMALLINT UNSIGNED DEFAULT 587,
    `username`        VARCHAR(255) NOT NULL,
    `password`        VARCHAR(255) NOT NULL,
    `encryption`      ENUM('tls','ssl','none') DEFAULT 'tls',
    `from_name`       VARCHAR(100) NOT NULL,
    `from_email`      VARCHAR(150) NOT NULL,
    `reply_to`        VARCHAR(150) NULL,
    `status`          ENUM('active','inactive') DEFAULT 'active',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    INDEX `idx_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 22. Email Template
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_email_template` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NULL,
    `smtp_id`         INT UNSIGNED NULL,
    `slug`            VARCHAR(100) NOT NULL,
    `name`            VARCHAR(255) NOT NULL,
    `subject`         VARCHAR(255) NOT NULL,
    `content`         TEXT NOT NULL,
    `email_to`        VARCHAR(255) NULL,
    `email_cc`        VARCHAR(255) NULL,
    `email_bcc`       VARCHAR(255) NULL,
    `variables`       JSON NULL,
    `status`          ENUM('active','inactive') DEFAULT 'active',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`smtp_id`) REFERENCES `tbl_email_config`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `uk_company_slug` (`company_id`, `slug`),
    INDEX `idx_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 23. Verification Token (Polymorphic)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_verification_token` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tokenable_type`  VARCHAR(100) NOT NULL,
    `tokenable_id`    BIGINT UNSIGNED NOT NULL,
    `type`            ENUM('email','mobile','password_reset') NOT NULL,
    `code_hash`       VARCHAR(255) NOT NULL,
    `attempts`        TINYINT UNSIGNED DEFAULT 0,
    `ip_address`      VARCHAR(45) NULL,
    `expires_at`      DATETIME NOT NULL,
    `resend_available_at` DATETIME NULL,
    `verified_at`     DATETIME NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tokenable` (`tokenable_type`, `tokenable_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 24. Notification Log (Polymorphic)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_notification_log` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `recipient_type`  VARCHAR(100) NOT NULL,
    `recipient_id`    BIGINT UNSIGNED NOT NULL,
    `channel`         ENUM('email','sms','push') NOT NULL,
    `template_id`     INT UNSIGNED NULL,
    `subject`         VARCHAR(255) NOT NULL,
    `content`         TEXT NOT NULL,
    `email_to`        VARCHAR(255) NULL,
    `email_cc`        VARCHAR(255) NULL,
    `status`          ENUM('sent','failed','queued') DEFAULT 'queued',
    `error_message`   TEXT NULL,
    `sent_at`         TIMESTAMP NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`template_id`) REFERENCES `tbl_email_template`(`id`) ON DELETE SET NULL,
    INDEX `idx_company` (`company_id`),
    INDEX `idx_recipient` (`recipient_type`, `recipient_id`),
    INDEX `idx_channel` (`channel`),
    INDEX `idx_status` (`status`),
    INDEX `idx_sent` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- 25. Audit Log (Polymorphic)
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_audit_log` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `actor_type`      VARCHAR(100) NOT NULL,
    `actor_id`        BIGINT UNSIGNED NULL,
    `action`          VARCHAR(50) NOT NULL,
    `subject_type`    VARCHAR(100) NULL,
    `subject_id`      BIGINT UNSIGNED NULL,
    `description`     TEXT NULL,
    `metadata`        JSON NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `tbl_company`(`id`) ON DELETE CASCADE,
    INDEX `idx_company` (`company_id`),
    INDEX `idx_actor` (`actor_type`, `actor_id`),
    INDEX `idx_subject` (`subject_type`, `subject_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- Record in Laravel migrations table
-- =============================================
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_05_29_000001_create_tbl_ref_bank_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000002_create_tbl_ref_industry_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000003_create_tbl_ref_industry_subcategory_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000004_create_tbl_company_agreement_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000005_create_tbl_company_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000006_create_tbl_company_role_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000007_create_tbl_company_admin_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000008_create_tbl_company_industry_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000009_create_tbl_partner_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000010_create_tbl_partner_bank_account_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000011_create_tbl_partner_document_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000012_create_tbl_product_category_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000013_create_tbl_product_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000014_create_tbl_product_category_pivot_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000015_create_tbl_attribute_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000016_create_tbl_attribute_term_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000017_create_tbl_product_variation_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000018_create_tbl_variation_attribute_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000019_create_tbl_product_image_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000020_create_tbl_stock_movement_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000021_create_tbl_email_config_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000022_create_tbl_email_template_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000023_create_tbl_verification_token_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000024_create_tbl_notification_log_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m)),
('2026_05_29_000025_create_tbl_audit_log_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) AS m));
