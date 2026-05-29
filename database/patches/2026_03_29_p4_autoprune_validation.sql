-- =============================================
-- P4: Auto-Prune, Config Validation, Session Warning
-- Created: 2026-03-29
-- =============================================

-- ── Add backup log retention config ──────────

INSERT INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('advanced', 'backup_log_retention_days', '30', 'number', 'Backup Log Retention (days)',
     'Auto-delete backup log entries older than this many days after each backup. Set 0 to disable.',
     NULL, '30', 50, 1)
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `description` = VALUES(`description`);

-- ── Clear config cache ──────────────────────

DELETE FROM `cache` WHERE `key` = 'config_all';

-- ── Changelog ──────────────────────────────────

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.5.0', 'Auto-Prune, Config Validation & Session Warning',
    '**Auto Backup Log Pruning**\n• Backup logs older than configurable retention (default 30 days) are automatically pruned after each backup completes\n• Configurable via Configuration → Advanced → Backup Log Retention\n• Always keeps logs for the 5 most recent backup runs regardless of age\n\n**Config Value Validation**\n• Color fields now validated as proper hex/rgba before saving\n• Number fields validated for range (0–9999)\n• Select fields validated against allowed options\n• Invalid values are skipped with a warning message — prevents broken CSS variables\n\n**Session Timeout Warning**\n• Red banner appears 5 minutes before the 8-hour session cookie expires\n• Warns admin to save work and re-login\n• Includes Refresh and Dismiss buttons',
    '{"features":["auto-prune-backup-logs","config-validation","session-timeout-warning"]}',
    NOW()
);
