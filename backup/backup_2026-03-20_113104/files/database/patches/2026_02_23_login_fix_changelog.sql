INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES 
('apps', '1.0.3', 'Login Fix: Missing Admin Record', 
'### Fixed (Critical)
- **Root cause**: `tbl_company_admin` had zero records for Company `synctech` (ID 7). The role insert failed silently because the initial script tried to insert `created_at`/`updated_at` columns that don''t exist on `tbl_company_admin_role`.
- **Fix applied**: Created Master Admin role (ID 2) and admin user (ID 2) for Company ID 7 using raw DB inserts matching the actual table schema.
- **Password**: Hashed via `Hash::make()` and verified with `Hash::check()` — confirmed VALID.

### Database (SQL Applied)
```sql
ALTER TABLE tbl_company MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL;
INSERT INTO tbl_company_admin_role (companyid, name, is_owner, status) VALUES (7, ''Master Admin'', 1, ''Active'');
INSERT INTO tbl_company_admin (...) VALUES (7, ''roderisland@hotmail.com'', ''<bcrypt_hash>'', ...);
```

### Files Modified
- `tbl_company` schema — `password` column made NULL DEFAULT NULL
- `tbl_company_admin_role` — inserted Master Admin role for company ID 7
- `tbl_company_admin` — inserted admin user with hashed password for company ID 7

### Verification
- Browser login test: synctech / roderisland@hotmail.com → landed on /dashboard with Welcome back!, role Master Admin ✅', 
'{"sql_changes":["ALTER TABLE tbl_company MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL","INSERT tbl_company_admin_role","INSERT tbl_company_admin"],"root_cause":"tbl_company_admin_role has no created_at/updated_at columns, causing silent insert failure","verified":"browser login test passed"}', 
'2026-02-23 10:07:00');
