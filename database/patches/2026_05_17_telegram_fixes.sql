-- =============================================
-- Fix: Telegram delete + Disconnect & Clear + branding
-- Created: 2026-05-17
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.3.5', 'Fix: Telegram delete + Disconnect & Clear + branding',
    'Fixed missing confirmAction() JS function that silently broke 4 actions: delete target, delete report, save source code, bulk send. Added Disconnect & Clear button on Setup tab — truncates bot config and all saved targets with confirmation. Replaced 7 hardcoded u365 references with dynamic portal_name from Configuration.',
    '{"files_changed": 2, "type": "bugfix+enhancement", "bugs_fixed": 2}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
