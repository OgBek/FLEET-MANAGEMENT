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
            $table->text('resolution_notes')->nullable()->after('completed_at');
            $table->text('parts_used')->nullable()->after('resolution_notes');
            $table->decimal('labor_hours', 8, 2)->nullable()->after('parts_used');
            $table->decimal('total_cost', 10, 2)->nullable()->after('labor_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropColumn(['resolution_notes', 'parts_used', 'labor_hours', 'total_cost']);
        });
    }
};
