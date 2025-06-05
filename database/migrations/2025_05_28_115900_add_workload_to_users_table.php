<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('workload')->default(0)->after('status');
        });

        // Set initial workload for existing maintenance staff
        if (Schema::hasTable('model_has_roles')) {
            $maintenanceStaff = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.role_id', 5) // Assuming 5 is the ID for maintenance_staff role
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->select('users.id')
                ->get();

            foreach ($maintenanceStaff as $staff) {
                $count = DB::table('vehicle_reports')
                    ->where('assigned_to', $staff->id)
                    ->where('status', 'in_progress')
                    ->count();
                
                DB::table('users')
                    ->where('id', $staff->id)
                    ->update(['workload' => $count]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('workload');
        });
    }
};
