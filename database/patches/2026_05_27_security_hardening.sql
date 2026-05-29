-- =============================================
-- Security Hardening: Path Traversal, Eval Audit, Rate Limits
-- Created: 2026-05-27
-- =============================================

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
