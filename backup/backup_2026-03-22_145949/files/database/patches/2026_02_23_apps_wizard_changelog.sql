INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES 
('apps', '1.0.1', 'Post-Verification Setup Wizard', 
'### Added (Post-Verification Setup Wizard)
- **Agreement Step**: After email & phone OTP verification, users must accept the latest Terms & Conditions before accessing the portal. Agreements are versioned in `tbl_company_agreement` (fetched by `ORDER BY id DESC`).
- **Company Info Step**: A guided form collects entity type, legal name, registration details (BRN/NRIC/Passport/Army), TIN, SST, TARC code, and contact address before granting dashboard access.
- **Wizard Progress Tracking**: Added `setup_step` column to `tbl_company` (0=pending, 1=agreement, 2=info, 3=complete). Users who logout mid-wizard resume at their exact step upon re-login.
- **New Middleware**: `EnsureSetupComplete` middleware gates dashboard access until all wizard steps are finished.
- **New Controller**: `SetupWizardController` handles agreement display/acceptance and company info submission.
- **New Model**: `CompanyAgreement` for versioned agreement content management.

### Changed
- **TARC Code**: Moved TARC code storage directly into `tbl_company.tarc_code` column.
- **AuthController**: Login now checks `setup_step` and redirects to the correct wizard step if setup is incomplete.
- **VerificationController**: Phone verification success now redirects to the agreement step instead of the dashboard.
- **Routes**: Added `/setup/agreement` and `/setup/info` routes; dashboard now requires `setup.complete` middleware.
- **Company Model**: Added `setup_step`, `tarc_code`, `agreement_id`, `agreement_accepted_at`, `company_info` to fillable fields.

### Database (SQL Applied)
```sql
ALTER TABLE tbl_company ADD COLUMN setup_step TINYINT NOT NULL DEFAULT 0;
ALTER TABLE tbl_company ADD COLUMN tarc_code VARCHAR(50) NULL;
ALTER TABLE tbl_company ADD COLUMN agreement_id BIGINT UNSIGNED NULL;
ALTER TABLE tbl_company ADD COLUMN agreement_accepted_at DATETIME NULL;
ALTER TABLE tbl_company ADD COLUMN company_info TEXT NULL;
CREATE TABLE tbl_company_agreement (id, version, content, created_at);
```

### Files Modified
- `app/Models/Company.php`
- `app/Models/CompanyAgreement.php` [NEW]
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/SetupWizardController.php` [NEW]
- `app/Http/Controllers/VerificationController.php`
- `app/Http/Middleware/EnsureSetupComplete.php` [NEW]
- `bootstrap/app.php`
- `routes/web.php`
- `resources/views/setup/agreement.blade.php` [NEW]
- `resources/views/setup/info.blade.php` [NEW]
- `database/patches/2026_02_23_wizard_setup.sql` [NEW]', 
'{"modified_files":["Company.php","CompanyAgreement.php","AuthController.php","SetupWizardController.php","VerificationController.php","EnsureSetupComplete.php","app.php","web.php","agreement.blade.php","info.blade.php"],"sql_patches":["2026_02_23_wizard_setup.sql"],"new_tables":["tbl_company_agreement"],"new_columns":["setup_step","tarc_code","agreement_id","agreement_accepted_at","company_info"]}', 
'2026-02-23 09:24:00');
