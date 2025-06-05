<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserRole extends Command
{
    protected $signature = 'user:check-role {id?}';
    protected $description = 'Check user role relationship';

    public function handle()
    {
        $id = $this->argument('id') ?? 1;
        $user = User::find($id);

        if (!$user) {
            $this->error("User with ID {$id} not found!");
            return;
        }

        $this->info("User Details:");
        $this->table(
            ['ID', 'Name', 'Email'],
            [[
                $user->id,
                $user->name,
                $user->email
            ]]
        );

        // Get all roles assigned to the user
        $roles = $user->getRoleNames();
        
        if ($roles->count() > 0) {
            $this->info("\nAssigned Roles:");
            $this->table(
                ['Role Name'],
                $roles->map(fn($role) => [$role])->toArray()
            );
            
            // Get permissions for each role
            foreach ($roles as $roleName) {
                $role = $user->roles()->where('name', $roleName)->first();
                $permissions = $role->permissions->pluck('name');
                
                if ($permissions->count() > 0) {
                    $this->info("\nPermissions for role '{$roleName}':");
                    $this->table(
                        ['Permission Name'],
                        $permissions->map(fn($permission) => [$permission])->toArray()
                    );
                }
            }
        } else {
            $this->error("No roles assigned to this user!");
        }
    }
} 