INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT 'office', '3.2.1', 'Fix: Users page toggle route',
'Bugfix: /users page was throwing HTTP 500 because the toggle button referenced route name admin.users.toggle instead of the correct admin.users.toggle-status.',
'{"files_changed": 1, "type": "bugfix"}', NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '3.2.1' AND `app_type` = 'office');
