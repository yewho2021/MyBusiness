# B2B SaaS Platform — Clean Database Schema Design

> **Project:** apps.mybusiness.com.my
> **Database:** `mybusiness_b2b`
> **Date:** 2026-05-29
> **Status:** Draft — Reviewed & improved 2026-05-29

---

## 1. System Overview

A **multi-tenant B2B SaaS platform** where companies register, onboard partners/agents (individuals or businesses), and manage a product catalog with variations. Each company is a self-contained tenant with their own admins, partners, products, and data.

### Core Business Flow

```
Company signs up → Setup Wizard (3 steps) → Company Admin manages:
├── Partners/Agents (individual or company-type)
│   ├── KYC verification (IC, docs, bank accounts)
│   ├── Referral codes & upline tracking
│   └── Status lifecycle: pending → active → suspended → blacklisted
├── Product Catalog
│   ├── Simple products (fixed price)
│   ├── Variable products (Color × Size × Material = variations)
│   ├── Categories (hierarchical, company-scoped)
│   └── Stock management & movements
└── System Settings (SMTP, agreements)
```

---

## 2. What's Changing

### Tables REMOVED (not needed)

| Old Table | Reason |
|-----------|--------|
| `tbl_marketing_data` | Flat denormalized CRM dump — not SaaS architecture |
| `tbl_marketing_campaign` | SQL-in-columns design, 0 rows, not maintainable |
| `tbl_marketing_campaign_data` | Depends on campaign, removed |
| `tbl_marketing_campaign_partner` | Depends on campaign, removed |
| `tbl_partner` | Legacy duplicate — `tbl_partners` is the replacement |
| `tbl_partner_bankdetails` | Empty, replaced by `tbl_partner_bank_accounts` |
| `tbl_partner_access_log` | Legacy format — merged into `tbl_partner_activity_log` |
| `tbl_company_admin_access_log` | Legacy format — use unified `tbl_company_audit_log` |
| `tbl_company_admin_email_log` | Merged into `tbl_company_notification_log` |
| `tbl_partner_email_log` | Merged into `tbl_company_notification_log` |
| `tbl_partner_notes` | Merged into `tbl_company_audit_log` (type: note) |

### Tables MERGED (from 3 email logs → 1 notification log)

| Old Tables | New Table |
|-----------|-----------|
| `tbl_company_admin_email_log` + `tbl_partner_email_log` | `tbl_company_notification_log` |
| `tbl_company_admin_access_log` + `tbl_partner_access_log` + `tbl_partner_notes` | `tbl_company_audit_log` |

### Tables from office.mybusiness NOT duplicated

These already exist in `mybusiness_db` and should NOT be recreated in B2B:
- `tbl_admin` / `tbl_admin_roles` / `tbl_admin_menus` (office admin panel)
- `tbl_backup_*` (backup system)
- `tbl_changelog` (versioning)
- `tbl_configuration` (office settings)
- `tbl_query_*` (DB tools)

---

## 3. Clean Schema

### 3.1 Company (Tenant)

