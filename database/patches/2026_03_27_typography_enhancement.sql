-- Typography Enhancement: 72 Google Fonts + Color Previews
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '2.9.0', 'Typography + Color Input Enhancement',
    'Expanded font library from 22 to 72 Google Fonts in 7 categories. Font CSS now preloaded via HTML link tags (not JS) so font previews render correctly in dropdown. All text fields containing rgba/hex color values now show a live color swatch preview. Shadow fields show a live shadow preview box.',
    '{"sans_serif": 50, "serif": 12, "monospace": 12, "total": 72, "font_preload": "blade_links", "color_text_fields": true}',
    NOW()
);
