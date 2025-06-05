<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SecurityQuestion;
use App\Models\UserSecurityAnswer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SecurityQuestionController extends Controller
{
    /**
     * Show the form for selecting security questions during registration.
     *
     * @param  int  $userId
     * @return \Illuminate\View\View
     */
    public function showSetupForm($userId)
    {
        $user = User::findOrFail($userId);
        $securityQuestions = SecurityQuestion::where('active', true)->get();
        
        return view('auth.security-questions', [
            'user' => $user,
            'securityQuestions' => $securityQuestions
        ]);
    }
    
    /**
     * Store the security questions and answers for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $validator = Validator::make($request->all(), [
            'questions' => ['required', 'array', 'min:3'],
            'questions.*' => ['required', 'exists:security_questions,id', 'distinct'],
            'answers' => ['required', 'array', 'min:3'],
            'answers.*' => ['required', 'string', 'min:2', 'max:100']
        ], [
            'questions.min' => 'You must select at least 3 security questions.',
            'questions.*.distinct' => 'Each security question must be unique.',
            'answers.*.min' => 'Security answers must be at least 2 characters long.',
            'answers.*.max' => 'Security answers must not exceed 100 characters.'
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Save the user's security questions and answers
        foreach ($request->questions as $index => $questionId) {
            $answer = $request->answers[$index];
            
            // Create the security answer with correct column name (hashed_answer instead of answer_hash)
            UserSecurityAnswer::create([
                'user_id' => $user->id,
                'security_question_id' => $questionId,
                'answer' => $answer,
                'hashed_answer' => Hash::make($answer) // Using the correct column name
            ]);
        }
        
        return redirect()->route('login')
            ->with('success', 'Security questions set successfully. You can now log in.');
    }
    
    /**
     * Show the form to update security questions for logged-in user.
     *
     * @return \Illuminate\View\View
     */
    public function showUpdateForm()
    {
        $user = Auth::user();
        $securityQuestions = SecurityQuestion::where('active', true)->get();
        $userAnswers = $user->securityAnswers()->with('securityQuestion')->get();
        
        // Get the base layout based on user role
        $layout = 'layouts.dashboard';
        
        return view('auth.security-questions-update', [
            'user' => $user,
            'securityQuestions' => $securityQuestions,
            'userAnswers' => $userAnswers,
            'layout' => $layout
        ]);
    }
    
    /**
     * Update the security questions and answers for logged-in user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'questions' => ['required', 'array', 'min:3'],
            'questions.*' => ['required', 'exists:security_questions,id', 'distinct'],
            'answers' => ['required', 'array', 'min:3'],
            'answers.*' => ['required', 'string', 'min:2', 'max:100']
        ], [
            'questions.min' => 'You must select at least 3 security questions.',
            'questions.*.distinct' => 'Each security question must be unique.',
            'answers.*.min' => 'Security answers must be at least 2 characters long.',
            'answers.*.max' => 'Security answers must not exceed 100 characters.'
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Delete existing answers
        $user->securityAnswers()->delete();
        
        // Save the new security questions and answers
        foreach ($request->questions as $index => $questionId) {
            $answer = $request->answers[$index];
            
            UserSecurityAnswer::create([
                'user_id' => $user->id,
                'security_question_id' => $questionId,
                'answer' => $answer,
                'hashed_answer' => Hash::make($answer)
            ]);
        }
        
        // Determine which profile route to redirect to based on user role
        if ($user->hasRole('admin')) {
            $redirectRoute = 'admin.profile.edit';
        } elseif ($user->hasRole('driver')) {
            $redirectRoute = 'driver.profile.edit';
        } elseif ($user->hasRole('maintenance_staff')) {
            $redirectRoute = 'maintenance.profile.edit';
        } else {
            // Default to client route for department_head and department_staff
            $redirectRoute = 'client.profile.edit';
        }
        
        return redirect()->route($redirectRoute)
            ->with('status', 'security-questions-updated');
    }
}
