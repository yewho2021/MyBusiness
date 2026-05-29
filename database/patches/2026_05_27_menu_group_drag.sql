-- =============================================
-- Menu Management: Draggable Group Ordering
-- Created: 2026-05-27
-- =============================================

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '5.1.1', 'Menu Management: Draggable Group Ordering',
    'Improved Menu Management page — groups can now be reordered by drag-and-drop, just like menu items.\n\n**Added:**\n- Drag handle on each group header (grip icon on the left of the group title)\n- Groups can be dragged up/down to reorder sections in the sidebar\n- New API endpoint POST menus/groups/update-order saves group order via AJAX\n- Visual feedback (ghost, shadow) while dragging groups\n\n**Changed:**\n- MenuController: added updateGroupOrder() method\n- routes/admin.php: added admin.menus.groups.update-order route\n- menus/index.blade.php: group headers now have drag handles, SortableJS initialises on the group container\n\n**How it works:**\n- Dragging a group card saves the new sort_order for all groups automatically\n- Sidebar cache is cleared after each reorder so changes appear immediately',
    '{"type":"enhancement","files_changed":3,"routes_added":1}',
    NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
