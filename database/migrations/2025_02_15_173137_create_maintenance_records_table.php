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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('maintenance_staff_id')->constrained('users');
            $table->date('service_date');
            $table->string('service_type');
            $table->text('description');
            $table->decimal('cost', 10, 2);
            $table->integer('odometer_reading');
            $table->string('parts_replaced')->nullable();
            $table->string('labor_hours')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed']);
            $table->date('next_service_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
