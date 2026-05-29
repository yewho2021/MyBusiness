-- =============================================
-- System Patch: Auto-Reload on Success
-- Created: 2026-05-27
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '5.1.3', 'System Patch: Auto-Reload on Success',
    'Improved System Patch UX — page now auto-reloads 2 seconds after a successful patch apply or rollback.\n\n**Behaviour:**\n- **Success (0 errors):** Shows animated progress bar counting down from 2s, then auto-reloads. A Cancel button lets the user stop and stay on the page if they want to review logs first.\n- **Errors:** No auto-reload. Shows the manual Refresh button as before so the user can review what went wrong.\n\n**Applies to both:**\n- Patch apply (doApply → showRes)\n- Version rollback (execRB → showRes)\n\n**Changed:** resources/views/admin/pages/system/patch.blade.php — showRes() function rewritten with countdown timer, progress bar animation, and cancel handler.',
    '{"type":"enhancement","files_changed":1}',
    NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
