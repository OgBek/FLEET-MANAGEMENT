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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->foreignId('type_id')->constrained('vehicle_types');
            $table->foreignId('brand_id')->constrained('vehicle_brands');
            $table->string('model');
            $table->integer('year');
            $table->string('color');
            $table->string('vin_number')->unique();
            $table->string('engine_number')->unique();
            $table->string('fuel_type');
            $table->integer('current_mileage')->default(0);
            $table->enum('status', ['available', 'booked', 'maintenance', 'out_of_service']);
            $table->text('features')->nullable();
            $table->date('insurance_expiry');
            $table->date('last_maintenance_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
