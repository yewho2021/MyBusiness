-- Test Patch v2
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.0.2-test', 'Test Patch v2',
    'Modifies app/Services/VersionTest.php. After rollback to v1, this file should revert and v2Feature() should disappear.',
    '{"type":"test"}',
    NOW()
);
