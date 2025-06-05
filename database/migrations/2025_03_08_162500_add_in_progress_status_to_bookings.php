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
        // First, modify the ENUM type to include 'in_progress'
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled', 'in_progress')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'in_progress' from the ENUM
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled')");
    }
};
