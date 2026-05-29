<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminResetPasswordCommand extends Command
{
    protected $signature = 'admin:reset-password
                            {username : Admin username or email}
                            {--password= : New password (prompted if not provided)}';

    protected $description = 'Reset an admin user\'s password';

    public function handle(): int
    {
        $identifier = $this->argument('username');

        $admin = Admin::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$admin) {
            $this->error("Admin not found: {$identifier}");
            return 1;
        }

        $password = $this->option('password') ?? $this->secret('New password');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return 1;
        }

        $admin->password = Hash::make($password);
        $admin->save();

        $this->info("Password reset for: {$admin->name} ({$admin->username})");
        return 0;
    }
}
