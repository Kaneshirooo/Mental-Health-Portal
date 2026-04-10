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

        if ($request->otp_code == $tempUser['otp_code']) {
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

            return $this->redirectUserByRole($user);
        }

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
            return back()->withErrors(['otp_code' => 'Failed to resend verification email.']);
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
        return match ($user->user_type->value ?? $user->user_type) {
            'admin' => redirect()->route('admin.dashboard'),
            'counselor' => redirect()->route('counselor.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
