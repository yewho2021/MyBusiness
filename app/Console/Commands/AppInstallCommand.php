<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AppInstallCommand extends Command
{
    protected $signature = 'app:install
                            {--fresh : Drop all tables and reinstall}
                            {--skip-seed : Skip seeding initial data}';

    protected $description = 'Install the Admin Portal: import schema, seed data, clear caches';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║     Admin Portal Installer               ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('');

        // ── Pre-flight checks ─────────────────────────
        $this->info('Running pre-flight checks...');

        // Check .env exists
        if (!file_exists(base_path('.env'))) {
            $this->error('No .env file found. Copy .env.example to .env and configure it first.');
            return 1;
        }

        // Check APP_KEY
        if (empty(config('app.key'))) {
            $this->warn('APP_KEY is empty. Generating...');
            Artisan::call('key:generate', ['--force' => true]);
            $this->info('  APP_KEY generated.');
        }

        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info('  Database connection: OK (' . config('database.connections.mysql.database') . ')');
        } catch (\Exception $e) {
            $this->error('Cannot connect to database: ' . $e->getMessage());
            $this->error('Check DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env');
            return 1;
        }

        // Check PHP extensions
        $required = ['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'zip'];
        $missing = [];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        if (!empty($missing)) {
            $this->error('Missing PHP extensions: ' . implode(', ', $missing));
            return 1;
        }
        $this->info('  PHP extensions: OK');

        // ── Fresh install? ────────────────────────────
        if ($this->option('fresh')) {
            if (!$this->confirm('This will DROP ALL TABLES. Are you sure?', false)) {
                $this->info('Aborted.');
                return 0;
            }
            $this->warn('Dropping all tables...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $name = reset((array) $table);
                DB::statement("DROP TABLE IF EXISTS `{$name}`");
                $this->line("  Dropped: {$name}");
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // ── Import schema ─────────────────────────────
        $schemaPath = base_path('database/schema.sql');
        if (!file_exists($schemaPath)) {
            $this->error('database/schema.sql not found.');
            return 1;
        }

        $tables = DB::select('SHOW TABLES');
        $tableCount = count($tables);

        if ($tableCount > 3) {
            $this->info("Database already has {$tableCount} tables. Skipping schema import.");
            $this->info('  (Use --fresh to reinstall from scratch)');
        } else {
            $this->info('Importing database schema...');
            $sql = file_get_contents($schemaPath);
            // Split and execute
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            $created = 0;
            foreach ($statements as $stmt) {
                if (empty($stmt) || strpos($stmt, '--') === 0) continue;
                try {
                    DB::statement($stmt);
                    if (stripos($stmt, 'CREATE TABLE') !== false) {
                        $created++;
                    }
                } catch (\Exception $e) {
                    // Skip errors for SET statements etc.
                    if (stripos($stmt, 'CREATE TABLE') !== false) {
                        $this->warn("  Warning: {$e->getMessage()}");
                    }
                }
            }
            $this->info("  Created {$created} tables.");
        }

        // ── Seed data ─────────────────────────────────
        if (!$this->option('skip-seed')) {
            $this->info('Seeding initial data...');
            try {
                Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\InstallSeeder', '--force' => true]);
                $this->info('  Roles, admin user, menus, and config seeded.');
            } catch (\Exception $e) {
                $this->warn('  Seeder warning: ' . $e->getMessage());
                $this->info('  (This is normal if data already exists)');
            }
        }

        // ── Clear caches ──────────────────────────────
        $this->info('Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        $this->info('  All caches cleared.');

        // ── Storage link ──────────────────────────────
        if (!file_exists(public_path('storage'))) {
            $this->info('Creating storage symlink...');
            Artisan::call('storage:link');
            $this->info('  Storage linked.');
        }

        // ── Permissions ───────────────────────────────
        $storagePath = storage_path();
        $bootstrapCache = base_path('bootstrap/cache');
        if (is_writable($storagePath) && is_writable($bootstrapCache)) {
            $this->info('  Directory permissions: OK');
        } else {
            $this->warn('  Warning: storage/ or bootstrap/cache may not be writable.');
            $this->warn('  Run: chmod -R 775 storage bootstrap/cache');
        }

        // ── Done ──────────────────────────────────────
        $this->info('');
        $this->info('══════════════════════════════════════════');
        $this->info('  Installation complete!');
        $this->info('');
        $this->info('  URL:      ' . config('app.url') . '/login');
        $this->info('  Username: admin');
        $this->info('  Password: Admin@1234');
        $this->info('');
        $this->warn('  ⚠  Change the default password immediately!');
        $this->info('══════════════════════════════════════════');
        $this->info('');

        return 0;
    }
}
