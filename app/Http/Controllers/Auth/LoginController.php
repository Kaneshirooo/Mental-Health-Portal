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

            // Send OTP email synchronously before redirecting.
            // The Resend HTTP API call is fast (< 300ms) so this does not
            // noticeably delay the redirect for the user.
            try {
                $this->sendOtpEmail($user->email, $otp);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('OTP Mail Error: ' . $e->getMessage());
            }

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
        $clientId = trim(config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID'));
        $clientSecret = trim(config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET'));
        $redirectUrl = route('auth.google.callback');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in is not configured yet. Please contact administrator.']);
        }

        return Socialite::buildProvider(\Laravel\Socialite\Two\GoogleProvider::class, [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect'      => $redirectUrl,
        ])
        ->stateless()
        ->with(['prompt' => 'select_account'])
        ->redirect();
    }

    public function handleGoogleCallback()
    {
        $clientId = trim(config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID'));
        $clientSecret = trim(config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET'));
        $redirectUrl = route('auth.google.callback');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in is not configured yet. Please contact administrator.']);
        }

        try {
            $googleUser = Socialite::buildProvider(\Laravel\Socialite\Two\GoogleProvider::class, [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect'      => $redirectUrl,
            ])
            ->stateless()
            ->user();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth callback failed: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }

        $googleEmail = strtolower(trim($googleUser->getEmail()));

        $user = User::where('email', $googleEmail)->first();
        $isNewUser = false;

        if (!$user) {
            $isNewUser = true;

            // Generate a strong, human-readable temporary password
            $tempPassword = ucfirst(Str::lower(Str::random(5)))
                . rand(100, 999)
                . Str::upper(Str::random(2))
                . '!';

            // Auto-registration for Google users
            $user = User::create([
                'full_name'          => $googleUser->getName() ?? 'Google User',
                'email'              => $googleEmail,
                'password'           => Hash::make($tempPassword),
                'user_type'          => 'student',
                'roll_number'        => 'G-' . substr(md5($googleEmail . time()), 0, 8),
                'email_verified_at'  => now(),
            ]);

            // Send credentials email so the user can also log in via email+password
            try {
                $this->sendCredentialsEmail($googleEmail, $tempPassword);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Credentials mail error: ' . $e->getMessage());
            }
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

        // If we detect a Brevo API key (xkeysib-), use Brevo HTTP API (Port 443) which delivers to ALL recipient domains!
        if (str_starts_with((string) $password, 'xkeysib-') || str_starts_with((string) env('BREVO_API_KEY'), 'xkeysib-')) {
            $apiKey = str_starts_with((string) $password, 'xkeysib-') ? $password : env('BREVO_API_KEY');
            $senderEmail = env('BREVO_SENDER_EMAIL', (!empty($fromAddress) && !str_contains($fromAddress, 'resend.dev') ? $fromAddress : 'aquinorenz69@gmail.com'));

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'api-key'      => $apiKey,
                'accept'       => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender'      => ['name' => $fromName, 'email' => $senderEmail],
                'to'          => [['email' => $email]],
                'subject'     => '[PSU Mental Health Portal] Your Login Verification Code',
                'htmlContent' => $html,
                'textContent' => $text,
            ]);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::error("Brevo API HTTP Error [{$response->status()}] for email {$email}: " . $response->body());
            }
        }
        // If we detect a Resend API key, use their HTTP API (port 443)
        elseif (str_starts_with((string) $password, 're_')) {
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
                $errBody = $response->body();
                \Illuminate\Support\Facades\Log::error("Resend API HTTP Error [{$response->status()}] for email {$email}: " . $errBody);

                if (str_contains($errBody, 'testing emails') || $response->status() === 403) {
                    session()->flash('warning', 'Note: Resend Sandbox active. Emails can only be sent to the registered owner address unless a custom domain is added in Resend.');
                }
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

    /**
     * Send a welcome email with the generated email/password credentials
     * to a user who signed up for the first time via Google OAuth.
     */
    protected function sendCredentialsEmail(string $email, string $password)
    {
        $year      = date('Y');
        $fromName  = config('mail.from.name', env('MAIL_FROM_NAME', 'PSU Mental Health Portal'));

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Account Credentials</title>
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
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.8);font-size:13px;">Welcome! Your Account Has Been Created</p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px 40px 24px;">
              <p style="margin:0 0 16px;color:#374151;font-size:15px;line-height:1.6;">Hello,</p>
              <p style="margin:0 0 24px;color:#374151;font-size:15px;line-height:1.6;">
                Your account has been created via Google Sign-In. Below are your login credentials so you can also sign in using your <strong>email and password</strong> at any time.
              </p>

              <!-- Credentials Block -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="background-color:#f0fdf4;border:2px solid #10b981;border-radius:10px;padding:28px 32px;">
                    <p style="margin:0 0 16px;color:#065f46;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;">Your Login Credentials</p>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="padding:8px 0;border-bottom:1px solid #d1fae5;">
                          <span style="color:#6b7280;font-size:13px;font-weight:600;">Email</span><br>
                          <span style="color:#064e3b;font-size:15px;font-weight:700;font-family:'Courier New',Courier,monospace;">$email</span>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;">
                          <span style="color:#6b7280;font-size:13px;font-weight:600;">Temporary Password</span><br>
                          <span style="color:#064e3b;font-size:22px;font-weight:800;letter-spacing:4px;font-family:'Courier New',Courier,monospace;">$password</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="margin:24px 0 0;color:#6b7280;font-size:13px;line-height:1.7;">
                <strong>Important:</strong> Please keep this password safe. You can change it anytime by visiting your profile settings after logging in.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:24px 40px;text-align:center;">
              <p style="margin:0;color:#9ca3af;font-size:12px;line-height:1.6;">
                This is an automated message from $fromName.<br>
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

        $text = "PSU Mental Health Portal - Your Account Credentials\n\n"
            . "Your account has been created via Google Sign-In.\n\n"
            . "Email: $email\n"
            . "Temporary Password: $password\n\n"
            . "You can use these credentials to log in with email and password.\n"
            . "Please keep your password safe and change it in your profile settings.\n\n"
            . "-- PSU Mental Health Portal";

        $apiKey      = config('mail.mailers.smtp.password', env('MAIL_PASSWORD'));
        $fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS'));

        if (str_starts_with((string) $apiKey, 're_')) {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from'     => $fromName . ' <' . $fromAddress . '>',
                'to'       => [$email],
                'reply_to' => $fromAddress,
                'subject'  => '[PSU Mental Health Portal] Your Account Has Been Created',
                'html'     => $html,
                'text'     => $text,
                'tags'     => [
                    ['name' => 'category', 'value' => 'welcome'],
                ],
            ]);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::error('Credentials mail Resend error: ' . $response->body());
            }
        } else {
            \Illuminate\Support\Facades\Mail::html($html, function ($message) use ($email, $fromName, $fromAddress, $text) {
                $message->to($email)
                    ->replyTo($fromAddress, $fromName)
                    ->subject('[PSU Mental Health Portal] Your Account Has Been Created')
                    ->text($text);
            });
        }
    }
}
