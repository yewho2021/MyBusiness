-- =============================================
-- Admin Portal v2.1.1 — Micro-patch
-- Fixes: CheckAdminRole raw cookie, Media $guarded, tests deployed
-- =============================================

INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT 'office', '2.1.1', 'Security micro-patch',
'Security: CheckAdminRole middleware now uses DecryptsCookie trait (was reading raw encrypted cookie).\nSecurity: Media model mass assignment protection ($guarded) added.\nCode quality: 20 test methods deployed (AdminAuthTest.php).\nCleanup: Junk files flagged for manual removal.',
'{"files_changed": 4, "type": "bugfix"}', NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '2.1.1' AND `app_type` = 'office');
