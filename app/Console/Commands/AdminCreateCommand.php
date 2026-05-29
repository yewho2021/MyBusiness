<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminCreateCommand extends Command
{
    protected $signature = 'admin:create
                            {--name= : Admin name}
                            {--email= : Admin email}
                            {--username= : Admin username}
                            {--password= : Admin password}
                            {--role= : Role slug (default: administrator)}';

    protected $description = 'Create a new admin user';

    public function handle(): int
    {
        $name     = $this->option('name') ?? $this->ask('Name');
        $email    = $this->option('email') ?? $this->ask('Email');
        $username = $this->option('username') ?? $this->ask('Username');
        $password = $this->option('password') ?? $this->secret('Password');
        $roleSlug = $this->option('role') ?? 'administrator';

        if (Admin::where('email', $email)->exists()) {
            $this->error("Admin with email '{$email}' already exists.");
            return 1;
        }

        if (Admin::where('username', $username)->exists()) {
            $this->error("Admin with username '{$username}' already exists.");
            return 1;
        }

        $role = AdminRole::where('slug', $roleSlug)->first();
        if (!$role) {
            $this->error("Role '{$roleSlug}' not found. Available: " .
                AdminRole::pluck('slug')->join(', '));
            return 1;
        }

        $admin = Admin::create([
            'name'      => $name,
            'email'     => $email,
            'username'  => $username,
            'password'  => Hash::make($password),
            'role_id'   => $role->id,
            'is_active' => 1,
        ]);

        $this->info("Admin created successfully: {$admin->name} ({$admin->username}) — Role: {$role->name}");
        return 0;
    }
}