```sql
CREATE TABLE tbl_company (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(20) NOT NULL UNIQUE,          -- e.g. MYB-20260221-00001
    company_name    VARCHAR(150) NOT NULL,
    name            VARCHAR(100) NOT NULL,                -- contact person name
    email           VARCHAR(150) NOT NULL,
    mobile_no       VARCHAR(20) NOT NULL,
    password        VARCHAR(255) NULL,
    company_info    JSON NULL,                            -- flexible: address, SSM no, etc.
    logo_path       VARCHAR(255) NULL,
    timezone        VARCHAR(50) DEFAULT 'Asia/Kuala_Lumpur',
    setup_step      TINYINT UNSIGNED DEFAULT 1,           -- 1-3 wizard progress
    agreement_id    BIGINT UNSIGNED NULL,                 -- FK to tbl_company_agreement
    agreement_accepted_at DATETIME NULL,
    email_verified_at     DATETIME NULL,
    mobile_verified_at    DATETIME NULL,
    status          ENUM('pending','active','suspended','inactive') DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,                       -- soft delete

    FOREIGN KEY (agreement_id) REFERENCES tbl_company_agreement(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes from old:**
- `mediumtext` → `varchar` with proper lengths
- `enum('Yes','No')` verification → nullable `datetime` (standard Laravel pattern)
- Removed redundant `username` (same as `company_name`)
- Removed `email_verification_code` / `mobileno_verification_code` (moved to `tbl_company_verification_token`)
- Added `logo_path`, `timezone`, `deleted_at`
- `company_info` as JSON for flexible metadata

---

### 3.2 Company Admin (Tenant Staff)

> **Migration order:** Create `tbl_company_role` (3.3) BEFORE this table — `role_id` FK depends on it.

```sql
CREATE TABLE tbl_company_admin (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    role_id         BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    mobile_no       VARCHAR(20) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    is_owner        BOOLEAN DEFAULT FALSE,
    email_verified_at     DATETIME NULL,
    mobile_verified_at    DATETIME NULL,
    last_login_at   DATETIME NULL,
    status          ENUM('active','inactive') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,                       -- soft delete (preserve audit trail)

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES tbl_company_role(id),
    UNIQUE KEY uk_company_email (company_id, email),
    INDEX idx_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Consistent naming (`companyid` → `company_id`, `roleid` → `role_id`), removed `datetime_lastclick`, proper FK constraints. Added `deleted_at` for soft delete (preserves audit trail when admin is removed).

---

### 3.3 Company Role

```sql
CREATE TABLE tbl_company_role (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(50) NOT NULL,
    slug            VARCHAR(50) NOT NULL,
    permissions     JSON NULL,                            -- future: granular permissions
    is_owner        BOOLEAN DEFAULT FALSE,
    status          ENUM('active','inactive') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    UNIQUE KEY uk_company_slug (company_id, slug),
    INDEX idx_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Renamed from `tbl_company_admin_role`, added `slug`, `permissions` JSON for future RBAC, removed `mediumtext`.

---

### 3.4 Company Agreement

```sql
CREATE TABLE tbl_company_agreement (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    version         VARCHAR(20) NOT NULL,
    title           VARCHAR(255) DEFAULT 'Terms & Conditions',
    content         LONGTEXT NOT NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `title`, `is_active` for version management. No changes needed — clean table.

---

### 3.5 Partner (Agent/Reseller)

```sql
CREATE TABLE tbl_company_partner (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    upline_id       BIGINT UNSIGNED NULL,                 -- self-referencing for MLM/referral tree
    partner_type    ENUM('individual','company') NOT NULL,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    mobile_no       VARCHAR(20) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    referral_code   VARCHAR(20) NULL UNIQUE,              -- globally unique (cross-tenant referral links)

    -- Individual fields
    ic_number       VARCHAR(20) NULL,                     -- MyKad / passport

    -- Company fields
    company_name    VARCHAR(150) NULL,
    registration_no VARCHAR(50) NULL,                     -- SSM
    tin             VARCHAR(30) NULL,                     -- tax identification
    sst_no          VARCHAR(30) NULL,                     -- SST registration

    -- Verification
    email_verified_at     DATETIME NULL,
    mobile_verified_at    DATETIME NULL,
    document_verified_at  DATETIME NULL,                  -- KYC complete
    country         VARCHAR(50) DEFAULT 'Malaysia',

    -- Status & tracking
    status          ENUM('pending','active','suspended','blacklisted') DEFAULT 'pending',
    last_login_at   DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (upline_id) REFERENCES tbl_company_partner(id) ON DELETE SET NULL,
    UNIQUE KEY uk_company_email (company_id, email),
    INDEX idx_company (company_id),
    INDEX idx_referral (referral_code),
    INDEX idx_upline (upline_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Consolidated from `tbl_partners` (new) + `tbl_partner` (legacy) into one clean table. Consistent naming. Proper indexes.

---

### 3.6 Partner Bank Account

```sql
CREATE TABLE tbl_partner_bank_account (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    partner_id      BIGINT UNSIGNED NOT NULL,
    bank_id         INT UNSIGNED NOT NULL,
    account_name    VARCHAR(150) NOT NULL,
    account_number  VARCHAR(50) NOT NULL,
    is_primary      BOOLEAN DEFAULT FALSE,
    status          ENUM('pending','verified','rejected') DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES tbl_company_partner(id) ON DELETE CASCADE,
    FOREIGN KEY (bank_id) REFERENCES tbl_ref_bank(id),
    INDEX idx_company (company_id),
    INDEX idx_partner (partner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `is_primary`, `rejected` status, `updated_at`, `idx_company` index. Renamed singular.

---

### 3.7 Partner Document (KYC)

```sql
CREATE TABLE tbl_partner_document (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    partner_id      BIGINT UNSIGNED NOT NULL,
    document_type   VARCHAR(50) NOT NULL,                 -- ic_front, ic_back, ssm_cert, bank_statement
    file_path       VARCHAR(500) NOT NULL,
    file_size       BIGINT UNSIGNED NULL,
    remarks         TEXT NULL,                            -- admin notes on rejection
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    reviewed_by     BIGINT UNSIGNED NULL,
    reviewed_at     DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES tbl_company_partner(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES tbl_company_admin(id) ON DELETE SET NULL,
    INDEX idx_partner (partner_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `file_size`, `remarks`, `reviewed_by` (FK to company_admin), `reviewed_at` for proper KYC workflow.

---

### 3.8 Product

```sql
CREATE TABLE tbl_company_product (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    type            ENUM('simple','variable') NOT NULL DEFAULT 'simple',
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL,
    sku             VARCHAR(100) NULL,
    description     TEXT NULL,
    short_description TEXT NULL,

    -- Pricing
    base_price      DECIMAL(12,2) NOT NULL DEFAULT 0,
    cost_price      DECIMAL(12,2) NULL,
    sale_price      DECIMAL(12,2) NULL,

    -- Stock (simple products only — for variable products, stock lives on tbl_company_product_variation)
    manage_stock    BOOLEAN DEFAULT TRUE,
    stock_quantity  INT DEFAULT 0,
    stock_status    ENUM('in_stock','out_of_stock','on_backorder') DEFAULT 'in_stock',

    -- Physical
    weight          DECIMAL(8,2) NULL,
    length          DECIMAL(8,2) NULL,
    width           DECIMAL(8,2) NULL,
    height          DECIMAL(8,2) NULL,

    -- Tax
    tax_status      ENUM('taxable','exempt') DEFAULT 'taxable',
    tax_class       VARCHAR(50) NULL,

    -- Display
    featured_image  VARCHAR(500) NULL,
    is_featured     BOOLEAN DEFAULT FALSE,

    status          ENUM('draft','active','archived') DEFAULT 'draft',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    UNIQUE KEY uk_company_slug (company_id, slug),
    INDEX idx_company (company_id),
    INDEX idx_sku (sku),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:**
- Removed `grouped`, `external` types (unused, unnecessary complexity)
- Removed `is_virtual`, `is_downloadable`, `external_url`, `button_text`, `purchase_note` (0 usage)
- Added `is_featured`
- Renamed `companyid` → `company_id`
- Singular table name

---

### 3.9 Product Category

```sql
CREATE TABLE tbl_company_product_category (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    parent_id       BIGINT UNSIGNED NULL,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL,
    description     TEXT NULL,
    image_path      VARCHAR(500) NULL,
    sort_order      INT DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES tbl_company_product_category(id) ON DELETE SET NULL,
    UNIQUE KEY uk_company_slug (company_id, slug),
    INDEX idx_company (company_id),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `sort_order`. Singular name.

---

### 3.10 Product ↔ Category Pivot

```sql
CREATE TABLE tbl_company_product_category_pivot (
    product_id      BIGINT UNSIGNED NOT NULL,
    category_id     BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (product_id, category_id),
    FOREIGN KEY (product_id) REFERENCES tbl_company_product(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES tbl_company_product_category(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

No changes needed — already clean.

---

### 3.11 Product Variation

```sql
CREATE TABLE tbl_company_product_variation (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT UNSIGNED NOT NULL,
    sku             VARCHAR(100) NULL,
    price           DECIMAL(12,2) NOT NULL,
    sale_price      DECIMAL(12,2) NULL,
    cost_price      DECIMAL(12,2) NULL,                   -- NEW: cost tracking per variation
    manage_stock    BOOLEAN DEFAULT TRUE,
    stock_quantity  INT DEFAULT 0,
    stock_status    ENUM('in_stock','out_of_stock') DEFAULT 'in_stock',
    image_path      VARCHAR(500) NULL,
    sort_order      INT DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,                       -- soft delete (preserve order history)

    FOREIGN KEY (product_id) REFERENCES tbl_company_product(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `cost_price` per variation, `sort_order`, `deleted_at` for soft delete. Singular name.

---

### 3.12 Product Image

```sql
CREATE TABLE tbl_company_product_image (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT UNSIGNED NOT NULL,
    path            VARCHAR(500) NOT NULL,
    alt_text        VARCHAR(255) NULL,
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES tbl_company_product(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `alt_text`. Singular name.

---

### 3.13 Attribute

```sql
CREATE TABLE tbl_company_attribute (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(100) NOT NULL,
    type            ENUM('select','button','color') DEFAULT 'select',
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    UNIQUE KEY uk_company_slug (company_id, slug),
    INDEX idx_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `color` type for color swatches, `sort_order`. Singular name.

---

### 3.14 Attribute Term

```sql
CREATE TABLE tbl_company_attribute_term (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attribute_id    BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(100) NOT NULL,
    color_code      VARCHAR(7) NULL,                      -- hex color for color-type attributes
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (attribute_id) REFERENCES tbl_company_attribute(id) ON DELETE CASCADE,
    INDEX idx_attribute (attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `color_code` for color swatches, `sort_order`. Singular name.

---

### 3.15 Variation ↔ Attribute Pivot

```sql
CREATE TABLE tbl_company_variation_attribute (
    variation_id    BIGINT UNSIGNED NOT NULL,
    attribute_id    BIGINT UNSIGNED NOT NULL,
    term_id         BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (variation_id, attribute_id),
    FOREIGN KEY (variation_id) REFERENCES tbl_company_product_variation(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES tbl_company_attribute(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES tbl_company_attribute_term(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

No changes — already clean.

---

### 3.16 Stock Movement

```sql
CREATE TABLE tbl_company_stock_movement (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    product_id      BIGINT UNSIGNED NULL,
    variation_id    BIGINT UNSIGNED NULL,
    type            ENUM('receipt','sale','adjustment','return','transfer') NOT NULL,
    quantity        INT NOT NULL,                         -- positive = in, negative = out
    reference_type  VARCHAR(50) NULL,                     -- order, manual, import
    reference_id    BIGINT UNSIGNED NULL,                 -- link to order/PO id
    remarks         TEXT NULL,
    created_by      BIGINT UNSIGNED NULL,                 -- FK to company_admin
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES tbl_company_product(id) ON DELETE SET NULL,
    FOREIGN KEY (variation_id) REFERENCES tbl_company_product_variation(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES tbl_company_admin(id) ON DELETE SET NULL,
    INDEX idx_company (company_id),
    INDEX idx_product (product_id),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Added `transfer` type, polymorphic `reference_type` + `reference_id` for linking to orders, `updated_at` removed (stock movements are immutable). Renamed `reference_no` → polymorphic ref.

---

### 3.17 Verification Token (unified)

```sql
CREATE TABLE tbl_company_verification_token (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type  VARCHAR(100) NOT NULL,                -- App\Models\Company, App\Models\Partner
    tokenable_id    BIGINT UNSIGNED NOT NULL,
    type            ENUM('email','mobile','password_reset') NOT NULL,
    code_hash       VARCHAR(255) NOT NULL,                -- hashed OTP
    attempts        TINYINT UNSIGNED DEFAULT 0,
    ip_address      VARCHAR(45) NULL,
    expires_at      DATETIME NOT NULL,
    resend_available_at DATETIME NULL,
    verified_at     DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tokenable (tokenable_type, tokenable_id),
    INDEX idx_type (type),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Merged `tbl_company_tarc` + `tbl_partner_verification_tokens` into one polymorphic table. Added `password_reset` type, `idx_type` index.

---

### 3.18 Notification Log (unified)

```sql
CREATE TABLE tbl_company_notification_log (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    recipient_type  VARCHAR(100) NOT NULL,                -- CompanyAdmin, Partner
    recipient_id    BIGINT UNSIGNED NOT NULL,
    channel         ENUM('email','sms','push') NOT NULL,
    template_id     INT UNSIGNED NULL,
    subject         VARCHAR(255) NOT NULL,
    content         TEXT NOT NULL,
    email_to        VARCHAR(255) NULL,
    email_cc        VARCHAR(255) NULL,
    status          ENUM('sent','failed','queued') DEFAULT 'queued',
    error_message   TEXT NULL,
    sent_at         TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES tbl_company_email_template(id) ON DELETE SET NULL,
    INDEX idx_company (company_id),
    INDEX idx_recipient (recipient_type, recipient_id),
    INDEX idx_channel (channel),
    INDEX idx_status (status),
    INDEX idx_sent (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Merged 3 email logs into 1 polymorphic notification log. Added `channel` for future SMS/push, `status` for queue tracking. FK on `template_id`, index on `channel`.

---

### 3.19 Audit Log (unified)

```sql
CREATE TABLE tbl_company_audit_log (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    actor_type      VARCHAR(100) NOT NULL,                -- CompanyAdmin, Partner, System
    actor_id        BIGINT UNSIGNED NULL,
    action          VARCHAR(50) NOT NULL,                 -- login, logout, create, update, delete, note
    subject_type    VARCHAR(100) NULL,                    -- Partner, Product, etc.
    subject_id      BIGINT UNSIGNED NULL,
    description     TEXT NULL,
    metadata        JSON NULL,                            -- ip, user_agent, browser, platform, geo, etc.
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    INDEX idx_actor (actor_type, actor_id),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Replaces `tbl_company_admin_access_log`, `tbl_partner_access_log`, `tbl_partner_activity_log`, `tbl_partner_notes`. All login/logout/CRUD/notes tracked in one place. Geo/browser data stored in `metadata` JSON instead of 10+ separate columns.

---

### 3.20 Reference: Banks

```sql
CREATE TABLE tbl_ref_bank (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    swift_code      VARCHAR(20) NULL,
    status          ENUM('active','inactive') DEFAULT 'active',

    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Renamed from `tbl_system_bank`. `description` → `name`. Added `swift_code`. Consistent enum values.

---

### 3.21 Reference: Industry & Subcategory

```sql
CREATE TABLE tbl_ref_industry (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    sort_order      INT UNSIGNED DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',

    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_ref_industry_subcategory (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    industry_id     INT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    sort_order      INT UNSIGNED DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',

    FOREIGN KEY (industry_id) REFERENCES tbl_ref_industry(id) ON DELETE CASCADE,
    INDEX idx_industry (industry_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Renamed to `tbl_ref_*` prefix for reference data. Consistent naming (`industryid` → `industry_id`). Consistent enum casing.

---

### 3.22 Company ↔ Industry Pivot

```sql
CREATE TABLE tbl_company_industry (
    company_id      BIGINT UNSIGNED NOT NULL,
    subcategory_id  INT UNSIGNED NOT NULL,

    PRIMARY KEY (company_id, subcategory_id),
    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (subcategory_id) REFERENCES tbl_ref_industry_subcategory(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Removed auto-increment `id`, proper composite PK. Consistent naming.

---

### 3.23 Email SMTP Configuration

```sql
CREATE TABLE tbl_company_email_config (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NULL,                 -- NULL = system-wide default
    name            VARCHAR(100) NOT NULL,                -- e.g. "Main SMTP"
    host            VARCHAR(255) NOT NULL,
    port            SMALLINT UNSIGNED DEFAULT 587,
    username        VARCHAR(255) NOT NULL,
    password        VARCHAR(255) NOT NULL,                -- encrypted at rest
    encryption      ENUM('tls','ssl','none') DEFAULT 'tls',
    from_name       VARCHAR(100) NOT NULL,
    from_email      VARCHAR(150) NOT NULL,
    reply_to        VARCHAR(150) NULL,
    status          ENUM('active','inactive') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    INDEX idx_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Changes:** Renamed from `tbl_system_email_smtp`. `mediumtext` → `varchar`. `enum('Yes','No')` SSL → proper `encryption` enum. Added `status`. Per-company support via nullable `company_id`.

---

### 3.24 Email Template

```sql
CREATE TABLE tbl_company_email_template (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NULL,                 -- NULL = system default, set = company override
    smtp_id         INT UNSIGNED NULL,
    slug            VARCHAR(100) NOT NULL,                -- e.g. company.registration.verify_email
    name            VARCHAR(255) NOT NULL,
    subject         VARCHAR(255) NOT NULL,
    content         TEXT NOT NULL,                        -- Blade/HTML template with variables
    email_to        VARCHAR(255) NULL,                    -- override recipient
    email_cc        VARCHAR(255) NULL,
    email_bcc       VARCHAR(255) NULL,
    variables       JSON NULL,                            -- available placeholder docs
    status          ENUM('active','inactive') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id) REFERENCES tbl_company(id) ON DELETE CASCADE,
    FOREIGN KEY (smtp_id) REFERENCES tbl_company_email_config(id) ON DELETE SET NULL,
    UNIQUE KEY uk_company_slug (company_id, slug),        -- same slug allowed per company (NULL company = system default)
    INDEX idx_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Design note:** `company_id = NULL` means system default template. When a company wants to customize, create a row with their `company_id` + same slug. App logic: query company-specific first, fall back to NULL.

**Changes:** Added `company_id` for per-tenant customization, `slug` for code references (unique per company, not globally), `variables` JSON for documenting available placeholders, `status`. `mediumtext` → proper types.

---

## 4. Table Summary

### Clean Schema: 25 tables (down from 37)

| # | Table | Purpose | Scope |
|---|-------|---------|-------|
| 1 | `tbl_company` | Tenant accounts | Global |
| 2 | `tbl_company_admin` | Tenant staff | Per-company |
| 3 | `tbl_company_role` | Tenant roles | Per-company |
| 4 | `tbl_company_agreement` | T&C versions | Global |
| 5 | `tbl_company_industry` | Company ↔ industry | Pivot |
| 6 | `tbl_company_partner` | Partners/agents | Per-company |
| 7 | `tbl_partner_bank_account` | Payout bank details | Per-partner |
| 8 | `tbl_partner_document` | KYC documents | Per-partner |
| 9 | `tbl_company_product` | Product catalog | Per-company |
| 10 | `tbl_company_product_category` | Hierarchical categories | Per-company |
| 11 | `tbl_company_product_category_pivot` | Product ↔ category | Pivot |
| 12 | `tbl_company_product_variation` | Product variants | Per-product |
| 13 | `tbl_company_product_image` | Product gallery | Per-product |
| 14 | `tbl_company_attribute` | Attribute definitions | Per-company |
| 15 | `tbl_company_attribute_term` | Attribute values | Per-attribute |
| 16 | `tbl_company_variation_attribute` | Variation ↔ attribute terms | Pivot |
| 17 | `tbl_company_stock_movement` | Inventory tracking | Per-company |
| 18 | `tbl_company_verification_token` | OTP/verification codes | Polymorphic |
| 19 | `tbl_company_notification_log` | Email/SMS delivery log | Per-company |
| 20 | `tbl_company_audit_log` | All activity/login/notes | Per-company |
| 21 | `tbl_ref_bank` | Malaysian banks | Reference |
| 22 | `tbl_ref_industry` | Industry list | Reference |
| 23 | `tbl_ref_industry_subcategory` | Industry subcategories | Reference |
| 24 | `tbl_company_email_config` | SMTP settings | Per-company/global |
| 25 | `tbl_company_email_template` | Email templates | Per-company/global |

+ Standard Laravel tables: `users`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `migrations`

---

## 5. Relationship Diagram

```
tbl_company (tenant)
├── hasMany → tbl_company_admin
│   └── belongsTo → tbl_company_role
├── hasMany → tbl_company_role
├── belongsTo → tbl_company_agreement
├── belongsToMany → tbl_ref_industry_subcategory (via tbl_company_industry)
├── hasMany → tbl_company_partner
│   ├── hasMany → tbl_partner_bank_account
│   │   └── belongsTo → tbl_ref_bank
│   ├── hasMany → tbl_partner_document
│   └── morphMany → tbl_company_verification_token
├── morphMany → tbl_company_verification_token                      ← (company-level verification too)
├── hasMany → tbl_company_product
│   ├── belongsToMany → tbl_company_product_category (via pivot)
│   ├── hasMany → tbl_company_product_variation
│   │   └── belongsToMany → tbl_company_attribute_term (via tbl_company_variation_attribute)
│   └── hasMany → tbl_company_product_image
├── hasMany → tbl_company_attribute
│   └── hasMany → tbl_company_attribute_term
├── hasMany → tbl_company_product_category (self-referencing parent_id)
├── hasMany → tbl_company_stock_movement
├── hasMany → tbl_company_notification_log
├── hasMany → tbl_company_audit_log
├── hasMany → tbl_company_email_config                              ← (was hasOne, but allows multiple)
│   └── hasMany → tbl_company_email_template
└── hasMany → tbl_company_email_template                            ← (company-specific overrides)

tbl_ref_bank (shared reference)
tbl_ref_industry → hasMany → tbl_ref_industry_subcategory
tbl_company_partner → self-referencing (upline_id) for referral tree
```

---

## 6. Naming Conventions (enforced)

| Rule | Example |
|------|---------|
| Table prefix | `tbl_` |
| Singular names | `tbl_company_product` not `tbl_products` |
| Reference tables | `tbl_ref_` prefix |
| Pivot tables | `tbl_<entity>_<entity>_pivot` or just the relationship name |
| Foreign keys | `<entity>_id` (e.g. `company_id`, `partner_id`) |
| Boolean columns | `is_*` (e.g. `is_owner`, `is_primary`, `is_featured`) |
| Status columns | `ENUM` with lowercase values |
| Timestamps | `created_at`, `updated_at`, `deleted_at` |
| Datetime events | `*_at` suffix (e.g. `verified_at`, `last_login_at`) |
| JSON columns | descriptive name (e.g. `metadata`, `permissions`, `company_info`) |
| VARCHAR over TEXT | Use `VARCHAR(n)` with explicit max lengths, `TEXT` only for user-generated content |

---

## 7. Future Tables (not built now, but schema-ready)

These are NOT included in v1 but the schema supports them:

| Table | Purpose | When |
|-------|---------|------|
| `tbl_order` | Sales orders | When e-commerce flow is built |
| `tbl_order_item` | Order line items | When e-commerce flow is built |
| `tbl_payment` | Payment records | When payment gateway integrated |
| `tbl_commission` | Partner commission tracking | When commission rules are defined |
| `tbl_commission_payout` | Payout batches | When payout system is built |
| `tbl_price_list` | Partner-specific pricing tiers | When tiered pricing is needed |
| `tbl_address` | Polymorphic addresses | When shipping is needed |

---

## 8. Migration Plan

### Phase 1: Create new tables (in dependency order)
1. Reference tables first: `tbl_ref_bank`, `tbl_ref_industry`, `tbl_ref_industry_subcategory`
2. `tbl_company_agreement` (no FK dependencies)
3. `tbl_company` (depends on agreement)
4. `tbl_company_role` (depends on company)
5. `tbl_company_admin` (depends on company + role)
6. `tbl_company_industry` (depends on company + subcategory)
7. `tbl_company_partner` (depends on company, self-referencing upline)
8. `tbl_partner_bank_account`, `tbl_partner_document` (depends on partner + bank/admin)
9. Product tables: `tbl_company_product_category`, `tbl_company_product`, `tbl_company_product_category_pivot`, `tbl_company_attribute`, `tbl_company_attribute_term`, `tbl_company_product_variation`, `tbl_company_variation_attribute`, `tbl_company_product_image`
10. `tbl_company_stock_movement` (depends on product + variation + admin)
11. `tbl_company_email_config`, `tbl_company_email_template` (depends on company + config)
12. `tbl_company_verification_token`, `tbl_company_notification_log`, `tbl_company_audit_log` (polymorphic, depends on company)
- Seed reference data (banks, industries)

### Phase 2: Migrate existing data
- Map old `tbl_company` → new `tbl_company` (transform `mediumtext` → `varchar`, verification enum → datetime)
- Map old `tbl_partners` → new `tbl_company_partner`
- Map old `tbl_products` → new `tbl_company_product`
- Products, variations, attributes, categories — mostly 1:1 with column renames
- Consolidate logs into `tbl_company_audit_log` and `tbl_company_notification_log`

### Phase 3: Update application code
- Update all Models (new table names, relationships)
- Update Controllers (new column names)
- Update Views (new field names)

### Phase 4: Cleanup
- Drop old tables
- Verify FK integrity
- Run full test suite

---

## 9. Design Decisions & Notes

| Decision | Rationale |
|----------|-----------|
| `referral_code` is globally UNIQUE (not per-tenant) | Enables cross-tenant referral links — a partner's code works regardless of which company page they land on |
| `company_id` denormalized on `tbl_partner_bank_account`, `tbl_partner_document`, `tbl_company_stock_movement` | Allows direct tenant-scoped queries without joining through parent tables — important for admin dashboards and data isolation |
| Soft delete on `tbl_company`, `tbl_company_admin`, `tbl_company_partner`, `tbl_company_product`, `tbl_company_product_variation` | Preserves audit trail and order history; hard delete only on pivot/reference tables |
| Stock fields on `tbl_company_product` ignored for `type='variable'` | For variable products, stock is managed per-variation in `tbl_company_product_variation`. Application layer must enforce this — ignore product-level stock when `type='variable'` |
| `tbl_company_email_template.company_id` nullable | `NULL` = system default template. Per-company row with same slug = company override. App logic: query company-specific first, fall back to `NULL` |
| `tbl_company_agreement` is global (no `company_id`) | All tenants accept the same platform T&C. If per-company T&C is needed later, add `company_id` nullable (same pattern as email templates) |
| `tbl_company_product_category` ON DELETE SET NULL for `parent_id` | Deleting a parent category promotes children to root level instead of cascading deletion |
| Polymorphic tables (`verification_token`, `notification_log`, `audit_log`) | Avoids table explosion — one table serves multiple actor/subject types. Trade-off: no FK enforcement on polymorphic columns |
