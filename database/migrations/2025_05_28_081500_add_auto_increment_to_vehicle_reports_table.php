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
        // Get the current table structure
        $table = 'vehicle_reports';
        
        // Check if the table exists
        if (!Schema::hasTable($table)) {
            return;
        }
        
        // Get the current column definition
        $columns = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = 'id'");
        
        if (empty($columns)) {
            return;
        }
        
        $column = $columns[0];
        
        // If the column is already auto_increment, we're done
        if (str_contains($column->Extra, 'auto_increment')) {
            return;
        }
        
        // Modify the id column to add AUTO_INCREMENT
        DB::statement("ALTER TABLE {$table} MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        
        // Make sure the column is the primary key
        DB::statement("ALTER TABLE {$table} DROP PRIMARY KEY, ADD PRIMARY KEY (id)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive operation, so we'll just leave it empty
        // as we can't reliably determine the original state
    }
};
