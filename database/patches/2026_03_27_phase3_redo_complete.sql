-- =============================================
-- Phase 3 Redo: Complete View Migration
-- Created: 2026-03-27
-- =============================================

INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.4.2', 'Phase 3 Redo: Complete View Migration',
    'Migrated all views using safe individual approach. 7 simple views fully rewritten with sc-* Blade components (zero per-page CSS). 30 complex views improved with CSS variable replacements — hardcoded theme colors (borders, backgrounds, text, primary/secondary/success/warning) replaced with var(--name, fallback) references for consistent theming. 2 PDF templates unchanged (self-contained). Updated components.css with readonly input styling. Total: 39 views improved, ~1,000+ CSS var references added across all views.',
    '{"phase": "3-redo", "full_rewrites": 7, "css_var_improved": 30, "pdf_templates": 2, "total_var_refs": 1017}',
    NOW()
);
