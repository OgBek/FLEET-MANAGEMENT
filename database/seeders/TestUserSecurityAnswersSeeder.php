<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SecurityQuestion;
use App\Models\UserSecurityAnswer;
use Illuminate\Support\Facades\Hash;

class TestUserSecurityAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'phone' => '123-456-7890',
                'remember_token' => \Str::random(10),
            ]
        );

        // Get the first 3 security questions
        $questions = SecurityQuestion::where('active', true)->take(3)->get();

        // Delete any existing answers for this user
        UserSecurityAnswer::where('user_id', $user->id)->delete();

        // Create security answers for the test user
        $answers = [
            'Fluffy', // pet name
            'New York', // first job city
            'Pizza' // favorite food as a child
        ];

        foreach ($questions as $index => $question) {
            UserSecurityAnswer::create([
                'user_id' => $user->id,
                'security_question_id' => $question->id,
                'answer' => $answers[$index],
                'hashed_answer' => Hash::make($answers[$index])
            ]);
        }

        $this->command->info('Test user created with email: test@example.com and password: password123');
        $this->command->info('Security answers set up for the test user:');
        
        foreach ($questions as $index => $question) {
            $this->command->info("Q: {$question->question} - A: {$answers[$index]}");
        }
    }
}
