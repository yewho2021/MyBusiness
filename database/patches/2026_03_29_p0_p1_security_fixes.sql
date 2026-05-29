-- =============================================
-- P0/P1 Security & Stability Fixes
-- Created: 2026-03-29
-- =============================================

-- Add index on tbl_backup_logs.logged_at for faster log queries
ALTER TABLE `tbl_backup_logs`
    ADD INDEX `idx_backup_logs_logged_at` (`logged_at`);

-- Clear stale config cache (will be rebuilt with encrypted sensitive values on next load)
DELETE FROM `cache` WHERE `key` = 'config_all';
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

-- Changelog entry
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.3.0', 'P0/P1 Security & Stability Fixes',
    '**Security:**\n• Fixed sensitive config values (mail_password) being stored as plaintext in cache table — now encrypted at rest with self-healing auto-encryption\n• File Manager custom routes (get-content, create-file) now go through RBAC middleware — previously bypassed permission checks\n• Password expiry middleware (CheckPasswordExpiry) now active in the middleware stack\n\n**Code Quality:**\n• Extracted shared SqlParser service — eliminated 260+ lines of duplicated SQL parsing code between DatabaseController and SystemPatchController\n• New backup:prune-logs Artisan command for automated backup log cleanup (tbl_backup_logs had 8,788+ rows)\n• Added database index on tbl_backup_logs.logged_at for faster log queries\n\n**Files Changed:**\n• app/Services/SqlParser.php (NEW)\n• app/Console/Commands/BackupPruneLogsCommand.php (NEW)\n• app/Models/Configuration.php (self-healing encryption)\n• app/Http/Controllers/Admin/ConfigurationController.php (encrypt on save)\n• app/Http/Controllers/Admin/DatabaseController.php (use SqlParser)\n• app/Http/Controllers/Admin/SystemPatchController.php (use SqlParser)\n• bootstrap/app.php (admin.password middleware alias)\n• routes/admin.php (FM routes + password middleware)\n• routes/web.php (FM routes removed)\n• resources/views/admin/pages/filemanager/index.blade.php (URL update)',
    '{"fixes":["P0-cache-plaintext-password","P0-filemanager-rbac-bypass","P1-password-expiry-middleware","P1-duplicated-sqlparser","P1-backup-log-pruning","P1-backup-log-index"]}',
    NOW()
);
