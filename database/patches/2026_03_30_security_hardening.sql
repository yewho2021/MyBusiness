-- =============================================
-- P30: Phase 3 — Security Hardening
-- Created: 2026-03-30
-- =============================================

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

DELETE FROM `cache` WHERE `key` LIKE 'config_%';
