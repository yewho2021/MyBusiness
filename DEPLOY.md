# Deployment Guide

## Pre-Deploy Checklist

**MANDATORY before every deploy.** Every issue we've hit in production came from skipping one of these steps.

### Step 1: PHP Syntax Check
```bash
# Check ALL PHP files for syntax errors
find app/ -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors"
```
Zero output = pass. Any output = fix before proceeding.

### Step 2: Route Verification
```bash
php artisan route:list
```
If this errors, a controller has a missing import, wrong class name, or syntax issue.

### Step 3: Blade View Compilation
```bash
php artisan view:clear && php artisan view:cache
```
Catches Blade syntax errors. Does NOT catch runtime errors (null access, missing variables).

### Step 4: Dependency Check
```bash
# Verify no imports reference packages that aren't installed
grep -r "use Spatie\\" app/ --include="*.php"     # Only if spatie packages installed
grep -r "use Intervention\\" app/ --include="*.php" # Only if intervention installed
```
If a model/controller imports a package not in `composer.json`, it will crash at runtime.

### Step 5: Data Integrity Check
```bash
# Check for old table names (from migration)
grep -rn "tbl_partners\|tbl_products[^_]\|tbl_attributes[^_]\|tbl_company_admin_role\|tbl_system_\|tbl_company_tarc" app/ --include="*.php"

# Check for old column names
grep -rn "companyid\|mobileno[^_]\|roleid\|datetime_lastlogin\|datetime_lastclick\|companyname" app/ --include="*.php" resources/ --include="*.blade.php"

# Check for old model class names
grep -rn "use App\\\\Models\\\\Partner;\|use App\\\\Models\\\\Product;\|use App\\\\Models\\\\Attribute;\|use App\\\\Models\\\\CompanyAdminRole" app/ --include="*.php"
```
Zero output = pass. Any output = old reference that will crash.

### Step 6: Security Check — Tenant Isolation
```bash
# Models with company_id MUST have BelongsToCompany trait (except Company, CompanyAdmin)
grep -L "BelongsToCompany" app/Models/Company*.php | grep -v "Company.php$\|CompanyAdmin.php$\|CompanyAgreement.php$\|CompanyVerificationToken.php$\|CompanyAuditLog.php$"
```
Any output = model missing tenant isolation. Products/partners/attributes could leak across companies.

### Step 7: Encrypted Route Token Check
```bash
# No raw ->id in route() calls (URLs must use encrypted tokens)
grep -rn "route(.*->id)" resources/views/ --include="*.blade.php" | grep -v "product_ids\|categories\|attributes\|checkbox"

# No findOrFail($id) in controllers (must use findByTokenOrFail)
grep -rn "findOrFail(\$id)" app/Http/Controllers/ --include="*.php"
```
Zero output = pass. Any output = raw IDs exposed in URLs.

### Step 8: Form Field Consistency
```bash
# Check views don't reference model properties that don't exist in $fillable
# Common after schema migration — form shows field, model doesn't save it
grep -rn "is_virtual\|is_downloadable\|external_url\|button_text\|purchase_note" resources/views/ --include="*.blade.php"
```
If form references fields not in the model's `$fillable`, data is silently lost on save.

### Step 9: Password Hashing Check
```bash
# If resetting passwords via script, verify $2y$ prefix (not $2b$ from Python bcrypt)
# Laravel ONLY accepts $2y$ hashes. Python bcrypt generates $2b$ — WILL CRASH.
# Always use PHP's password_hash() for Laravel passwords.
```

### Step 10: Final Commit
```bash
git status                    # Check for uncommitted changes
git diff --stat               # Review what changed
git add <specific files>      # Stage only intended files
git commit -m "message"       # Commit with clear message
```

**If ANY step fails, DO NOT DEPLOY. Fix the issue first.**

---

## Auto Deployment (GitHub Actions)

Every push to `main` automatically deploys to the live server via SSH.

### What happens on deploy:
1. GitHub Actions SSHs into the cPanel server
2. Runs `git pull origin main`
3. Caches config, routes, and views
4. Runs pending migrations
5. Restarts queue workers

### Workflow:
```
git add .
git commit -m "your message"
git push
```
That's it — the site goes live automatically.

### Monitor deploys:
Check status at: https://github.com/yewho2021/MyBusiness/actions

---

## Database Notes

### Remote Database Access
- **Host:** mybusiness.com.my
- **Database:** mybusiness_db
- **Port:** 3306
- **Charset:** utf8mb4_unicode_ci

Local `.env` has `DB_HOST=localhost` for production. For local dev/testing, either:
- Install MySQL locally, or
- Temporarily use `DB_HOST=mybusiness.com.my` (requires cPanel Remote MySQL whitelist)

### B2B Schema
The B2B schema (25 tables with `tbl_company_*` prefix) was deployed directly to the live database. Laravel migration records are synced so `php artisan migrate:status` recognizes them.

Reference data seeded:
- 24 Malaysian banks (with SWIFT codes)
- 15 industries, 81 subcategories

---

## Setup (one-time)

### 1. Generate SSH key on your server

```bash
ssh mybusiness@server28.synctechhosting.com -p 6262
ssh-keygen -t ed25519 -C "github-deploy" -f ~/.ssh/github_deploy -N ""
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
cat ~/.ssh/github_deploy
```

Copy the private key output from the last command.

### 2. Add GitHub Secrets

Go to: https://github.com/yewho2021/MyBusiness/settings/secrets/actions

Add these 4 secrets:

| Secret Name      | Value                                |
|------------------|--------------------------------------|
| SERVER_HOST      | server28.synctechhosting.com         |
| SERVER_PORT      | 6262                                 |
| SERVER_USER      | mybusiness                           |
| SERVER_SSH_KEY   | (paste the private key from step 1)  |

### 3. Test it

Push any small change to `main` and check the Actions tab on GitHub.

---

## Manual Deployment (fallback)

If auto-deploy fails, you can still deploy manually:

```bash
ssh mybusiness@server28.synctechhosting.com -p 6262
cd ~/office.mybusiness.com.my
git pull
```

---

## Local Development Setup

### Requirements
- PHP 8.2+ (installed at `C:\php`)
- Composer 2.x (installed at `C:\php\composer.phar`)
- Node.js (for Vite/frontend assets)

### PHP Extensions (enabled in C:\php\php.ini)
curl, fileinfo, gd, intl, mbstring, mysqli, openssl, pdo_mysql, sodium, zip

### Useful Commands
```bash
php artisan serve                    # Start dev server
php artisan route:list               # List all routes
php artisan view:cache               # Compile Blade views
php artisan migrate:status           # Check migration status
php artisan tinker                   # Interactive REPL
composer install                     # Install PHP dependencies
```
