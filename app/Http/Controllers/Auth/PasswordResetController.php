<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SecurityQuestion;
use App\Models\UserSecurityAnswer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Display the form to request a password reset using security questions.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password-security');
    }

    /**
     * Find the user by email and show security questions for verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function findAccount(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'We cannot find a user with that email address.'
            ])->withInput();
        }

        // Check if the user has security questions set up
        $securityAnswers = $user->securityAnswers()->with('securityQuestion')->get();

        if ($securityAnswers->isEmpty()) {
            return redirect()->route('password.request')
                ->withErrors([
                    'email' => 'This account does not have security questions set up. Please use the standard password reset option or contact support.'
                ]);
        }

        // Store user email in session for the next step
        $request->session()->put('password_reset_email', $user->email);

        return view('auth.security-questions-verify', [
            'user' => $user,
            'securityAnswers' => $securityAnswers
        ]);
    }

    /**
     * Verify the security question answers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyAnswers(Request $request)
    {
        // Get email from session
        $email = $request->session()->get('password_reset_email');
        
        if (!$email) {
            return redirect()->route('password.security.request')
                ->withErrors(['email' => 'Your session has expired. Please start again.']);
        }
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('password.security.request')
                ->withErrors(['email' => 'User not found.']);
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*' => 'required|string|min:2'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
        
        // Get all user security answers
        $userAnswers = $user->securityAnswers()->get();
        
        // Verify answers
        $correctAnswers = 0;
        $requiredCorrect = 3; // Number of correctly answered questions required
        
        foreach ($request->answers as $questionId => $answer) {
            $userAnswer = $userAnswers->where('security_question_id', $questionId)->first();
            
            if ($userAnswer && Hash::check($answer, $userAnswer->hashed_answer)) {
                $correctAnswers++;
            }
        }
        
        if ($correctAnswers < $requiredCorrect) {
            return back()->withErrors([
                'security_questions' => 'Your answers do not match our records. Please try again or contact support.'
            ]);
        }
        
        // Generate password reset token and store in session
        $token = Str::random(60);
        $request->session()->put('password_reset_token', $token);
        $request->session()->put('password_reset_user_id', $user->id);
        
        return redirect()->route('password.security.reset', ['token' => $token]);
    }
    
    /**
     * Display the password reset view with the given token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token)
    {
        $sessionToken = $request->session()->get('password_reset_token');
        
        if (!$sessionToken || $sessionToken !== $token) {
            return redirect()->route('password.security.request')
                ->withErrors(['email' => 'Invalid or expired token. Please start again.']);
        }
        
        return view('auth.reset-password-security', ['token' => $token]);
    }
    
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);
        
        $sessionToken = $request->session()->get('password_reset_token');
        $userId = $request->session()->get('password_reset_user_id');
        
        if (!$sessionToken || $sessionToken !== $request->token || !$userId) {
            return redirect()->route('password.security.request')
                ->withErrors(['email' => 'Invalid or expired token. Please start again.']);
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('password.security.request')
                ->withErrors(['email' => 'User not found.']);
        }
        
        // Reset the password
        $user->password = Hash::make($request->password);
        $user->save();
        
        // Clear session data
        $request->session()->forget('password_reset_token');
        $request->session()->forget('password_reset_user_id');
        $request->session()->forget('password_reset_email');
        
        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully. You can now log in with your new password.');
    }
    
    /**
     * Display company contact information for alternative password reset methods.
     *
     * @return \Illuminate\View\View
     */
    public function showContactInfo()
    {
        return view('auth.contact-info');
    }
}
