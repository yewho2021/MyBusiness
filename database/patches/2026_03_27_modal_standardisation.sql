INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.7.0', 'Modal Standardisation: Locked Modals + Unified CSS',
    'All modals across the portal are now locked — only close button works. Overlay click and Escape key dismissal removed from all 11 modal pages. Modal CSS centralised in components.css with legacy class compatibility. Per-page modal CSS removed from 9 views.',
    '{"files_updated": 11, "overlay_click_removed": 3, "escape_handlers_removed": 5}',
    NOW()
);
