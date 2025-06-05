<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SecurityQuestion;
use App\Models\UserSecurityAnswer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AllUsersSecurityAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active security questions
        $securityQuestions = SecurityQuestion::where('active', true)->take(3)->get();
        
        if ($securityQuestions->count() < 3) {
            $this->command->error('Not enough security questions available. Need at least 3 questions.');
            return;
        }
        
        // Get all users
        $users = User::all();
        
        if ($users->count() === 0) {
            $this->command->error('No users found in the database.');
            return;
        }
        
        $this->command->info('Starting to create security answers for ' . $users->count() . ' users...');
        
        // Standard answers for all users
        $standardAnswers = [
            'Childhood' => 'Junior',      // Question about childhood nickname
            'First Job' => 'Chicago',     // Question about first job location
            'Friend' => 'Alex'            // Question about childhood friend
        ];
        
        // Sample answers for security questions
        $answers = [
            'What was your childhood nickname?' => 'Junior',
            'In what city or town was your first job?' => 'Chicago',
            'What is the name of your favorite childhood friend?' => 'Alex',
            'What is your mother\'s maiden name?' => 'Smith',
            'What was the make of your first car?' => 'Toyota',
            'What was your favorite food as a child?' => 'Pizza',
            'What is the name of the hospital where you were born?' => 'Central',
            'What was the name of your first pet?' => 'Buddy',
            'What is the middle name of your oldest child?' => 'James',
            'What street did you grow up on?' => 'Oak',
            'What was the name of your elementary school?' => 'Lincoln',
            'What was your favorite subject in high school?' => 'Math'
        ];
        
        $count = 0;
        
        foreach ($users as $user) {
            // Clear existing security answers for this user
            DB::table('user_security_answers')->where('user_id', $user->id)->delete();
            
            // Create 3 security answers for each user
            for ($i = 0; $i < 3; $i++) {
                $question = $securityQuestions[$i];
                $answer = $answers[$question->question] ?? "Answer" . ($i + 1);
                
                DB::table('user_security_answers')->insert([
                    'user_id' => $user->id,
                    'security_question_id' => $question->id,
                    'answer' => $answer,
                    'hashed_answer' => Hash::make($answer),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            $count++;
        }
        
        $this->command->info("Successfully created security answers for $count users.");
        $this->command->info("For all users, use these answers when resetting passwords:");
        $this->command->info("1. First question: " . $answers[$securityQuestions[0]->question] ?? 'Junior');
        $this->command->info("2. Second question: " . $answers[$securityQuestions[1]->question] ?? 'Chicago');
        $this->command->info("3. Third question: " . $answers[$securityQuestions[2]->question] ?? 'Alex');
    }
}
