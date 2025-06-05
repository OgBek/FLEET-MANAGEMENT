<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get role IDs
        $adminRole = DB::table('roles')->where('name', 'admin')->first()->id;
        $deptHeadRole = DB::table('roles')->where('name', 'department_head')->first()->id;
        $driverRole = DB::table('roles')->where('name', 'driver')->first()->id;
        $maintenanceRole = DB::table('roles')->where('name', 'maintenance_staff')->first()->id;
        $staffRole = DB::table('roles')->where('name', 'department_staff')->first()->id;

        // Get department IDs
        $leadershipDept = DB::table('departments')->where('name', 'University Leadership')->first()->id;
        $csDept = DB::table('departments')->where('name', 'Computer Science')->first()->id;
        $facilityDept = DB::table('departments')->where('name', 'Facilities Management')->first()->id;

        // Insert users one by one to avoid column count mismatch
        DB::table('users')->insert([
            'name' => 'System Admin',
            'email' => 'admin@university.edu',
            'password' => bcrypt('password'),
            'role_id' => $adminRole,
            'department_id' => null,
            'phone' => '123-456-7800',
            'license_number' => null,
            'specialization' => null,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'John Smith',
            'email' => 'president@university.edu',
            'password' => bcrypt('password'),
            'role_id' => $deptHeadRole,
            'department_id' => $leadershipDept,
            'phone' => '123-456-7890',
            'license_number' => null,
            'specialization' => null,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Jane Doe',
            'email' => 'cs.head@university.edu',
            'password' => bcrypt('password'),
            'role_id' => $deptHeadRole,
            'department_id' => $csDept,
            'phone' => '123-456-7891',
            'license_number' => null,
            'specialization' => null,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Mike Johnson',
            'email' => 'driver1@university.edu',
            'password' => bcrypt('password'),
            'role_id' => $driverRole,
            'department_id' => $facilityDept,
            'phone' => '123-456-7892',
            'license_number' => 'DL123456',
            'specialization' => null,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Sarah Wilson',
            'email' => 'driver2@university.edu',
            'password' => bcrypt('password'),
            'role_id' => $driverRole,
            'department_id' => $facilityDept,
            'phone' => '123-456-7893',
            'license_number' => 'DL123457',
            'specialization' => null,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Robert Brown',
            'email' => 'maintenance1@university.edu',
            'password' => bcrypt('password'),
            'role_id' => $maintenanceRole,
            'department_id' => $facilityDept,
            'phone' => '123-456-7894',
            'license_number' => null,
            'specialization' => 'General Maintenance',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Lisa Davis',
            'email' => 'staff1@university.edu',
            'password' => bcrypt('password'),
            'role_id' => $staffRole,
            'department_id' => $csDept,
            'phone' => '123-456-7895',
            'license_number' => null,
            'specialization' => null,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the admin role
        $adminRole = Role::where('name', 'admin')->first();
        
        // Get the Administration department
        $adminDepartment = Department::where('name', 'Administration')->first();

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'department_id' => $adminDepartment->id,
            'phone' => '123-456-7890',
        ]);
    }
}
