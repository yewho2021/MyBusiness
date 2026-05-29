<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class InstallSeeder extends Seeder
{
    /**
     * Fresh install: creates default admin, roles, menus, config, and changelog.
     * Safe to run on an existing database — skips if data already exists.
     *
     * Usage: php artisan db:seed --class=InstallSeeder
     */
    public function run(): void
    {
        // ── Pre-flight: check tables exist ─────────────
        $required = ['tbl_admin_roles', 'tbl_admin', 'tbl_admin_menu_groups', 'tbl_admin_menus', 'tbl_configuration', 'tbl_changelog'];
        foreach ($required as $table) {
            if (!Schema::hasTable($table)) {
                $this->command->error("Table '{$table}' does not exist. Run migrations or import the schema SQL first.");
                return;
            }
        }

        // ── Roles ──────────────────────────────────────
        if (DB::table('tbl_admin_roles')->count() === 0) {
            DB::table('tbl_admin_roles')->insert([
                ['id' => 1, 'name' => 'Administrator', 'slug' => 'administrator', 'description' => 'Full system access', 'level' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Supervisor',    'slug' => 'supervisor',    'description' => 'Supervisory access', 'level' => 2, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Staff',         'slug' => 'staff',         'description' => 'Basic staff access',  'level' => 3, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->command->info('Roles seeded: Administrator, Supervisor, Staff');
        }

        // ── Default Admin ──────────────────────────────
        if (DB::table('tbl_admin')->count() === 0) {
            DB::table('tbl_admin')->insert([
                'name'      => 'Administrator',
                'email'     => 'admin@admin.com',
                'username'  => 'admin',
                'password'  => Hash::make('Admin@1234'),
                'role_id'   => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Default admin created: admin / Admin@1234');
            $this->command->warn('CHANGE THE DEFAULT PASSWORD IMMEDIATELY!');
        }

        // ── Menu Groups ────────────────────────────────
        if (DB::table('tbl_admin_menu_groups')->count() === 0) {
            DB::table('tbl_admin_menu_groups')->insert([
                ['id' => 1, 'title' => 'MAIN MENU',     'slug' => 'main-menu',      'sort_order' => 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'title' => 'USER & ACCESS',  'slug' => 'access-control', 'sort_order' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'title' => 'SYSTEM TOOLS',   'slug' => 'data-tools',     'sort_order' => 2, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->command->info('Menu groups seeded.');
        }

        // ── Menu Items ─────────────────────────────────
        if (DB::table('tbl_admin_menus')->count() === 0) {
            $menus = [
                ['group_id' => 1, 'title' => 'Dashboard',       'icon' => 'fas fa-home',         'route_name' => 'admin.dashboard',               'permission_key' => 'dashboard',      'sort_order' => 0],
                ['group_id' => 2, 'title' => 'Admin Users',     'icon' => 'fas fa-users',        'route_name' => 'admin.users.index',             'permission_key' => 'users',          'sort_order' => 1],
                ['group_id' => 2, 'title' => 'Roles',           'icon' => 'fas fa-user-tag',     'route_name' => 'admin.roles.index',             'permission_key' => 'roles',          'sort_order' => 2],
                ['group_id' => 2, 'title' => 'Permissions',     'icon' => 'fas fa-shield-alt',   'route_name' => 'admin.permissions.index',       'permission_key' => 'permissions',    'sort_order' => 3],
                ['group_id' => 2, 'title' => 'Menu Management', 'icon' => 'fas fa-bars',         'route_name' => 'admin.menus.index',             'permission_key' => 'menus',          'sort_order' => 4],
                ['group_id' => 2, 'title' => 'Access Log',      'icon' => 'fas fa-user-shield',  'route_name' => 'admin.admin-log.index',         'permission_key' => 'admin_log',      'sort_order' => 5],
                ['group_id' => 2, 'title' => 'Activity Log',    'icon' => 'fas fa-shoe-prints',  'route_name' => 'admin.activity-log.index',      'permission_key' => 'activity_log',   'sort_order' => 6],
                ['group_id' => 4, 'title' => 'Configuration',   'icon' => 'fas fa-cogs',         'route_name' => 'admin.settings.configuration',  'permission_key' => 'configuration',  'sort_order' => 7],
                ['group_id' => 4, 'title' => 'Database',        'icon' => 'fas fa-server',       'route_name' => 'admin.database.connections.index', 'permission_key' => 'database',   'sort_order' => 8],
                ['group_id' => 4, 'title' => 'File Manager',    'icon' => 'fas fa-folder-open',  'route_name' => 'admin.filemanager.index',       'permission_key' => 'filemanager',    'sort_order' => 9],
                ['group_id' => 4, 'title' => 'File Structure',  'icon' => 'fas fa-sitemap',      'route_name' => 'admin.file-structure.index',    'permission_key' => 'file-structure', 'sort_order' => 10],
                ['group_id' => 4, 'title' => 'System Upgrade',  'icon' => 'fas fa-wrench',       'route_name' => 'admin.system-patch.index',      'permission_key' => 'system_patch',   'sort_order' => 11],
                ['group_id' => 4, 'title' => 'Chart Samples',   'icon' => 'fas fa-chart-pie',    'route_name' => 'admin.charts.index',            'permission_key' => 'chart_samples',  'sort_order' => 12],
                ['group_id' => 4, 'title' => 'Backup',          'icon' => 'fas fa-database',     'route_name' => 'admin.backup.index',            'permission_key' => 'backup',         'sort_order' => 13],
                ['group_id' => 4, 'title' => 'Changelog',       'icon' => 'fas fa-history',      'route_name' => 'admin.changelog.index',         'permission_key' => 'changelog',      'sort_order' => 14],
            ];

            foreach ($menus as $menu) {
                DB::table('tbl_admin_menus')->insert(array_merge($menu, [
                    'parent_id' => null, 'level' => 1, 'url' => null, 'is_active' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]));
            }
            $this->command->info('Menu items seeded: ' . count($menus) . ' entries.');
        }

        // ── Configuration (core groups only) ───────────
        if (DB::table('tbl_configuration')->count() === 0) {
            $this->seedConfiguration();
            $this->command->info('Configuration seeded: brand, colors, sidebar, header, typography, layout, login, advanced.');
        }

        // ── Initial Changelog ──────────────────────────
        if (DB::table('tbl_changelog')->count() === 0) {
            DB::table('tbl_changelog')->insert([
                'app_type'   => 'office',
                'version'    => '1.0.0',
                'title'      => 'Initial Setup',
                'details'    => 'Fresh installation of the Admin Portal base template.',
                'technical_info' => '{"type": "install"}',
                'created_at' => now(),
            ]);
            $this->command->info('Changelog: Initial entry created.');
        }

        $this->command->newLine();
        $this->command->info('Installation complete! Login at /login with: admin / Admin@1234');
    }

    protected function seedConfiguration(): void
    {
        $configs = [
            // Brand
            ['brand', 'portal_name',    config('app.name', 'Admin Portal'), 'text',     'Portal Name',    'Displayed in sidebar, login page, browser title, and footer', null, 'Admin Portal', 1],
            ['brand', 'portal_tagline', 'Sign in to your account',          'text',     'Login Tagline',  'Subtitle shown on the login page', null, 'Sign in to your account', 2],
            ['brand', 'logo_type',      'icon',                             'select',   'Logo Type',      'How the logo is displayed', '["icon","image","both"]', 'icon', 3],
            ['brand', 'logo_icon',      'fas fa-shield-alt',                'text',     'Logo Icon',      'FontAwesome icon class', null, 'fas fa-shield-alt', 4],
            ['brand', 'logo_image',     null,                               'image',    'Logo Image',     'Upload a logo image', null, null, 5],
            ['brand', 'favicon',        null,                               'image',    'Favicon',        'Upload a favicon', null, null, 6],
            ['brand', 'footer_text',    '© {year} {portal_name}. All rights reserved.', 'text', 'Footer Text', 'Supports placeholders: {year}, {portal_name}', null, '© {year} {portal_name}. All rights reserved.', 7],
            ['brand', 'footer_version', '1.0.0',                            'text',     'Footer Version', 'Version number displayed in footer', null, '1.0.0', 8],
            ['brand', 'meta_description', 'Admin Portal',                   'textarea', 'Meta Description', 'HTML meta description', null, 'Admin Portal', 9],

            // Colors
            ['colors', 'primary',         '#dc2626', 'color', 'Primary Color',    'Main brand color', null, '#dc2626', 1],
            ['colors', 'primary_hover',   '#b91c1c', 'color', 'Primary Hover',    'Button hover state', null, '#b91c1c', 2],
            ['colors', 'primary_light',   '#fef2f2', 'color', 'Primary Light',    'Light tint', null, '#fef2f2', 3],
            ['colors', 'secondary',       '#2563eb', 'color', 'Secondary Color',  'Links, focus rings', null, '#2563eb', 4],
            ['colors', 'secondary_hover', '#1d4ed8', 'color', 'Secondary Hover',  'Link hover', null, '#1d4ed8', 5],
            ['colors', 'secondary_light', '#eff6ff', 'color', 'Secondary Light',  'Selected rows', null, '#eff6ff', 6],
            ['colors', 'success',         '#16a34a', 'color', 'Success',          'Success badges', null, '#16a34a', 7],
            ['colors', 'success_light',   '#f0fdf4', 'color', 'Success Light',    'Success alert bg', null, '#f0fdf4', 8],
            ['colors', 'warning',         '#d97706', 'color', 'Warning',          'Warning badges', null, '#d97706', 9],
            ['colors', 'warning_light',   '#fffbeb', 'color', 'Warning Light',    'Warning alert bg', null, '#fffbeb', 10],
            ['colors', 'danger',          '#dc2626', 'color', 'Danger',           'Delete buttons, errors', null, '#dc2626', 11],
            ['colors', 'danger_light',    '#fef2f2', 'color', 'Danger Light',     'Error alert bg', null, '#fef2f2', 12],
            ['colors', 'info',            '#0ea5e9', 'color', 'Info',             'Info badges', null, '#0ea5e9', 13],
            ['colors', 'info_light',      '#f0f9ff', 'color', 'Info Light',       'Info alert bg', null, '#f0f9ff', 14],

            // Sidebar
            ['sidebar', 'sidebar_bg',           '#111111',                    'color', 'Background',       'Sidebar background', null, '#111111', 1],
            ['sidebar', 'sidebar_text',         '#d1d5db',                    'color', 'Text Color',       'Menu item text', null, '#d1d5db', 2],
            ['sidebar', 'sidebar_text_muted',   '#6b7280',                    'color', 'Muted Text',       'Group titles', null, '#6b7280', 3],
            ['sidebar', 'sidebar_hover_bg',     'rgba(220,38,38,0.1)',        'text',  'Hover Background', 'Menu hover bg', null, 'rgba(220,38,38,0.1)', 4],
            ['sidebar', 'sidebar_active_bg',    '#dc2626',                    'color', 'Active Background','Selected menu bg', null, '#dc2626', 5],
            ['sidebar', 'sidebar_active_text',  '#ffffff',                    'color', 'Active Text',      'Selected menu text', null, '#ffffff', 6],
            ['sidebar', 'sidebar_width',        '260',                        'number','Width (px)',        'Sidebar width', null, '260', 7],
            ['sidebar', 'sidebar_logo_bg',      '#dc2626',                    'color', 'Logo Background',  'Logo icon bg', null, '#dc2626', 8],
            ['sidebar', 'sidebar_border_color', 'rgba(255,255,255,0.08)',     'text',  'Border Color',     'Separator borders', null, 'rgba(255,255,255,0.08)', 9],

            // Header
            ['header', 'header_bg',        '#ffffff', 'color', 'Background',   'Header bg', null, '#ffffff', 1],
            ['header', 'header_text',      '#1e293b', 'color', 'Text Color',   'Page title color', null, '#1e293b', 2],
            ['header', 'header_height',    '60',      'number','Height (px)',   'Header height', null, '60', 3],
            ['header', 'header_border',    '#e2e8f0', 'color', 'Border Color', 'Bottom border', null, '#e2e8f0', 4],
            ['header', 'header_avatar_bg', '#dc2626', 'color', 'Avatar Background', 'User avatar bg', null, '#dc2626', 5],

            // Typography
            ['typography', 'font_family',    'Inter',          'select', 'Font Family',    'Main body font', '["Inter","Roboto","Poppins","Nunito","Open Sans","Lato","Source Sans 3","DM Sans","Plus Jakarta Sans","Outfit","Montserrat","Raleway","Quicksand","Manrope","Figtree"]', 'Inter', 1],
            ['typography', 'font_mono',      'JetBrains Mono', 'select', 'Mono Font',      'Code/monospace font', '["JetBrains Mono","Fira Code","Source Code Pro","IBM Plex Mono","Roboto Mono","Ubuntu Mono","Space Mono"]', 'JetBrains Mono', 2],
            ['typography', 'font_size_base', '14',             'number', 'Base Size (px)', 'Body text', null, '14', 3],
            ['typography', 'font_size_sm',   '13',             'number', 'Small (px)',      'Secondary text', null, '13', 4],
            ['typography', 'font_size_xs',   '12',             'number', 'XS (px)',         'Labels, badges', null, '12', 5],
            ['typography', 'font_size_lg',   '16',             'number', 'Large (px)',      'Emphasized text', null, '16', 6],
            ['typography', 'font_size_h1',   '24',             'number', 'H1 (px)',         'Page titles', null, '24', 7],
            ['typography', 'font_size_h2',   '20',             'number', 'H2 (px)',         'Section headers', null, '20', 8],
            ['typography', 'font_size_h3',   '16',             'number', 'H3 (px)',         'Card headers', null, '16', 9],

            // Layout
            ['layout', 'body_bg',         '#f1f5f9', 'color', 'Page Background',  'Main content bg', null, '#f1f5f9', 1],
            ['layout', 'card_bg',         '#ffffff', 'color', 'Card Background',  'Card bg', null, '#ffffff', 2],
            ['layout', 'card_radius',     '12',      'number','Card Radius (px)', 'Card corner radius', null, '12', 3],
            ['layout', 'card_border',     '#e2e8f0', 'color', 'Card Border',      'Card border color', null, '#e2e8f0', 4],
            ['layout', 'button_radius',   '8',       'number','Button Radius',    'Button corner radius', null, '8', 5],
            ['layout', 'input_radius',    '8',       'number','Input Radius',     'Form input radius', null, '8', 6],
            ['layout', 'content_padding', '24',      'number','Content Padding',  'Main content padding', null, '24', 7],
            ['layout', 'table_header_bg', '#f8fafc', 'color', 'Table Header BG',  'Table header bg', null, '#f8fafc', 8],
            ['layout', 'border_color',    '#e2e8f0', 'color', 'Border Color',     'General borders', null, '#e2e8f0', 9],
            ['layout', 'border_light',    '#f1f5f9', 'color', 'Border Light',     'Subtle borders', null, '#f1f5f9', 10],

            // Login
            ['login', 'login_bg_type',          'color',   'select',  'Login BG Type',    'Background type', '["color","gradient","image"]', 'color', 1],
            ['login', 'login_bg_color',         '#dc2626', 'color',   'Login BG Color',   'Solid bg color', null, '#dc2626', 2],
            ['login', 'login_bg_gradient_end',  '#7f1d1d', 'color',   'Gradient End',     'Second gradient color', null, '#7f1d1d', 3],
            ['login', 'login_bg_image',         null,      'image',   'Login BG Image',   'Background image', null, null, 4],
            ['login', 'login_heading',          'Welcome Back', 'text', 'Login Heading',  'Login page heading', null, 'Welcome Back', 5],
            ['login', 'login_show_remember',    '1',       'boolean', 'Show Remember Me', 'Show remember me checkbox', null, '1', 6],
            ['login', 'login_show_version',     '1',       'boolean', 'Show Version',     'Show version on login page', null, '1', 7],

            // Login Access
            ['login_access', 'login_restriction_enabled', 'disabled', 'select',  'IP Restriction',    'Enable login IP restriction', '["disabled","enabled"]', 'disabled', 1],
            ['login_access', 'login_restriction_type',    'ipv4',     'select',  'Restriction Type',  'Type of restriction', '["ipv4","ipv6","ddns"]', 'ipv4', 2],
            ['login_access', 'login_restriction_value',   '',         'text',    'Allowed Value',     'IP address or hostname', null, '', 3],
            ['login_access', 'login_new_ip_notify',       'disabled', 'select',  'New IP Notification', 'Email admin on login from new IP', '["disabled","enabled"]', 'disabled', 4],

            // Advanced
            ['advanced', 'custom_css',             '',    'code',     'Custom CSS',          'Injected into every page', null, '', 1],
            ['advanced', 'custom_head_html',       '',    'code',     'Custom Head HTML',    'Injected into <head>', null, '', 2],
            ['advanced', 'session_lifetime_minutes','120', 'number',  'Session Lifetime',    'Minutes before session expires', null, '120', 3],
            ['advanced', 'date_format',            'Y-m-d','text',    'Date Format',         'PHP date format string', null, 'Y-m-d', 4],
            ['advanced', 'time_format',            'H:i',  'text',    'Time Format',         'PHP time format string', null, 'H:i', 5],
            ['advanced', 'default_timezone',       'UTC',  'text',    'Default Timezone',    'Fallback timezone for the portal', null, 'UTC', 6],
            ['advanced', 'items_per_page',         '20',   'number',  'Items Per Page',      'Default pagination count', null, '20', 7],
        ];

        foreach ($configs as $c) {
            DB::table('tbl_configuration')->insert([
                'group'         => $c[0],
                'key'           => $c[1],
                'value'         => $c[2],
                'type'          => $c[3],
                'label'         => $c[4],
                'description'   => $c[5],
                'options'       => $c[6],
                'default_value' => $c[7],
                'sort_order'    => $c[8],
                'is_active'     => 1,
            ]);
        }
    }
}
