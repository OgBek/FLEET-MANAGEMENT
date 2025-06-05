<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@fleet.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890'
        ])->assignRole('admin');

        // Create Department Head
        User::create([
            'name' => 'Department Head',
            'email' => 'head@fleet.com',
            'password' => Hash::make('password123'),
            'phone' => '2345678901',
            'department_id' => 1  // Assuming first department
        ])->assignRole('department_head');

        // Create Department Staff
        User::create([
            'name' => 'Department Staff',
            'email' => 'staff@fleet.com',
            'password' => Hash::make('password123'),
            'phone' => '3456789012',
            'department_id' => 1  // Assign to same department as head
        ])->assignRole('department_staff');

        // Create Driver
        User::create([
            'name' => 'Driver User',
            'email' => 'driver@fleet.com',
            'password' => Hash::make('password123'),
            'phone' => '4567890123',
            'license_number' => 'DL123456'
        ])->assignRole('driver');

        // Create Maintenance Staff
        User::create([
            'name' => 'Maintenance Staff',
            'email' => 'maintenance@fleet.com',
            'password' => Hash::make('password123'),
            'phone' => '5678901234',
            'specialization' => 'General Maintenance'
        ])->assignRole('maintenance_staff');
    }
} 