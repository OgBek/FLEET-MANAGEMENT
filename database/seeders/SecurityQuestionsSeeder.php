<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecurityQuestion;

class SecurityQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            'What was your childhood nickname?',
            'In what city or town was your first job?',
            'What is the name of your favorite childhood friend?',
            'What is your mother\'s maiden name?',
            'What was the make of your first car?',
            'What was your favorite food as a child?',
            'What is the name of the hospital where you were born?',
            'What was the name of your first pet?',
            'What is the middle name of your oldest child?',
            'What street did you grow up on?',
            'What was the name of your elementary school?',
            'What was your favorite subject in high school?'
        ];

        foreach ($questions as $question) {
            SecurityQuestion::create([
                'question' => $question,
                'active' => true
            ]);
        }
    }
}
