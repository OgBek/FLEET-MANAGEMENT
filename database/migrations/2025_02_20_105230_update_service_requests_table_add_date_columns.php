<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'scheduled_date')) {
                $table->datetime('scheduled_date')->nullable();
            }
            if (!Schema::hasColumn('service_requests', 'completed_at')) {
                $table->datetime('completed_at')->nullable();
            }
        });

        // Update existing records to have a default scheduled_date
        DB::table('service_requests')
            ->whereNull('scheduled_date')
            ->update(['scheduled_date' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['scheduled_date', 'completed_at']);
        });
    }
};
