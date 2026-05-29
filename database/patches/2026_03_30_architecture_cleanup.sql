-- =============================================
-- P35: Phase 5 — Architecture Cleanup
-- Created: 2026-03-30
-- =============================================

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
