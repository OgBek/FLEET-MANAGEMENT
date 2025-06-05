<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vehicle_categories')->insert([
            [
                'name' => 'Executive',
                'description' => 'Luxury vehicles for university leadership',
                'priority_level' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administrative',
                'description' => 'Vehicles for administrative staff and department heads',
                'priority_level' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Department',
                'description' => 'Vehicles for department use',
                'priority_level' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Service',
                'description' => 'Vehicles for maintenance and service',
                'priority_level' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transport',
                'description' => 'Mass transport vehicles',
                'priority_level' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
