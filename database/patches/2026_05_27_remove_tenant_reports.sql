-- =============================================
-- Remove Tenant-Specific Report Data
-- Created: 2026-05-27
-- =============================================

-- Remove orphan built-in report registrations from tbl_telegram_reports
-- These reference slugs that no longer have built-in methods.
-- Reports created by the user via the Reports page (php_code or template) are preserved.
DELETE FROM `tbl_telegram_reports`
WHERE `slug` IN (
    'daily_summary', 'campaign_performance', 'pixel_summary',
    'bo_users', 'channel_recovery', 'visitor_traffic',
    'weekly_comparison', 'monthly_summary', 'visitor_channels',
    'spend_alert', 'no_reg_alert', 'ftd_notification', 'fb_campaign_daily'
)
AND (`php_code` IS NULL OR `php_code` = '')
AND (`report_type` IS NULL OR `report_type` = 'code');

-- Remove orphan subscriptions for deleted reports
DELETE FROM `tbl_telegram_subscriptions`
WHERE `report_id` NOT IN (SELECT `id` FROM `tbl_telegram_reports`);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'config_%';
