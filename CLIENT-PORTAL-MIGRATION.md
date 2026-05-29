# Client Portal Migration Study
> **From:** apps.mybusiness.com.my (mybusiness_b2b database)
> **To:** apps.mybusiness.com.my (mybusiness_db — shared with office portal)
> **Date:** 2026-05-29
> **Status:** Deep study complete — ready for implementation

---

## 1. Current State

### Old Setup
- **Database:** `mybusiness_b2b` (separate database, 56 tables)
- **DB User:** `mybusiness_b2b`
- **Framework:** Laravel 11.31
- **Location on server:** `~/apps.mybusiness.com.my`
- **Auth guards:** `company` (CompanyAdmin), `partner` (Partner)

### Demo Data in old DB

| Table | Rows | Notes |
|-------|------|-------|
| tbl_company | 2 | 2 test companies |
| tbl_company_admin | 2 | 1 admin per company |
| tbl_company_admin_role | 2 | Owner role per company |
| tbl_company_agreement | 1 | 1 T&C version |
| tbl_partners | 20 | Test partners |
| tbl_products | 20 | Test products |
| tbl_product_categories | 10 | Product categories |
| tbl_product_variations | 18 | Variable product SKUs |
| tbl_product_images | 24 | Product gallery images |
| tbl_attributes | 3 | Color, Size, Material |
| tbl_attribute_terms | 11 | Red, Blue, S, M, L, etc. |
| tbl_variation_attributes | 33 | Variation-attribute links |
| tbl_partner_bank_accounts | 9 | Partner bank details |
| tbl_media | 23 | Uploaded media files |
| tbl_system_email_smtp | 1 | SMTP config |
| tbl_system_email_templates | 15 | Email templates |
| tbl_product_category_pivot | 39 | Product-category links |
| tbl_partner_activity_log | 6 | Login history |
| tbl_system_bank | 20 | Malaysian banks |
| tbl_industry | 14 | Industry list |
| tbl_industry_subcategory | 176 | Industry subcategories |

---

## 2. Table Name Mapping (Old → New)

| # | Old Table (mybusiness_b2b) | New Table (mybusiness_db) | Column Renames |
|---|---------------------------|--------------------------|----------------|
| 1 | `tbl_company` | `tbl_company` | `username` removed, `companyname`→`company_name`, `mobileno`→`mobile_no`, verification enums→datetime |
| 2 | `tbl_company_admin` | `tbl_company_admin` | `companyid`→`company_id`, `roleid`→`role_id`, `mobileno`→`mobile_no`, `datetime_registration`→`created_at`, `datetime_lastlogin`→`last_login_at` |
| 3 | `tbl_company_admin_role` | `tbl_company_role` | `companyid`→`company_id`, added `slug`, `permissions` JSON |
| 4 | `tbl_company_agreement` | `tbl_company_agreement` | Added `title`, `is_active` |
| 5 | `tbl_company_tarc` | `tbl_company_verification_token` | Merged into polymorphic table |
| 6 | `tbl_partners` | `tbl_company_partner` | `company_id` stays, all columns match |
| 7 | `tbl_partner_bank_accounts` | `tbl_company_partner_bank_account` | Added `is_primary`, `updated_at` |
| 8 | `tbl_partner_documents` | `tbl_company_partner_document` | Added `file_size`, `remarks`, `reviewed_by`, `reviewed_at` |
| 9 | `tbl_products` | `tbl_company_product` | `companyid`→`company_id`, removed `is_virtual`, `is_downloadable`, `external_url`, `button_text`, `purchase_note` |
| 10 | `tbl_product_categories` | `tbl_company_product_category` | `companyid`→`company_id`, added `sort_order` |
| 11 | `tbl_product_category_pivot` | `tbl_company_product_category_pivot` | No changes |
| 12 | `tbl_product_variations` | `tbl_company_product_variation` | Added `cost_price`, `sort_order`, `deleted_at` |
| 13 | `tbl_product_images` | `tbl_company_product_image` | Added `alt_text` |
| 14 | `tbl_attributes` | `tbl_company_attribute` | `companyid`→`company_id`, added `color` type, `sort_order` |
| 15 | `tbl_attribute_terms` | `tbl_company_attribute_term` | Added `color_code`, `sort_order` |
| 16 | `tbl_variation_attributes` | `tbl_company_variation_attribute` | No changes |
| 17 | `tbl_stock_movements` | `tbl_company_stock_movement` | `companyid`→`company_id`, `reference_no`→polymorphic `reference_type`+`reference_id`, added `transfer` type |
| 18 | `tbl_media` | — | Keep as-is or migrate to Spatie media library |
| 19 | `tbl_system_bank` | `tbl_ref_bank` | `description`→`name`, added `swift_code` |
| 20 | `tbl_industry` | `tbl_ref_industry` | Added `sort_order` |
| 21 | `tbl_industry_subcategory` | `tbl_ref_industry_subcategory` | `industryid`→`industry_id`, added `sort_order` |
| 22 | `tbl_company_industry` | `tbl_company_industry` | No changes |
| 23 | `tbl_system_email_smtp` | `tbl_company_email_config` | Restructured completely |
| 24 | `tbl_system_email_templates` | `tbl_company_email_template` | Added `company_id`, `variables` JSON |
| 25 | `tbl_partner_activity_log` | `tbl_company_audit_log` | Merged into unified audit log |
| 26 | `tbl_partner_notes` | `tbl_company_audit_log` | Merged (action='note') |
| 27 | `tbl_partner_verification_tokens` | `tbl_company_verification_token` | Merged into polymorphic table |

