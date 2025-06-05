<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMaintenanceSchedulesEnumFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE maintenance_schedules MODIFY COLUMN maintenance_type ENUM('routine_service', 'inspection', 'oil_change', 'tire_rotation', 'brake_inspection', 'major_service', 'other') NOT NULL DEFAULT 'routine_service'");
        DB::statement("ALTER TABLE maintenance_schedules MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled', 'overdue') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE maintenance_schedules MODIFY COLUMN maintenance_type VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE maintenance_schedules MODIFY COLUMN status VARCHAR(20) NOT NULL");
    }
}
