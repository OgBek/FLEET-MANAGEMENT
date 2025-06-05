<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SecurityQuestion;
use App\Models\UserSecurityAnswer;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class SecurityQuestionsTest extends TestCase
{
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        
        // Seed security questions
        $this->artisan('db:seed', ['--class' => 'SecurityQuestionsSeeder']);
    }

    /** @test */
    public function user_can_setup_security_questions_after_registration()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Get security questions
        $questions = SecurityQuestion::where('active', true)->take(3)->get();
        
        // Setup data for security questions
        $data = [
            'questions' => [$questions[0]->id, $questions[1]->id, $questions[2]->id],
            'answers' => ['Answer 1', 'Answer 2', 'Answer 3']
        ];
        
        // Submit security questions setup
        $response = $this->actingAs($user)
            ->post(route('security-questions.store', ['userId' => $user->id]), $data);
        
        // Assert redirect to dashboard
        $response->assertRedirect(route('dashboard'));
        
        // Assert security answers were saved
        $this->assertDatabaseCount('user_security_answers', 3);
        $this->assertDatabaseHas('user_security_answers', [
            'user_id' => $user->id,
            'security_question_id' => $questions[0]->id
        ]);
    }
    
    /** @test */
    public function user_can_update_security_questions()
    {
        // Create a user with security questions
        $user = User::factory()->create();
        $questions = SecurityQuestion::where('active', true)->take(3)->get();
        
        // Create initial security answers
        foreach ($questions as $index => $question) {
            UserSecurityAnswer::create([
                'user_id' => $user->id,
                'security_question_id' => $question->id,
                'answer' => "Original Answer " . ($index + 1),
                'hashed_answer' => Hash::make("Original Answer " . ($index + 1))
            ]);
        }
        
        // Setup data for updating security questions
        $data = [
            'questions' => [$questions[0]->id, $questions[1]->id, $questions[2]->id],
            'answers' => ['Updated Answer 1', 'Updated Answer 2', 'Updated Answer 3']
        ];
        
        // Submit security questions update
        $response = $this->actingAs($user)
            ->post(route('security-questions.update.post'), $data);
        
        // Assert successful update
        $response->assertSessionHas('success');
        
        // Assert answers were updated (checking for answer field)
        $updatedAnswer = UserSecurityAnswer::where('user_id', $user->id)
            ->where('security_question_id', $questions[0]->id)
            ->first();
            
        $this->assertEquals('Updated Answer 1', $updatedAnswer->answer);
    }
    
    /** @test */
    public function user_can_reset_password_using_security_questions()
    {
        // Create a user with security questions
        $user = User::factory()->create(['email' => 'test@example.com']);
        $questions = SecurityQuestion::where('active', true)->take(3)->get();
        
        // Create security answers
        $answers = [];
        foreach ($questions as $index => $question) {
            $answer = "Test Answer " . ($index + 1);
            $answers[$question->id] = $answer;
            
            UserSecurityAnswer::create([
                'user_id' => $user->id,
                'security_question_id' => $question->id,
                'answer' => $answer,
                'hashed_answer' => Hash::make($answer)
            ]);
        }
        
        // Step 1: Find account by email
        $response = $this->post(route('password.security.email'), [
            'email' => 'test@example.com'
        ]);
        
        // Assert redirect to verify answers
        $response->assertSessionHas('userId', $user->id);
        
        // Step 2: Submit answers
        $response = $this->withSession(['userId' => $user->id])
            ->post(route('password.security.verify'), [
                'answers' => $answers
            ]);
        
        // Assert token generated for password reset
        $response->assertSessionHas('token');
        $token = session('token');
        
        // Step 3: Reset password
        $response = $this->withSession(['token' => $token, 'userId' => $user->id])
            ->post(route('password.security.update'), [
                'token' => $token,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);
        
        // Assert password was reset
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }
}
