<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\SessionLog;
use Carbon\Carbon;

class OtpController extends Controller
{
    public function showVerifyForm()
    {
        if (!Session::has('temp_user')) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', [
            'email' => Session::get('temp_user')['email']
        ]);
    }

    // Called via AJAX from the OTP page to send email in the background
    public function sendBackground(Request $request)
    {
        if (!Session::has('temp_user')) {
            return response()->json(['ok' => false]);
        }

        $tempUser = Session::get('temp_user');

        try {
            $this->sendOtpEmail($tempUser['email'], $tempUser['otp_code']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Background Send Error: ' . $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|numeric|digits:6',
        ]);

        if (!Session::has('temp_user')) {
            return redirect()->route('login');
        }

        $tempUser = Session::get('temp_user');

        if (Carbon::now()->isAfter($tempUser['otp_expiry'])) {
            return back()->withErrors(['otp_code' => 'Verification code has expired. Please request a new one.']);
        }

        $enteredOtp = str_pad(preg_replace('/\D+/', '', (string) $request->input('otp_code')), 6, '0', STR_PAD_LEFT);
        $sessionOtp = str_pad(preg_replace('/\D+/', '', (string) ($tempUser['otp_code'] ?? '')), 6, '0', STR_PAD_LEFT);

        if (hash_equals($sessionOtp, $enteredOtp)) {
            // Success - Finalize Login
            $user = User::findOrFail($tempUser['user_id']);
            Auth::login($user);

            // Clean up session
            Session::forget('temp_user');

            // Log session start
            SessionLog::create([
                'user_id' => $user->user_id,
                'login_time' => Carbon::now(),
                'activity' => $tempUser['activity'] ?? 'OTP Verified login',
            ]);

            // Clean up any unanswered pending calls from offline periods
            $userRole = strtolower((string) ($user->user_type->value ?? $user->user_type));
            if (in_array($userRole, ['counselor', 'admin'], true)) {
                \App\Models\EmergencyCall::cleanupStaleCalls(0);
            }

            return $this->redirectUserByRole($user);
        }

        \Illuminate\Support\Facades\Log::warning('OTP mismatch during verification', [
            'user_id' => $tempUser['user_id'] ?? null,
            'entered_length' => strlen($enteredOtp),
            'session_length' => strlen($sessionOtp),
            'entered_suffix' => substr($enteredOtp, -2),
            'session_suffix' => substr($sessionOtp, -2),
        ]);

        return back()->withErrors(['otp_code' => 'Incorrect verification code. Please try again.']);
    }

    public function resend()
    {
        if (!Session::has('temp_user')) {
            return redirect()->route('login');
        }

        $tempUser = Session::get('temp_user');
        $otp = rand(100000, 999999);
        
        $tempUser['otp_code'] = $otp;
        $tempUser['otp_expiry'] = Carbon::now()->addMinutes(10);
        Session::put('temp_user', $tempUser);

        try {
            $this->sendOtpEmail($tempUser['email'], $otp);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("OTP Resend Mail Error: " . $e->getMessage());
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
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
                'from'     => $fromName . ' <' . $fromAddress . '>',
                'to'       => [$email],
                'reply_to' => $fromAddress,
                'subject'  => '[PSU Mental Health Portal] Your Login Verification Code',
                'html'     => $html,
                'text'     => $text,
                'tags'     => [
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

    protected function redirectUserByRole($user)
    {
        return redirect()->route($user->dashboardRoute());
    }
}
