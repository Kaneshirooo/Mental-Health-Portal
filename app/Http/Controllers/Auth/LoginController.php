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
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', 'PSU Mental Health Portal'));

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f5;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#064e3b 0%,#059669 60%,#10b981 100%);padding:36px 40px;text-align:center;">
              <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.3px;">PSU Mental Health Portal</h1>
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.8);font-size:13px;">Secure Login Verification</p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px 40px 24px;">
              <p style="margin:0 0 16px;color:#374151;font-size:15px;line-height:1.6;">Hello,</p>
              <p style="margin:0 0 28px;color:#374151;font-size:15px;line-height:1.6;">You are receiving this email because a login was attempted on your account. Use the verification code below to complete your sign-in. <strong>Do not share this code with anyone.</strong></p>

              <!-- OTP Block -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center" style="background-color:#f0fdf4;border:2px dashed #10b981;border-radius:10px;padding:28px 20px;">
                    <p style="margin:0 0 8px;color:#065f46;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;">Your Verification Code</p>
                    <p style="margin:0;color:#064e3b;font-size:42px;font-weight:800;letter-spacing:14px;font-family:'Courier New',Courier,monospace;">$code</p>
                    <p style="margin:10px 0 0;color:#6b7280;font-size:12px;">Expires in <strong>10 minutes</strong></p>
                  </td>
                </tr>
              </table>

              <p style="margin:28px 0 0;color:#6b7280;font-size:13px;line-height:1.7;">
                If you did not attempt to log in, please ignore this email. Your account remains secure and no changes have been made.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:24px 40px;text-align:center;">
              <p style="margin:0;color:#9ca3af;font-size:12px;line-height:1.6;">
                This is an automated security message from $fromName.<br>
                Please do not reply to this email.<br><br>
                &copy; $year PSU Mental Health Portal. All rights reserved.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        $text = "PSU Mental Health Portal - Login Verification\n\n"
            . "Your one-time verification code is: $code\n\n"
            . "This code expires in 10 minutes.\n\n"
            . "If you did not attempt to log in, please ignore this email.\n\n"
            . "-- PSU Mental Health Portal";

        $password = config('mail.mailers.smtp.password', env('MAIL_PASSWORD'));
        $fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS'));

        // Render Free Tier blocks outbound SMTP (port 25, 465, 587).
        // If we detect a Resend API key, use their HTTP API (port 443) instead of Laravel's SMTP Mail facade.
        if (str_starts_with((string) $password, 're_')) {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $password,
                'Content-Type'  => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from'    => $fromName . ' <' . $fromAddress . '>',
                'to'      => [$email],
                'reply_to' => $fromAddress,
                'subject' => '[PSU Mental Health Portal] Your Login Verification Code',
                'html'    => $html,
                'text'    => $text,
                'tags'    => [
                    ['name' => 'category', 'value' => 'otp'],
                ],
            ]);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::error('Resend API HTTP Error: ' . $response->body());
            }
        } else {
            \Illuminate\Support\Facades\Mail::html($html, function ($message) use ($email, $fromName, $fromAddress, $text) {
                $message->to($email)
                    ->replyTo($fromAddress, $fromName)
                    ->subject('[PSU Mental Health Portal] Your Login Verification Code')
                    ->text($text);
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
