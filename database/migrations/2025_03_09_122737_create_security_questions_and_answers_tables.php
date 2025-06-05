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
        // First create the security questions table
        Schema::create('security_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Then create the user security answers table with the foreign key
        Schema::create('user_security_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('security_question_id')->constrained('security_questions')->onDelete('cascade');
            $table->string('answer');
            $table->string('hashed_answer');
            $table->timestamps();
            
            // A user can only answer each question once
            $table->unique(['user_id', 'security_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to avoid foreign key constraints
        Schema::dropIfExists('user_security_answers');
        Schema::dropIfExists('security_questions');
    }
};
