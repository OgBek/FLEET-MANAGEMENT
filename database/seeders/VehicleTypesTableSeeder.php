<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $executive = DB::table('vehicle_categories')->where('name', 'Executive')->first()->id;
        $administrative = DB::table('vehicle_categories')->where('name', 'Administrative')->first()->id;
        $department = DB::table('vehicle_categories')->where('name', 'Department')->first()->id;
        $service = DB::table('vehicle_categories')->where('name', 'Service')->first()->id;
        $transport = DB::table('vehicle_categories')->where('name', 'Transport')->first()->id;

        DB::table('vehicle_types')->insert([
            [
                'name' => 'Luxury Sedan',
                'category_id' => $executive,
                'description' => 'High-end luxury sedan',
                'seating_capacity' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Executive SUV',
                'category_id' => $executive,
                'description' => 'Luxury SUV for executives',
                'seating_capacity' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sedan',
                'category_id' => $administrative,
                'description' => 'Standard sedan for administrative use',
                'seating_capacity' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Minivan',
                'category_id' => $department,
                'description' => 'Department minivan for group transport',
                'seating_capacity' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Utility Van',
                'category_id' => $service,
                'description' => 'Service utility van',
                'seating_capacity' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bus',
                'category_id' => $transport,
                'description' => 'Large capacity bus for mass transport',
                'seating_capacity' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mini Bus',
                'category_id' => $transport,
                'description' => 'Medium capacity bus',
                'seating_capacity' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
