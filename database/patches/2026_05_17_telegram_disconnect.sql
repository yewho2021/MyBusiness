-- =============================================
-- Telegram: Disconnect & Clear button
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.3.4', 'Telegram: Disconnect & Clear button',
    'Added Disconnect & Clear button to Telegram Setup tab. Removes bot token and all saved targets in one click with confirmation. Re-applied dynamic portal_name branding across all Telegram messages.',
    '{"files_changed": 2, "type": "enhancement"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
