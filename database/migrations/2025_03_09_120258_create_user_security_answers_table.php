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
        Schema::create('user_security_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('security_question_id')->constrained()->onDelete('cascade');
            $table->string('answer');
            $table->string('answer_hash'); // Hashed answer for verification
            $table->timestamps();
            
            // Ensure each user can only have one answer per security question
            $table->unique(['user_id', 'security_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_security_answers');
    }
};
