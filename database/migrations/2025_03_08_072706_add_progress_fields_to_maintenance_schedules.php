<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgressFieldsToMaintenanceSchedules extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_schedules', 'started_at')) {
                $table->timestamp('started_at')->nullable();
            }
            if (!Schema::hasColumn('maintenance_schedules', 'completion_notes')) {
                $table->text('completion_notes')->nullable();
            }
            if (!Schema::hasColumn('maintenance_schedules', 'parts_used')) {
                $table->text('parts_used')->nullable();
            }
            if (!Schema::hasColumn('maintenance_schedules', 'labor_hours')) {
                $table->decimal('labor_hours', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('maintenance_schedules', 'total_cost')) {
                $table->decimal('total_cost', 10, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $columns = [
                'started_at',
                'completion_notes',
                'parts_used',
                'labor_hours',
                'total_cost'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('maintenance_schedules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
