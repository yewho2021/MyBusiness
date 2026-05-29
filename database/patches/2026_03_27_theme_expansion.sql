-- =============================================
-- Theme Expansion: Full CSS Variable System
-- Created: 2026-03-27
-- =============================================

-- New text color config rows (colors group)
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('colors', 'text_heading', '#0f172a', 'color', 'Heading Text', 'Color for page titles and headings', NULL, '#0f172a', 30, 1),
    ('colors', 'text_primary', '#1e293b', 'color', 'Primary Text', 'Main text color for strong/emphasis text', NULL, '#1e293b', 31, 1),
    ('colors', 'text_body', '#334155', 'color', 'Body Text', 'Default body text and table cell text', NULL, '#334155', 32, 1),
    ('colors', 'text_secondary', '#475569', 'color', 'Secondary Text', 'Less prominent text', NULL, '#475569', 33, 1),
    ('colors', 'text_muted', '#64748b', 'color', 'Muted Text', 'Subdued labels, descriptions, timestamps', NULL, '#64748b', 34, 1),
    ('colors', 'text_faint', '#94a3b8', 'color', 'Faint Text', 'Very subtle text — icons, column headers', NULL, '#94a3b8', 35, 1),
    ('colors', 'text_placeholder', '#9ca3af', 'color', 'Placeholder Text', 'Input placeholder text color', NULL, '#9ca3af', 36, 1),
    ('colors', 'purple', '#7c3aed', 'color', 'Purple', 'Accent color for badges and special highlights', NULL, '#7c3aed', 37, 1),
    ('colors', 'purple_light', '#f3e8ff', 'color', 'Purple Light', 'Light purple background for badges', NULL, '#f3e8ff', 38, 1),
    ('colors', 'success_border', '#bbf7d0', 'color', 'Success Border', 'Border color for success alerts and badges', NULL, '#bbf7d0', 39, 1),
    ('colors', 'warning_border', '#fde68a', 'color', 'Warning Border', 'Border color for warning alerts and badges', NULL, '#fde68a', 40, 1),
    ('colors', 'danger_border', '#fecaca', 'color', 'Danger Border', 'Border color for danger alerts and badges', NULL, '#fecaca', 41, 1),
    ('colors', 'info_border', '#bae6fd', 'color', 'Info Border', 'Border color for info alerts and badges', NULL, '#bae6fd', 42, 1);

-- New layout/UI config rows
INSERT IGNORE INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('layout', 'input_border', '#d1d5db', 'color', 'Input Border', 'Default border color for form inputs and selects', NULL, '#d1d5db', 20, 1),
    ('layout', 'hover_border', '#cbd5e1', 'color', 'Hover Border', 'Border color on hover states', NULL, '#cbd5e1', 21, 1),
    ('layout', 'hover_bg', '#f8fafc', 'color', 'Hover Background', 'Background on row/item hover', NULL, '#f8fafc', 22, 1),
    ('layout', 'focus_ring', 'rgba(37,99,235,0.1)', 'text', 'Focus Ring', 'Box-shadow color for focused inputs', NULL, 'rgba(37,99,235,0.1)', 23, 1),
    ('layout', 'shadow_sm', '0 1px 3px rgba(0,0,0,0.08)', 'text', 'Small Shadow', 'Subtle shadow for cards', NULL, '0 1px 3px rgba(0,0,0,0.08)', 24, 1),
    ('layout', 'shadow_md', '0 4px 12px rgba(0,0,0,0.06)', 'text', 'Medium Shadow', 'Hover shadow for cards and stat items', NULL, '0 4px 12px rgba(0,0,0,0.06)', 25, 1),
    ('layout', 'modal_backdrop', 'rgba(15,23,42,0.6)', 'text', 'Modal Backdrop', 'Background overlay color for modals', NULL, 'rgba(15,23,42,0.6)', 26, 1);

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.8.0', 'Theme Expansion: Full CSS Variable System',
    'Added 20 new configurable theme variables: 7 text colors (heading, primary, body, secondary, muted, faint, placeholder), purple accent, 4 status border colors, input/hover border colors, hover background, focus ring, shadows, and modal backdrop. All CSS in components.css now uses only CSS variables — zero hardcoded hex values. Configuration page restyled to use CSS variables. Total configurable CSS properties: 65+.',
    '{"new_config_keys": 20, "total_css_vars": 65, "hardcoded_hex_in_components": 0}',
    NOW()
);
