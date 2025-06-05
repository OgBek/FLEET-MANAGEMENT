<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleBrandsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vehicle_brands')->insert([
            [
                'name' => 'Mercedes-Benz',
                'description' => 'Luxury German automobile manufacturer',
                'manufacturer' => 'Daimler AG',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BMW',
                'description' => 'Premium German automobile manufacturer',
                'manufacturer' => 'BMW Group',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Toyota',
                'description' => 'Japanese automobile manufacturer',
                'manufacturer' => 'Toyota Motor Corporation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Honda',
                'description' => 'Japanese automobile manufacturer',
                'manufacturer' => 'Honda Motor Co., Ltd.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ford',
                'description' => 'American automobile manufacturer',
                'manufacturer' => 'Ford Motor Company',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Volvo',
                'description' => 'Swedish automobile manufacturer',
                'manufacturer' => 'Volvo Group',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
