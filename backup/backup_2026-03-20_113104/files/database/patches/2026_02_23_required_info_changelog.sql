INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES 
('apps', '1.0.4', 'Enforce Required Basic Info in Wizard', 
'### Changed
- **EnsureSetupComplete middleware**: Now validates required fields even when `setup_step=3`. If required data is missing, resets user to step 2 (info form) and locks them in the wizard.
- **SetupWizardController**: Conditional backend validation per entity type:
  - Company / Foreign Company → Company name + Registration No. (SSM/BRN) required
  - Individual / Foreign Individual / General Public → Full name + IC/Passport No. required
  - Exempted Person → Name only required
- **info.blade.php**: Dynamic labels and required indicators update via JavaScript when entity type changes. Form pre-fills from existing `company_info` JSON.

### Files Modified
- `app/Http/Middleware/EnsureSetupComplete.php` — added `hasRequiredInfo()` check
- `app/Http/Controllers/SetupWizardController.php` — conditional validation rules per entity type
- `resources/views/setup/info.blade.php` — dynamic required fields UI', 
'{"modified_files":["EnsureSetupComplete.php","SetupWizardController.php","info.blade.php"],"validation_rules":{"Company":"companyname+reg_no required","Individual":"companyname+reg_no required","Exempted":"companyname required"}}', 
'2026-02-23 10:10:00');
