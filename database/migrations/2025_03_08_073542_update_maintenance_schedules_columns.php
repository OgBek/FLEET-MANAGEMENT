<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMaintenanceSchedulesColumns extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            // First modify the columns to be nullable temporarily to avoid data loss
            $table->string('maintenance_type', 50)->nullable()->change();
            $table->string('status', 20)->nullable()->change();
            
            // Update existing data to ensure valid values
            DB::table('maintenance_schedules')->update([
                'maintenance_type' => 'routine_service',
                'status' => 'pending'
            ]);
            
            // Now make them required with proper length constraints
            $table->string('maintenance_type', 50)->nullable(false)->change();
            $table->string('status', 20)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->string('maintenance_type')->change();
            $table->string('status')->change();
        });
    }
}
