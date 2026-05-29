-- =============================================
-- Phase 3: Install & Documentation
-- Created: 2026-03-26
-- =============================================

-- Clear sidebar cache
DELETE FROM `cache` WHERE `key` LIKE '%sidebar_menu%';

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office',
    '2.6.0',
    'Phase 3: Install & Documentation',
    'Complete install and documentation package:\n\n1. .env.example — All required environment variables documented with descriptions and defaults\n\n2. INSTALL.md — Step-by-step guide covering requirements, cPanel setup, database import, seeder, first login, post-install checklist, updating, and troubleshooting\n\n3. database/schema.sql — Full CREATE TABLE schema (19 tables) for fresh install without needing migrations\n\n4. README.md — Project overview with features list, tech stack, project structure, conventions reference\n\n5. php artisan app:install — One-command installer with pre-flight checks (PHP extensions, DB connection, APP_KEY), schema import, seeder, cache clear, storage link, permission check\n\n6. Default admin password updated from Admin@123 to Admin@1234 for consistency across all documentation',
    '{"files_changed": [".env.example", "INSTALL.md", "README.md", "database/schema.sql", "app/Console/Commands/AppInstallCommand.php", "database/seeders/InstallSeeder.php"]}',
    NOW()
);
