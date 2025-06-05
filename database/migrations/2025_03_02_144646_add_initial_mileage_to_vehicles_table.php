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
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'initial_mileage')) {
                $table->integer('initial_mileage')->default(0)->after('current_mileage');
            }
            if (!Schema::hasColumn('vehicles', 'maintenance_interval')) {
                $table->integer('maintenance_interval')->default(5000)->after('initial_mileage');
            }
            if (!Schema::hasColumn('vehicles', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained()->after('brand_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
            if (Schema::hasColumn('vehicles', 'maintenance_interval')) {
                $table->dropColumn('maintenance_interval');
            }
            if (Schema::hasColumn('vehicles', 'initial_mileage')) {
                $table->dropColumn('initial_mileage');
            }
        });
    }
};
