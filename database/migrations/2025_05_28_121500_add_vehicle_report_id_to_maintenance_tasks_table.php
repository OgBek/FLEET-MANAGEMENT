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
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_tasks', 'vehicle_report_id')) {
                $table->foreignId('vehicle_report_id')
                      ->nullable()
                      ->after('vehicle_id')
                      ->constrained('vehicle_reports')
                      ->onDelete('set null');
            }
            
            if (!Schema::hasColumn('maintenance_tasks', 'due_date')) {
                $table->dateTime('due_date')
                      ->nullable()
                      ->after('scheduled_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_tasks', 'vehicle_report_id')) {
                $table->dropForeign(['vehicle_report_id']);
                $table->dropColumn('vehicle_report_id');
            }
            
            if (Schema::hasColumn('maintenance_tasks', 'due_date')) {
                $table->dropColumn('due_date');
            }
        });
    }
};
