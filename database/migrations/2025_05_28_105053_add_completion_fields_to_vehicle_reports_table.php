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
        Schema::table('vehicle_reports', function (Blueprint $table) {
            $table->timestamp('completion_date')->nullable()->after('due_date');
            $table->text('parts_used')->nullable()->after('completion_date');
            $table->decimal('labor_hours', 8, 2)->nullable()->after('parts_used');
            $table->decimal('total_cost', 10, 2)->nullable()->after('labor_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_reports', function (Blueprint $table) {
            $table->dropColumn([
                'completion_date',
                'parts_used',
                'labor_hours',
                'total_cost'
            ]);
        });
    }
};
