<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // First ensure image_data exists
            if (!Schema::hasColumn('users', 'image_data')) {
                $table->longText('image_data')->nullable();
            }
            
            // Copy data from profile_photo_path to image_data if exists
            if (Schema::hasColumn('users', 'profile_photo_path')) {
                DB::statement('UPDATE users SET image_data = profile_photo_path WHERE profile_photo_path IS NOT NULL');
                $table->dropColumn('profile_photo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable();
            }
            // Note: Data recovery in down() is not implemented as it would be lossy
        });
    }
}; 