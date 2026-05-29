-- =============================================
-- Dynamic Branding & Portable Paths
-- Created: 2026-03-25
-- Description: Remove all hardcoded brand names (SyncOffice, SYNCTECH,
--              office.synctech.com.my) from PHP, Blade, and config files.
--              All references now read from tbl_configuration.portal_name
--              or config('app.name') / config('app.url') so the project
--              is fully portable across domains and server paths.
-- =============================================

-- Changelog entry
INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '1.21.0',
    'Dynamic Branding & Domain Portability',
    'Removed all hardcoded brand names and domain references from the codebase.\n\nChanges:\n- SecurePathGenerator: hash salt now uses config(app.name) instead of hardcoded string\n- FileStructureController: AI export zip filename, manifest title, and Live URL now dynamic\n- PdfToolController & ExportController: fallback portal name uses config(app.name)\n- All PDF templates (layout, export, activity-log): portal name from Configuration::get(portal_name)\n- ActivityLogController: now passes portalName to PDF view\n- Image Tools: watermark placeholder reads from Configuration\n- System Patch guide title: reads from Configuration\n- AppServiceProvider & activitylog.php: generic comments\n\nNote: composer.json cache-dir must be manually updated (blocked by System Patch).',
    '{"files_changed": 11, "type": "portability", "manual_action": "Update composer.json config.cache-dir from absolute to relative path"}',
    NOW()
);
