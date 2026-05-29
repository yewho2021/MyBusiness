-- =============================================
-- PDF Suite Module (iLovePDF-style tools)
-- Created: 2026-03-24
-- Packages: setasign/fpdi, smalot/pdfparser, ghostscript
-- =============================================

-- Menu entry
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 4, NULL, 1, 'PDF Suite', 'fas fa-file-pdf', 'admin.pdf-suite.index', 'pdf_suite', 17, 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_admin_menus` WHERE `route_name` = 'admin.pdf-suite.index');

-- Role access
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.pdf-suite.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
SELECT
    'office', '1.20.0', 'PDF Suite — iLovePDF-Style Tools',
    'Complete PDF toolkit with 11 tools.\n- Merge PDF: combine multiple PDFs into one\n- Split PDF: extract specific pages\n- Rotate PDF: rotate pages 90/180/270 degrees\n- Page Numbers: add page numbers with position control\n- Watermark: add text watermark to all pages\n- JPG to PDF: convert images to PDF\n- PDF to JPG: convert PDF pages to images\n- Extract Text: pull text content from PDFs\n- Compress PDF: reduce file size\n- Protect PDF: add password encryption\n- Unlock PDF: remove password protection\n- Powered by setasign/fpdi, smalot/pdfparser, ghostscript',
    '{"packages":"setasign/fpdi@2.6, smalot/pdfparser@2.12, ghostscript","tools":11}', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_changelog` WHERE `version` = '1.20.0' AND `app_type` = 'office');
