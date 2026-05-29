INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES 
('apps', '1.0.2', 'Auth Logic Fix & Company Setup', 
'### Changed (Auth Logic)
- **RegistrationController**: Removed redundant `password` storage from `tbl_company` during registration. Passwords are now **only** stored on `tbl_company_admin`, which is the table the auth guard authenticates against.
- **tbl_company.password**: Column set to `NULLABLE` since authentication goes through `tbl_company_admin`, not `tbl_company`.
- **Company ID 8** (synctech) created with master admin role and credentials.

### Database (SQL Applied)
```sql
ALTER TABLE tbl_company MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL;
```

### Files Modified
- `app/Http/Controllers/RegistrationController.php` — removed `Hash::make($data[''password''])` from company creation
- `tbl_company` schema — `password` column made nullable', 
'{"modified_files":["RegistrationController.php"],"sql_changes":["ALTER TABLE tbl_company MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL"],"notes":"Password auth is via tbl_company_admin only"}', 
'2026-02-23 09:33:00');
