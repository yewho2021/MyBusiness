-- =============================================
-- Add Clear Bot button to Telegram Setup
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.3.3', 'Add Clear Bot button to Telegram Setup',
    'Added red Clear Bot button next to Connect Bot / Test Only on the Telegram Setup tab. Clears the bot token via existing save endpoint and reloads page. Only visible when a bot is connected. Saved targets are preserved.',
    '{"files_changed": 1, "type": "enhancement"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
