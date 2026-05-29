-- Add destination_path to backup jobs and runs
-- Run this on your database before deploying the code changes

ALTER TABLE `tbl_backup_jobs` ADD COLUMN `destination_path` VARCHAR(255) NULL DEFAULT NULL AFTER `cron_expression`;

ALTER TABLE `tbl_backup_runs` ADD COLUMN `destination_path` VARCHAR(255) NULL DEFAULT NULL AFTER `folder_name`;

-- Changelog entry
INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES 
('office', '1.3.0', 'Backup: Configurable Destination Path', 
'- **Destination Path**: Backup jobs now support a configurable destination path. Can be relative to project root (e.g. `backup`) or absolute (e.g. `/home/syncoffice/backups`). Defaults to `backup/` if left empty.
- **Portable**: Destination path is stored per-job and per-run, making backups portable across different cPanel accounts and server setups.
- **UI**: New "Destination Path" field added to the Create/Edit Backup Job form with helper text explaining relative vs absolute paths.', 
'{"modified_files":["BackupJob.php","BackupRun.php","BackupService.php","BackupController.php","jobs.blade.php"]}', 
NOW());
