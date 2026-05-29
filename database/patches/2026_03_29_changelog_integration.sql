-- =============================================
-- P9: Changelog + Version Integration
-- Created: 2026-03-29
-- =============================================

-- ── Add version_id FK to tbl_changelog ───────

ALTER TABLE `tbl_changelog`
    ADD COLUMN `version_id` bigint(20) unsigned DEFAULT NULL AFTER `id`;

ALTER TABLE `tbl_changelog`
    ADD INDEX `idx_version_id` (`version_id`);

-- ── Changelog menu entry (idempotent) ────────

INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 4, NULL, 1, 'Changelog', 'fas fa-scroll', 'admin.changelog.index', 'changelog', 16, 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menus` WHERE `route_name` = 'admin.changelog.index');

INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.changelog.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

-- ── Changelog entry ──────────────────────────

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.1.0', 'Changelog + Version Integration',
    'Unified the version system with the changelog. Every patch now auto-creates a changelog entry with file change details. The changelog page shows linked version data including file lists with before/after code viewer.',
    '{"features":["changelog-version-link","auto-changelog","file-viewer-in-changelog"]}',
    NOW()
);
