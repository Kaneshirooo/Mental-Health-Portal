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
            $emailToSend = $tempUser['email'];
            $otpToSend = $otp;
            $controller = $this;
            app()->terminating(function () use ($controller, $emailToSend, $otpToSend) {
                $controller->sendOtpEmail($emailToSend, $otpToSend);
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("OTP Resend Mail Error: " . $e->getMessage());
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
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

        Mail::html($body, function ($message) use ($email) {
            $message->to($email)
                ->subject('Your Verification Code — Mental Health Portal');
        });
    }

    protected function redirectUserByRole($user)
    {
        return redirect()->route($user->dashboardRoute());
    }
}
