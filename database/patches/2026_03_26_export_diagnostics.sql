-- =============================================
-- Export Diagnostics Enhancement + Cleanup
-- Created: 2026-03-26
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.1.2',
    'Export Diagnostics & Cleanup',
    'Enhanced file structure export with full diagnostic logging and exclusion cleanup:\n- Phase-level progress tracking (0-4) with per-step timing\n- Pre-flight checks: storage writable, ZipArchive extension, disk space\n- Detailed ZipArchive error codes mapped to human-readable messages\n- Per-file addFile() error tracking with counts\n- Skipped extension and directory file counts in log\n- F12 console.log at every step: network, status, headers, response parsing\n- Robust error handling: HTTP status check before JSON parse, HTML error page extraction\n- Download endpoint: validates file size, returns structured JSON errors for AJAX\n- Export log panel: taller (500px), shows phases, timing, footer with filename/duration\n- Excluded storage/debugbar/ (was adding ~3000 JSON files / 119MB to export)\n- Excluded junk root files: error_log, media_disk, tbl_media,',
    '{"files_changed": ["app/Http/Controllers/Admin/FileStructureController.php", "resources/views/admin/pages/system/file-structure.blade.php"], "new_excludes": ["storage/debugbar", "error_log", "media_disk,", "tbl_media,"]}',
    NOW()
);
