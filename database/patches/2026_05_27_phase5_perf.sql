-- =============================================
-- Phase 5: Performance & Scale
-- Created: 2026-05-27
-- =============================================

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
