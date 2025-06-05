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
        Schema::table('users', function (Blueprint $table) {
            // Ensure image_data exists
            if (!Schema::hasColumn('users', 'image_data')) {
                $table->longText('image_data')->nullable();
            }
        });

        // Copy data from profile_photo_path to image_data if exists
        if (Schema::hasColumn('users', 'profile_photo_path')) {
            DB::statement("UPDATE users SET image_data = CONCAT('data:image/jpeg;base64,', TO_BASE64(profile_photo_path)) WHERE profile_photo_path IS NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed as we're not removing any columns
    }
};
