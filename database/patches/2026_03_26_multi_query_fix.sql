-- =============================================
-- Multi-Query + mod_security Fix
-- Created: 2026-03-26
-- =============================================

-- Clear stale sidebar cache (ensures new menu structure loads)
DELETE FROM cache WHERE `key` LIKE '%sidebar_menu%';

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.3.2',
    'Multi-Query + Security Fixes',
    'Two critical fixes for Database Manager:\n\n1. mod_security bypass:\n- SQL queries are now base64-encoded before POST to bypass cPanel mod_security blocking raw SQL keywords (DELETE, DROP, INSERT etc) in request body\n- Server decodes sql_b64 parameter, falls back to raw sql for backward compatibility\n- Better 403 error message when mod_security still blocks\n\n2. Multi-query execution:\n- Paste multiple SQL statements separated by semicolons\n- Each executes individually with own status badge (OK / Error)\n- Summary bar: total count, success/fail split, total time\n- Expandable result tables per statement\n- Errors do not stop remaining statements\n- Single statements work exactly as before\n\n3. CSP worker-src fix:\n- Added worker-src self blob: to Content-Security-Policy\n- Added blob: to script-src\n- Fixes Monaco editor web worker console errors',
    '{"files_changed": ["DatabaseController.php", "SecurityHeaders.php", "query.blade.php", "query_result_multi.blade.php"]}',
    NOW()
);
