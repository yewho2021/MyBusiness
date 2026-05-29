-- =============================================
-- Phase 1: Bug Fixes
-- Created: 2026-05-27
-- =============================================

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'config_%';
