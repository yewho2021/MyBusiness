-- =============================================
-- P2 Code Quality & Hardening
-- Created: 2026-03-29
-- =============================================

-- ── FK constraints for referential integrity ──────────

-- tbl_admin_log.admin_id → tbl_admin.id (SET NULL on delete, so logs survive admin deletion)
ALTER TABLE `tbl_admin_log`
    ADD CONSTRAINT `fk_admin_log_admin_id`
    FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- tbl_query_history.admin_id → tbl_admin.id
ALTER TABLE `tbl_query_history`
    ADD CONSTRAINT `fk_query_history_admin_id`
    FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- tbl_export_history.admin_id → tbl_admin.id
ALTER TABLE `tbl_export_history`
    ADD CONSTRAINT `fk_export_history_admin_id`
    FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- tbl_backup_runs.job_id → tbl_backup_jobs.id (SET NULL — manual runs have no job)
ALTER TABLE `tbl_backup_runs`
    ADD CONSTRAINT `fk_backup_runs_job_id`
    FOREIGN KEY (`job_id`) REFERENCES `tbl_backup_jobs`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- ── Clear stale sidebar cache (will rebuild with lightweight objects) ──
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

-- ── Changelog ──────────────────────────────────

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.3.1', 'P2 Code Quality & Hardening',
    '**Security:**\n• IP geolocation now uses HTTPS (ipwho.is) instead of plaintext HTTP (ip-api.com) — admin IPs no longer leak in transit\n• New IP login check limited to 90-day history for performance\n\n**Stability:**\n• Sidebar menu cache now stores lightweight objects instead of full Eloquent models — prevents deserialization crashes on model changes, reduces cache size ~90%\n• Added foreign key constraints on tbl_admin_log, tbl_query_history, tbl_export_history, tbl_backup_runs — prevents orphaned records\n\n**Files Changed:**\n• app/Models/AdminLog.php (HTTPS geolocation)\n• app/Http/Controllers/Admin/Auth/LoginController.php (bounded IP history query)\n• resources/views/admin/partials/menu_left.blade.php (lightweight sidebar cache)',
    '{"fixes":["P2-https-geolocation","P2-sidebar-cache-lightweight","P2-fk-constraints","P2-bounded-ip-query"]}',
    NOW()
);
