<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'description' => 'System Administrator with full access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'department_head',
                'description' => 'Department Head with booking and management capabilities',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'driver',
                'description' => 'Vehicle Driver',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'maintenance_staff',
                'description' => 'Maintenance Staff for vehicle servicing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'department_staff',
                'description' => 'Regular Department Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