### Tables REMOVED (not migrated)
- `tbl_partner` (legacy duplicate of `tbl_partners`)
- `tbl_partner_bankdetails` (empty, replaced)
- `tbl_partner_access_log` (merged into audit_log)
- `tbl_partner_email_log` (merged into notification_log)
- `tbl_company_admin_access_log` (merged into audit_log)
- `tbl_company_admin_email_log` (merged into notification_log)
- `tbl_marketing_*` (4 tables — removed, not SaaS architecture)

---

## 3. Model Mapping (Old → New)

| Old Model | Old Table | New Model | New Table |
|-----------|-----------|-----------|-----------|
| `Company` | `tbl_company` | `Company` | `tbl_company` |
| `CompanyAdmin` | `tbl_company_admin` | `CompanyAdmin` | `tbl_company_admin` |
| `CompanyAdminRole` | `tbl_company_admin_role` | `CompanyRole` | `tbl_company_role` |
| `CompanyAgreement` | `tbl_company_agreement` | `CompanyAgreement` | `tbl_company_agreement` |
| `Partner` | `tbl_partners` | `CompanyPartner` | `tbl_company_partner` |
| `PartnerBankAccount` | `tbl_partner_bank_accounts` | `CompanyPartnerBankAccount` | `tbl_company_partner_bank_account` |
| `PartnerDocument` | `tbl_partner_documents` | `CompanyPartnerDocument` | `tbl_company_partner_document` |
| `PartnerActivityLog` | `tbl_partner_activity_log` | — (merged into `CompanyAuditLog`) | `tbl_company_audit_log` |
| `PartnerVerificationToken` | `tbl_partner_verification_tokens` | — (merged into `CompanyVerificationToken`) | `tbl_company_verification_token` |
| `PartnerNote` | `tbl_partner_notes` | — (merged into `CompanyAuditLog`) | `tbl_company_audit_log` |
| `Product` | `tbl_products` | `CompanyProduct` | `tbl_company_product` |
| `ProductCategory` | `tbl_product_categories` | `CompanyProductCategory` | `tbl_company_product_category` |
| `ProductVariation` | `tbl_product_variations` | `CompanyProductVariation` | `tbl_company_product_variation` |
| `ProductImage` | `tbl_product_images` | `CompanyProductImage` | `tbl_company_product_image` |
| `Attribute` | `tbl_attributes` | `CompanyAttribute` | `tbl_company_attribute` |
| `AttributeTerm` | `tbl_attribute_terms` | `CompanyAttributeTerm` | `tbl_company_attribute_term` |
| `StockMovement` | `tbl_stock_movements` | `CompanyStockMovement` | `tbl_company_stock_movement` |
| `Media` | `tbl_media` | `CompanyMedia` | `tbl_media` (keep or migrate) |
| — (no model) | `tbl_system_bank` | `RefBank` | `tbl_ref_bank` |

