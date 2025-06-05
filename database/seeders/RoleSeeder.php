<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            [
                'name' => 'admin',
                'guard_name' => 'web'
            ],
            [
                'name' => 'department_head',
                'guard_name' => 'web'
            ],
            [
                'name' => 'department_staff',
                'guard_name' => 'web'
            ],
            [
                'name' => 'driver',
                'guard_name' => 'web'
            ],
            [
                'name' => 'maintenance_staff',
                'guard_name' => 'web'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
