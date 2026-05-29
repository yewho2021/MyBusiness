-- =============================================
-- Database Connections UI Enhancement
-- Created: 2026-03-26
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.1.3',
    'Database Connections UI Enhancement',
    'Redesigned Database Connections page with polished interface:\n- SVG database cylinder icons for each connection card\n- Color-coded accent stripes: green (ENV), blue (active session), gray (saved), muted (disabled)\n- Structured info grid with labeled Host/Database/Username fields\n- Active session pulse animation with BROWSING badge\n- Active session banner showing current external DB\n- Unique Hosts stat counter\n- Improved empty state with icon and CTA button\n- Connection footer with status indicator and last browsed time\n- Better responsive grid layout\n- Flash message support for success/error alerts',
    '{"files_changed": ["resources/views/admin/pages/database/connections/index.blade.php"]}',
    NOW()
);
