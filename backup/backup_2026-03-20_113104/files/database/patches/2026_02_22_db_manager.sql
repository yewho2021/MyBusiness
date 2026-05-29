INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES 
('office', '1.2.4', 'Database Manager: Refactoring, Memory Safe Export & UI Polish', 
'- **Dashboard Refresh**: Styled the main database table list overview with softer border-radiuses, improved spacing, refined typographic fonts, and added a subtle transition lifting effect when hovering over the main system stats cards. Replaced hard black pagination and search borders with slightly shadowed variants.
- **Unified Workspace Styling**: Polished the query workspace layout. Sidebar inputs and list items now display with gentler backgrounds with robust hover interactions. Tabs were refined with a strong upper colored border for active states, while the embedded generic SQL code editor text box was transformed into a professional dark-themed IDE-style interface using monospaced layout (Fira Code).
- **Table Detailing Expansion**: Softened the UI bounding around the metadata properties section with specific card-style containers. Standardized the table tabs component and pagination buttons.
- **Empty State Enhancement**: Tables without records now correctly display column headers along with an empty "No data" row.
- **Export Functionality Stability**: Overhauled `.sql` export logic within `DatabaseController.php`. Implemented chunked data fetching (`LIMIT...OFFSET`) and streaming using `fopen()/fwrite()`. This eradicates the massive in-memory variable construction, avoiding PHP out-of-memory errors for significantly large database tables.', 
'{"modified_files":["index.blade.php","query.blade.php","table.blade.php","DatabaseController.php"]}', 
'2026-02-22 14:15:00');
