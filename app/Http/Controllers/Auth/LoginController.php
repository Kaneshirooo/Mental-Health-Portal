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
use Illuminate\Support\Str;
use Carbon\Carbon;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\GoogleProvider;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userRole = strtolower((string) ($user->user_type->value ?? $user->user_type));
            if (in_array($userRole, ['counselor', 'admin'], true)) {
                \App\Models\EmergencyCall::cleanupStaleCalls(0);
            }
            return $this->redirectUserByRole($user);
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
            // Success - Generate OTP and store in session
            $otp = rand(100000, 999999);

            session(['temp_user' => [
                'user_id'    => $user->user_id,
                'email'      => $user->email,
                'otp_code'   => $otp,
                'otp_expiry' => Carbon::now()->addMinutes(10),
                'activity'   => 'Standard login',
            ]]);

            // Explicitly flush session to disk NOW before the terminating callback fires
            session()->save();

            LoginAttempt::where('ip_address', $ip)->delete();

            // Send OTP email AFTER the redirect response is delivered to the browser.
            // app()->terminating() fires after $response->send() + fastcgi_finish_request(),
            // so the user sees the verify page instantly with no SMTP delay.
            $emailToSend = $user->email;
            $otpToSend   = $otp;
            $controller  = $this;
            app()->terminating(function () use ($controller, $emailToSend, $otpToSend) {
                try {
                    $controller->sendOtpEmail($emailToSend, $otpToSend);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('OTP Mail Error: ' . $e->getMessage());
                }
            });

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
        if (!$this->hasGoogleOauthConfig()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in is not configured yet. Please contact administrator.']);
        }

        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl(route('auth.google.callback'))
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        if (!$this->hasGoogleOauthConfig()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in is not configured yet. Please contact administrator.']);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl(route('auth.google.callback'))
                ->user();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth callback failed: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }

        $googleEmail = strtolower(trim($googleUser->getEmail()));

        $user = User::where('email', $googleEmail)->first();

        if (!$user) {
            // Auto-registration for Google/Institutional Users
            $user = User::create([
                'full_name' => $googleUser->getName() ?? 'Google User',
                'email' => $googleEmail,
                'password' => Hash::make(Str::random(24)),
                'user_type' => 'student',
                'roll_number' => 'G-' . substr(md5($googleEmail . time()), 0, 8), // Unique roll number
                'email_verified_at' => now(),
            ]);
        }

        // Google already verified the user's identity — log in directly (no OTP needed)
        Auth::login($user, true);

        // Record session log
        SessionLog::create([
            'user_id'    => $user->user_id,
            'login_time' => Carbon::now(),
            'activity'   => 'Google OAuth login',
        ]);

        return $this->redirectUserByRole($user);
    }

    protected function sendOtpEmail($email, $code)
    {
        $year = date('Y');
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
                <p style='font-size: 12px; color: #94a3b8;'>© $year PSU Mental Health Portal. All rights reserved.</p>
            </div>
        ";

        $password = config('mail.mailers.smtp.password', env('MAIL_PASSWORD'));
        
        // Render Free Tier blocks outbound SMTP (port 25, 465, 587).
        // If we detect a Resend API key, use their HTTP API (port 443) instead of Laravel's SMTP Mail facade.
        if (str_starts_with((string) $password, 're_')) {
            $fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS'));
            $fromName = config('mail.from.name', env('MAIL_FROM_NAME', 'Mental Health Portal'));
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $password,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => $fromName . ' <' . $fromAddress . '>',
                'to' => [$email],
                'subject' => 'Your Verification Code — Mental Health Portal',
                'html' => $body,
            ]);
            
            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::error('Resend API HTTP Error: ' . $response->body());
            }
        } else {
            \Illuminate\Support\Facades\Mail::html($body, function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your Verification Code — Mental Health Portal');
            });
        }
    }

    protected function isInstitutionalEmail(string $email): bool
    {
        return true; // Filter removed per user request
    }

    protected function hasGoogleOauthConfig(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    protected function redirectUserByRole($user)
    {
        return redirect()->route($user->dashboardRoute());
    }
}
