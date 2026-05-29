-- =============================================
-- Phase 1: Security Hardening
-- Created: 2026-03-27
-- =============================================

-- Add missing index on tbl_configuration.key for faster single-key lookups
-- (Currently only has composite unique on group+key)
ALTER TABLE `tbl_configuration`
    ADD INDEX `idx_key` (`key`);

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.2.0', 'Phase 1: Security Hardening',
    'Fixed SQL escaping in database exports (addslashes→PDO::quote). Added encrypted storage for sensitive config values (mail_password). Switched SystemPatch file path validation from denylist to allowlist. Added per-request session validation in auth middleware. Reduced cookie lifetime from 7 days to 8 hours. Fixed RBAC to deny unknown routes for non-admin roles. Added CSP nonce infrastructure.',
    '{"phase": 1, "fixes": ["sql_escaping", "config_encryption", "patch_allowlist", "session_validation", "cookie_lifetime", "rbac_default_deny", "csp_nonce"], "files_changed": 7}',
    NOW()
);
