-- =============================================
-- Remove hardcoded u365 branding from Telegram module
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.3.2', 'Remove hardcoded u365 branding from Telegram module',
    'Replaced 8 hardcoded u365/U365/u365.fun references in TelegramController (test message, connect message, daily report, campaign report, BO user report, channel attribution) and reports blade template with dynamic Configuration::get(portal_name). Telegram messages now follow the portal branding.',
    '{"files_changed": 2, "type": "bugfix"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
