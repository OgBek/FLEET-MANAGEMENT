<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the column to allow NULL temporarily
        DB::statement('ALTER TABLE service_requests MODIFY COLUMN status VARCHAR(255)');
        
        // Then update the ENUM values
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'approved', 'in_progress', 'completed', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, modify the column to allow NULL temporarily
        DB::statement('ALTER TABLE service_requests MODIFY COLUMN status VARCHAR(255)');
        
        // Then revert back to original ENUM values
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'rejected') DEFAULT 'pending'");
    }
};
