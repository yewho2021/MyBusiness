-- =============================================
-- Remove ALL remaining hardcoded u365 branding
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.3.6', 'Remove all remaining hardcoded u365 branding',
    'Deep scan found and replaced 15 hardcoded u365 references in TelegramReportBuilder.php (auto-report footers for daily, campaign, pixel summary, BO users, recovery, alerts, custom reports) plus 1 in reports blade. Added portalName() helper using Configuration::get(portal_name). Zero hardcoded brand references remain in codebase.',
    '{"files_changed": 2, "type": "bugfix", "u365_refs_removed": 16}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
