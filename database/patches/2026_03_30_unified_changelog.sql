-- =============================================
-- P11: Unified Versioning + Changelog
-- Created: 2026-03-30
-- =============================================

-- ── Update Changelog menu icon (now version-based) ──

UPDATE `tbl_admin_menus`
    SET `icon` = 'fas fa-code-branch', `title` = 'Changelog'
    WHERE `route_name` = 'admin.changelog.index';

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';