---

## 4. Key Code Changes Needed

### 4.1 Database Connection
**File:** `.env`
```
DB_DATABASE=mybusiness_db          # was: mybusiness_b2b
DB_USERNAME=mybusiness_user        # was: mybusiness_b2b
DB_PASSWORD=.XZ(X9Uq{dXDnYQw     # was: SrQ-]8eS.6@p2O9(
```

### 4.2 Model Updates (ALL models need)
- Update `$table` property to new table names
- Update `$fillable` for renamed columns
- Update foreign key names in relationships
- Update `BelongsToCompany` trait to use `company_id` (not `companyid`)
- Remove old columns from `$fillable` (`username`, `is_virtual`, etc.)

### 4.3 Controller Updates
- Update column references (`companyid`→`company_id`, `companyname`→`company_name`, etc.)
- Update model class names (`CompanyAdminRole`→`CompanyRole`, `Partner`→`CompanyPartner`, etc.)
- Update validation rules for new column names

### 4.4 Auth Guards
**File:** `config/auth.php`
- `company_admins` provider → model: `CompanyAdmin`, table: `tbl_company_admin`
- `partners` provider → model: `CompanyPartner`, table: `tbl_company_partner`

### 4.5 BelongsToCompany Trait
- Default column: `company_id` (was `companyid` in most models)
- Partner model already used `company_id` — no change needed there

### 4.6 Views
- Update `@csrf` form field names if column names changed
- Update Blade variable references (e.g., `$company->companyname` → `$company->company_name`)

---

## 5. Data Migration Plan

### Phase A: Copy demo data from mybusiness_b2b → mybusiness_db
Tables with data to copy (mapped to new names):

1. `tbl_company` (2 rows) → `tbl_company` (transform columns)
2. `tbl_company_admin` (2 rows) → `tbl_company_admin` (transform columns)
3. `tbl_company_admin_role` (2 rows) → `tbl_company_role` (add slug)
4. `tbl_company_agreement` (1 row) → `tbl_company_agreement` (add title, is_active)
5. `tbl_partners` (20 rows) → `tbl_company_partner` (direct copy, most columns match)
6. `tbl_partner_bank_accounts` (9 rows) → `tbl_company_partner_bank_account`
7. `tbl_products` (20 rows) → `tbl_company_product` (transform companyid)
8. `tbl_product_categories` (10 rows) → `tbl_company_product_category`
9. `tbl_product_category_pivot` (39 rows) → `tbl_company_product_category_pivot`
10. `tbl_product_variations` (18 rows) → `tbl_company_product_variation`
11. `tbl_product_images` (24 rows) → `tbl_company_product_image`
12. `tbl_attributes` (3 rows) → `tbl_company_attribute`
13. `tbl_attribute_terms` (11 rows) → `tbl_company_attribute_term`
14. `tbl_variation_attributes` (33 rows) → `tbl_company_variation_attribute`
15. `tbl_system_email_smtp` (1 row) → `tbl_company_email_config`
16. `tbl_system_email_templates` (15 rows) → `tbl_company_email_template`
17. `tbl_media` (23 rows) → keep or create new table
18. `tbl_partner_activity_log` (6 rows) → `tbl_company_audit_log`

### Phase B: Update client portal code
- All models, controllers, views, routes to use new table/column names
- Switch .env to mybusiness_db

### Phase C: Test
- Company login flow
- Partner login flow
- Product CRUD
- Partner management

---

## 6. Client Portal Features (Existing)

### Working Features
- Company registration + dual OTP verification (email + SMS)
- Setup wizard (agreement + LHDN company info)
- Company admin login/logout
- Product CRUD (simple + variable, gallery, categories, attributes, variations)
- Partner management (CRUD, KYC docs, bank accounts, status workflow)
- Partner portal (login, profile, bank, documents, referral links)
- Media library (upload, browse, delete)

### Coming Soon (Placeholder Routes Only)
- Orders & order management
- Partner invitation system
- Commission rules & tracking
- Performance analytics
- Payout management
- Finance & e-Invoice
- Company settings
- Reports & analytics
