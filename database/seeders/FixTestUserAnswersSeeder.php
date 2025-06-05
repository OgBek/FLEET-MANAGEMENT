<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SecurityQuestion;
use App\Models\UserSecurityAnswer;
use Illuminate\Support\Facades\Hash;

class FixTestUserAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the test user
        $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            $this->command->error('Test user not found!');
            return;
        }
        
        // Clear existing security answers
        UserSecurityAnswer::where('user_id', $user->id)->delete();
        
        // Find the exact security questions by their text
        $question1 = SecurityQuestion::where('question', 'What was your childhood nickname?')->first();
        $question2 = SecurityQuestion::where('question', 'In what city or town was your first job?')->first();
        $question3 = SecurityQuestion::where('question', 'What is the name of your favorite childhood friend?')->first();
        
        if (!$question1 || !$question2 || !$question3) {
            $this->command->error('Not all security questions found!');
            return;
        }
        
        // Create security answers with exact question IDs
        $answers = [
            [
                'user_id' => $user->id,
                'security_question_id' => $question1->id,
                'answer' => 'Junior',
                'hashed_answer' => Hash::make('Junior')
            ],
            [
                'user_id' => $user->id,
                'security_question_id' => $question2->id,
                'answer' => 'Chicago',
                'hashed_answer' => Hash::make('Chicago')
            ],
            [
                'user_id' => $user->id,
                'security_question_id' => $question3->id,
                'answer' => 'Alex',
                'hashed_answer' => Hash::make('Alex')
            ]
        ];
        
        foreach ($answers as $answer) {
            UserSecurityAnswer::create($answer);
        }
        
        $this->command->info('Test user security answers have been fixed!');
        $this->command->info('Email: test@example.com');
        $this->command->info('Password: password123');
        $this->command->info('Security answers:');
        $this->command->info('- What was your childhood nickname? -> Junior');
        $this->command->info('- In what city or town was your first job? -> Chicago');
        $this->command->info('- What is the name of your favorite childhood friend? -> Alex');
    }
}
