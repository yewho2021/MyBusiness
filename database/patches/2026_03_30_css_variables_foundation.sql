-- =============================================
-- P14: Phase 1 — CSS Variable Foundation
-- Created: 2026-03-30
-- =============================================

-- ── Add missing CSS variables to tbl_configuration ──────

-- Success spectrum
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'c-success-light', '#f0fdf4', 'color', 'Success Light', 'Light green background for success badges and alerts', NULL, '#f0fdf4', 200, 1),
    ('colors', 'c-success-border', '#bbf7d0', 'color', 'Success Border', 'Green border for success elements', NULL, '#bbf7d0', 201, 1);

-- Warning spectrum
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'c-warning-light', '#fffbeb', 'color', 'Warning Light', 'Light amber background for warnings', NULL, '#fffbeb', 210, 1),
    ('colors', 'c-warning-border', '#fde68a', 'color', 'Warning Border', 'Amber border for warning elements', NULL, '#fde68a', 211, 1);

-- Danger spectrum
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'c-danger', '#dc2626', 'color', 'Danger', 'Red color for errors and destructive actions', NULL, '#dc2626', 220, 1),
    ('colors', 'c-danger-light', '#fef2f2', 'color', 'Danger Light', 'Light red background', NULL, '#fef2f2', 221, 1),
    ('colors', 'c-danger-border', '#fecaca', 'color', 'Danger Border', 'Red border', NULL, '#fecaca', 222, 1);

-- Info spectrum
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'c-info', '#0284c7', 'color', 'Info', 'Info blue for informational elements', NULL, '#0284c7', 230, 1),
    ('colors', 'c-info-light', '#eff6ff', 'color', 'Info Light', 'Light blue background', NULL, '#eff6ff', 231, 1),
    ('colors', 'c-info-border', '#bae6fd', 'color', 'Info Border', 'Blue border', NULL, '#bae6fd', 232, 1);

-- Additional text shade
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'text-faint', '#94a3b8', 'color', 'Text Faint', 'Faintest text — timestamps, placeholders, tertiary info', NULL, '#94a3b8', 105, 1);

-- Code block colors
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'code-bg', '#0f172a', 'color', 'Code Background', 'Dark background for code blocks and terminals', NULL, '#0f172a', 300, 1),
    ('colors', 'code-text', '#e2e8f0', 'color', 'Code Text', 'Light text color for code blocks', NULL, '#e2e8f0', 301, 1);

-- Tag/badge system
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'tag-purple-bg', '#f5f3ff', 'color', 'Tag Purple BG', 'Purple tag background', NULL, '#f5f3ff', 310, 1),
    ('colors', 'tag-purple-text', '#7c3aed', 'color', 'Tag Purple Text', 'Purple tag text', NULL, '#7c3aed', 311, 1),
    ('colors', 'tag-purple-border', '#ddd6fe', 'color', 'Tag Purple Border', 'Purple tag border', NULL, '#ddd6fe', 312, 1);

-- Secondary spectrum additions
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'c-secondary-border', '#bfdbfe', 'color', 'Secondary Border', 'Blue border for secondary elements', NULL, '#bfdbfe', 152, 1);

-- Shadows
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'shadow-sm', '0 1px 2px rgba(0,0,0,0.05)', 'text', 'Shadow Small', 'Subtle shadow for cards and elements', NULL, '0 1px 2px rgba(0,0,0,0.05)', 400, 1),
    ('colors', 'shadow-md', '0 4px 6px rgba(0,0,0,0.07)', 'text', 'Shadow Medium', 'Medium shadow for elevated elements', NULL, '0 4px 6px rgba(0,0,0,0.07)', 401, 1),
    ('colors', 'shadow-lg', '0 10px 15px rgba(0,0,0,0.1)', 'text', 'Shadow Large', 'Large shadow for modals and dropdowns', NULL, '0 10px 15px rgba(0,0,0,0.1)', 402, 1);

-- ── Clear caches ─────────────────────────────

DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';

DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

DELETE FROM `cache` WHERE `key` LIKE 'config_%';
