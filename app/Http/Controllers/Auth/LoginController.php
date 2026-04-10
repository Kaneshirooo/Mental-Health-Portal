<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\SessionLog;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ip = $request->ip();

        // Brute force protection (check last 15 mins)
        $attempts = LoginAttempt::where('ip_address', $ip)
            ->where('attempt_time', '>=', Carbon::now()->subMinutes(15))
            ->count();

        if ($attempts >= 5) {
            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in 15 minutes.',
            ]);
        }

        // Domain restriction removed (any email allowed)
        $credentials['email'] = strtolower(trim($credentials['email']));

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Success - Generate OTP
            $otp = rand(100000, 999999);
            
            session(['temp_user' => [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'otp_code' => $otp,
                'otp_expiry' => Carbon::now()->addMinutes(10),
                'activity' => 'Standard login',
            ]]);

            try {
                $this->sendOtpEmail($user->email, $otp);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Login OTP Mail Error: " . $e->getMessage());
                return back()->withErrors(['email' => 'Failed to send verification email. Please check your mail configuration or try again later.']);
            }
            LoginAttempt::where('ip_address', $ip)->delete(); // Clear attempts

            return redirect()->route('verify.otp');
        }

        // Log failed attempt
        LoginAttempt::create([
            'ip_address' => $ip,
            'attempt_time' => Carbon::now(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        // Close the most recent open session
        SessionLog::where('user_id', Auth::id())
            ->whereNull('logout_time')
            ->latest('login_time')
            ->first()
            ?->update(['logout_time' => Carbon::now()]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed.']);
        }

        $googleEmail = strtolower(trim($googleUser->getEmail()));

        $googleEmail = strtolower(trim($googleUser->getEmail()));

        // Domain restriction removed

        $user = User::where('email', $googleEmail)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Your Google account (' . $googleEmail . ') is not registered in the system. Please contact your counselor or administrator.']);
        }

        // Success - Generate OTP for Google Login
        $otp = rand(100000, 999999);
        
        session(['temp_user' => [
            'user_id' => $user->user_id,
            'email' => $user->email,
            'otp_code' => $otp,
            'otp_expiry' => Carbon::now()->addMinutes(10),
            'activity' => 'Google OAuth login',
        ]]);

        try {
            $this->sendOtpEmail($user->email, $otp);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Google Login OTP Mail Error: " . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Failed to send verification email for Google account.']);
        }

        return redirect()->route('verify.otp');
    }

    protected function sendOtpEmail($email, $code)
    {
        $body = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                <h2 style='color: #0d9488; margin-bottom: 16px;'>Security Verification</h2>
                <p style='font-size: 16px; color: #475569;'>Hello,</p>
                <p style='font-size: 16px; color: #475569;'>Your one-time verification code for the Mental Health Portal is:</p>
                <div style='background: #f1f5f9; padding: 24px; text-align: center; border-radius: 8px; margin: 24px 0;'>
                    <span style='font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #0f172a;'>$code</span>
                </div>
                <p style='font-size: 14px; color: #64748b; margin-top: 24px;'>This code will expire in 10 minutes. If you didn't request this code, please ignore this email.</p>
                <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'>
                <p style='font-size: 12px; color: #94a3b8;'>© " . date('Y') . " PSU Mental Health Portal. All rights reserved.</p>
            </div>
        ";

        \Illuminate\Support\Facades\Mail::html($body, function ($message) use ($email) {
            $message->to($email)
                ->subject('Your Verification Code — Mental Health Portal');
        });
    }

    protected function isInstitutionalEmail(string $email): bool
    {
        return true; // Filter removed per user request
    }

    protected function redirectUserByRole($user)
    {
        return match ($user->user_type->value ?? $user->user_type) {
            'admin' => redirect()->route('admin.dashboard'),
            'counselor' => redirect()->route('counselor.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
