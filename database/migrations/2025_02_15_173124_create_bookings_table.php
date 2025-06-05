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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('driver_id')->nullable()->constrained('users');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('purpose');
            $table->text('destination');
            $table->integer('estimated_distance')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled']);
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->integer('actual_distance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
