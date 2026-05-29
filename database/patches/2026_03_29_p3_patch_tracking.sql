-- =============================================
-- P3: Patch Tracking + Auto-Cache Invalidation
-- Created: 2026-03-29
-- =============================================

-- ── Patch tracking table ──────────────────────

CREATE TABLE IF NOT EXISTS `tbl_patches_applied` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `file_name` varchar(255) NOT NULL,
    `file_size` int(10) unsigned NOT NULL DEFAULT 0,
    `file_hash` varchar(32) DEFAULT NULL COMMENT 'MD5 hash of ZIP contents',
    `code_files` int(10) unsigned NOT NULL DEFAULT 0,
    `sql_files` int(10) unsigned NOT NULL DEFAULT 0,
    `files_ok` int(10) unsigned NOT NULL DEFAULT 0,
    `files_err` int(10) unsigned NOT NULL DEFAULT 0,
    `sql_ok` int(10) unsigned NOT NULL DEFAULT 0,
    `sql_err` int(10) unsigned NOT NULL DEFAULT 0,
    `elapsed_ms` int(10) unsigned NOT NULL DEFAULT 0,
    `status` enum('success','partial','failed') NOT NULL DEFAULT 'success',
    `admin_id` bigint(20) unsigned DEFAULT NULL,
    `admin_name` varchar(100) DEFAULT NULL,
    `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `log` longtext DEFAULT NULL COMMENT 'JSON array of log entries',
    PRIMARY KEY (`id`),
    INDEX `idx_file_name` (`file_name`),
    INDEX `idx_file_hash` (`file_hash`),
    INDEX `idx_applied_at` (`applied_at`),
    INDEX `idx_admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Clear stale caches (sidebar will rebuild with lightweight objects) ──
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

-- ── Changelog ──────────────────────────────────

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.4.0', 'Patch Tracking + Auto-Cache + Dashboard Fix',
    '**New: Patch Tracking System**\n• Every applied patch is now recorded in tbl_patches_applied with filename, hash, file counts, status, and full log\n• Preview step detects duplicate patches — warns if same file or same content was previously applied\n• Patch History table shown on System Patch page with stats and timeline\n\n**Auto-Cache Invalidation**\n• Sidebar and dashboard caches now auto-clear when menus, menu groups, roles, or permissions are saved or deleted\n• No more stale sidebar after menu changes — uses InvalidatesMenuCache trait on 4 models\n\n**Dashboard Fix**\n• Control panel colors now use a rotating palette instead of hardcoded group IDs — no more broken colors when groups are reordered\n\n**Files Changed:**\n• app/Models/PatchHistory.php (NEW)\n• app/Traits/InvalidatesMenuCache.php (NEW)\n• app/Models/AdminMenu.php (trait added)\n• app/Models/AdminMenuGroup.php (trait added)\n• app/Models/AdminRole.php (trait added)\n• app/Models/AdminRoleMenuAccess.php (trait added)\n• app/Http/Controllers/Admin/SystemPatchController.php (tracking + duplicate detection)\n• app/Http/Controllers/Admin/DashboardController.php (rotating palette)\n• resources/views/admin/pages/system/patch.blade.php (history table + warning)',
    '{"features":["patch-tracking","auto-cache-invalidation","dashboard-palette-fix"]}',
    NOW()
);
