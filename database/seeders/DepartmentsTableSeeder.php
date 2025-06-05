<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Department;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Administration',
                'description' => 'Main Administration Department',
                'priority_level' => 5,
                'contact_person' => 'Admin Contact',
                'contact_email' => 'admin@university.edu',
                'contact_phone' => '123-456-7890'
            ],
            [
                'name' => 'University Leadership',
                'description' => 'University Leadership and Management',
                'priority_level' => 5,
                'contact_person' => 'John Smith',
                'contact_email' => 'leadership@university.edu',
                'contact_phone' => '123-456-7891'
            ],
            [
                'name' => 'Computer Science',
                'description' => 'Computer Science Department',
                'priority_level' => 3,
                'contact_person' => 'Jane Doe',
                'contact_email' => 'cs@university.edu',
                'contact_phone' => '123-456-7892'
            ],
            [
                'name' => 'Facilities Management',
                'description' => 'Facilities and Maintenance Management',
                'priority_level' => 4,
                'contact_person' => 'David Brown',
                'contact_email' => 'facilities@university.edu',
                'contact_phone' => '123-456-7893'
            ]
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
