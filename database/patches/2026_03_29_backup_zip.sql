-- =============================================
-- P10: Backup ZIP Output + Download
-- Created: 2026-03-29
-- =============================================

-- ── Add ZIP columns to tbl_backup_runs ───────

ALTER TABLE `tbl_backup_runs`
    ADD COLUMN `zip_path` VARCHAR(500) DEFAULT NULL AFTER `destination_path`;

ALTER TABLE `tbl_backup_runs`
    ADD COLUMN `zip_size` bigint(20) unsigned DEFAULT NULL AFTER `total_size`;

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

-- ── Changelog ────────────────────────────────

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.2.0', 'Backup ZIP Output + Download',
    '**Backup module enhanced with ZIP packaging and download.**\n\n**New Features:**\n• Backups now auto-package into a single .zip file after completion\n• Raw backup folder deleted after ZIP is verified — saves disk space\n• ZIP compression reduces backup size by 70-80% for text files\n• Download button in Backup Dashboard and History pages\n• ZIP size shown alongside uncompressed size\n• Restore from ZIP — auto-extracts before restoring\n• Legacy folder-based backups still supported for restore\n\n**Files Changed:**\n• app/Services/BackupService.php — ZIP packaging phase + ZIP extraction for restore\n• app/Http/Controllers/Admin/BackupController.php — download endpoint + auto-prune\n• app/Models/BackupRun.php — zip_path, zip_size columns + helper methods\n• Backup dashboard + history views — download button + ZIP size display',
    '{"features":["backup-zip","backup-download","zip-restore"]}',
    NOW()
);
