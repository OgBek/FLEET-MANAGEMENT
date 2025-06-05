<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AssignAdminRole extends Command
{
    protected $signature = 'role:assign-admin';
    protected $description = 'Assign admin role to the admin user';

    public function handle()
    {
        $user = User::where('email', 'admin@fleet.com')->first();
        
        if (!$user) {
            $this->error('Admin user not found!');
            return 1;
        }

        $user->assignRole('admin');
        $this->info('Admin role assigned successfully!');
        return 0;
    }
}
