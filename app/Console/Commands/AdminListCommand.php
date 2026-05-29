<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

class AdminListCommand extends Command
{
    protected $signature = 'admin:list
                            {--role= : Filter by role slug}
                            {--inactive : Include inactive admins}';

    protected $description = 'List all admin users';

    public function handle(): int
    {
        $query = Admin::with('role');

        if (!$this->option('inactive')) {
            $query->where('is_active', 1);
        }

        if ($role = $this->option('role')) {
            $query->whereHas('role', fn($q) => $q->where('slug', $role));
        }

        $admins = $query->orderBy('id')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admins found.');
            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Username', 'Email', 'Role', 'Active', 'Last Login', 'Password Set'],
            $admins->map(fn($a) => [
                $a->id,
                $a->name,
                $a->username,
                $a->email,
                $a->role->name ?? '—',
                $a->is_active ? 'Yes' : 'No',
                $a->datetime_lastlogin?->format('Y-m-d H:i') ?? 'Never',
                $a->password_changed_at ? 'Yes' : 'No',
            ])
        );

        $this->info("Total: {$admins->count()} admin(s)");
        return 0;
    }
}
