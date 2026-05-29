-- Test Patch v1
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '3.0.1-test', 'Test Patch v1',
    'Test patch to verify version system. Creates app/Services/VersionTest.php.',
    '{"type":"test"}',
    NOW()
);
