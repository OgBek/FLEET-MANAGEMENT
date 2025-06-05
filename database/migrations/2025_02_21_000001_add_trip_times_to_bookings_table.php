<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->datetime('actual_start_time')->nullable()->after('end_time');
            $table->datetime('actual_end_time')->nullable()->after('actual_start_time');
            $table->datetime('completed_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['actual_start_time', 'actual_end_time', 'completed_at']);
        });
    }
}; 