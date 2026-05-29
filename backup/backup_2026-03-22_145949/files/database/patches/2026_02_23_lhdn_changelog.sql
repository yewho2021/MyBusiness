INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES 
('apps', '1.1.0', 'LHDN-Compliant Entity Simplification', 
'### Changed (Major — LHDN Alignment)
- **Entity types**: Simplified to only **Company** and **Individual**. Removed General Public, Foreign Company, Foreign Individual, Exempted Person.
- **Registration types**: Company = BRN (auto-set), Individual = NRIC (auto-set). Removed Passport and Army options.
- **IC validation**: Individual IC must be exactly 12 digits, numeric only. JavaScript auto-strips non-digits.
- **Country**: Hardcoded to Malaysia (Phase 1, not editable).
- **UI**: Rebuilt with 3 sections (Entity Info, Tax Info, Contact Address) with dynamic show/hide per entity type.
- **LHDN-ready JSON**: company_info structured for direct mapping to LHDN e-Invoice API.

### Removed
- `tarc_code` column dropped from `tbl_company`
- Other Name / Alternate Name field
- TARC Code field
- Country dropdown
- Passport / Army registration types

### Database
```sql
ALTER TABLE tbl_company DROP COLUMN IF EXISTS tarc_code;
```

### Files Modified
- `EnsureSetupComplete.php` — Company/Individual with IC 12-digit regex
- `SetupWizardController.php` — LHDN validation, BRN/NRIC auto-set
- `Company.php` — removed tarc_code from fillable
- `info.blade.php` — rebuilt UI with entity toggle, IC input mask
- `2026_02_23_lhdn_simplify.sql` — drop tarc_code', 
'{"modified_files":["EnsureSetupComplete.php","SetupWizardController.php","Company.php","info.blade.php"],"sql_patches":["2026_02_23_lhdn_simplify.sql"],"dropped_columns":["tarc_code"],"removed_features":["Other Name","TARC Code","Passport","Army","Foreign Company","Foreign Individual","General Public","Exempted Person"],"lhdn_ready":true}', 
'2026-02-23 10:41:00');
