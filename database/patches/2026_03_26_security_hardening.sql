-- =============================================
-- Phase 1: Security Hardening
-- Created: 2026-03-26
-- =============================================

-- Add admin_id column to query_history (track who ran each query)
ALTER TABLE `tbl_query_history`
    ADD COLUMN `admin_id` BIGINT UNSIGNED NULL AFTER `id`;

-- Add index for lookup
ALTER TABLE `tbl_query_history`
    ADD INDEX `idx_admin_id` (`admin_id`);

-- Clear sidebar cache
DELETE FROM `cache` WHERE `key` LIKE '%sidebar_menu%';

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.5.0',
    'Phase 1: Security Hardening',
    'Critical security fixes and hardening:\n\n1. SQL Injection Fix (CRITICAL):\n- deleteRow() was interpolating raw user input into DELETE WHERE clause\n- Now uses parameterized query with pk_column/pk_value validation\n- JS updated to send pk_column + pk_value instead of raw WHERE string\n\n2. Session Fixation Fix:\n- Added session()->regenerate() after successful login in completeLogin()\n- Prevents session fixation attacks\n\n3. Password Policy Consistency:\n- Users page HTML minlength updated from 6 to 8 to match FormRequest rules\n- All password fields now enforce min:8 + uppercase + digit\n\n4. Legacy Cookie Removal:\n- Removed plain-numeric cookie fallback from DecryptsCookie trait\n- Only encrypted cookies accepted now — tampered cookies rejected\n\n5. Query Audit Trail:\n- Added admin_id column to tbl_query_history\n- All SQL queries now track which admin executed them\n- QueryHistory model has admin() relationship\n\n6. File Operation Safety:\n- Added error checks on all file_put_contents() calls in SystemPatch, Export, Database controllers\n- Added fopen() return check before fwrite() in database export\n- Failures now return proper error messages instead of silent corruption\n\n7. Timezone Fix:\n- Removed hardcoded Asia/Kuala_Lumpur from DatabaseController\n- Now uses app timezone via now() consistently',
    '{"files_changed": ["DatabaseController.php", "LoginController.php", "DecryptsCookie.php", "QueryHistory.php", "SystemPatchController.php", "ExportController.php", "users/index.blade.php", "query.blade.php"], "security_fixes": ["SQL_injection_deleteRow", "session_fixation", "legacy_cookie_bypass"]}',
    NOW()
);
