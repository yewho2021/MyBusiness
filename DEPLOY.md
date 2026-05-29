# Deployment Guide

## Pre-Deploy Checklist

Before every deploy, run these checks locally:

```bash
# 1. PHP syntax check on all app files
php -l app/Models/*.php
php -l app/Http/Controllers/Admin/*.php

# 2. Verify routes load without errors
php artisan route:list

# 3. Compile Blade views (catches template errors)
php artisan view:clear && php artisan view:cache

# 4. Check for uncommitted changes
git status
```

If any step fails, fix the issue before pushing.

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
