<?php

namespace App\Services;

use App\Mail\CounselorAlert;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends Gmail/email alerts to counselors. Failures are logged only —
 * email must never break appointments, calls, or assessments.
 *
 * NOTE: Render Free Tier blocks outbound SMTP (ports 25/465/587), so on
 * production we send through an HTTP email API (port 443):
 *   1. Brevo HTTP API when a Brevo key (xkeysib-...) is configured —
 *      delivers to ANY recipient address.
 *   2. Resend HTTP API when a Resend key (re_...) is configured —
 *      sandbox accounts only deliver to the owner's own address unless
 *      a custom domain is verified in Resend.
 *   3. Plain SMTP otherwise (works on local dev / unrestricted hosts).
 */
class CounselorMailer
{
    /**
     * @param \Illuminate\Support\Collection|array $recipients Users with email addresses
     */
    public static function send($recipients, string $title, string $body, ?string $actionUrl = null, string $actionLabel = 'Open Portal'): void
    {
        $emails = collect($recipients)
            ->map(fn($u) => is_string($u) ? $u : ($u->email ?? null))
            ->filter()
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            return;
        }

        $smtpPassword = (string) config('mail.mailers.smtp.password', env('MAIL_PASSWORD'));
        $brevoKey = (string) env('BREVO_API_KEY', '');
        if ($brevoKey === '' && str_starts_with($smtpPassword, 'xkeysib-')) {
            $brevoKey = $smtpPassword;
        }

        if ($brevoKey !== '') {
            foreach ($emails as $email) {
                self::sendViaBrevo($email, $title, $body, $actionUrl, $actionLabel);
            }
            return;
        }

        $resendKey = (string) env('RESEND_API_KEY', '');
        if ($resendKey === '' && str_starts_with($smtpPassword, 're_')) {
            $resendKey = $smtpPassword;
        }

        if ($resendKey !== '') {
            foreach ($emails as $email) {
                self::sendViaResend($email, $resendKey, $title, $body, $actionUrl, $actionLabel);
            }
            return;
        }

        try {
            foreach ($emails as $email) {
                Mail::to($email)->send(new CounselorAlert($title, $body, $actionUrl, $actionLabel));
            }
        } catch (\Throwable $e) {
            Log::warning('CounselorMailer SMTP failed: ' . $e->getMessage());
        }
    }

    protected static function fromAddress(): string
    {
        $from = (string) config('mail.from.address', env('MAIL_FROM_ADDRESS', ''));
        return $from !== '' ? $from : 'aquinorenz69@gmail.com';
    }

    protected static function fromName(): string
    {
        return (string) config('mail.from.name', env('MAIL_FROM_NAME', 'PSU Mental Health Portal'));
    }

    protected static function htmlBody(string $title, string $body, ?string $actionUrl, string $actionLabel): string
    {
        $safeTitle = e($title);
        $safeBody = nl2br(e($body));
        $button = $actionUrl
            ? '<a href="' . e($actionUrl) . '" style="display:inline-block;background:#059669;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 24px;border-radius:10px;">' . e($actionLabel) . '</a>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:0 auto;padding:24px;">
<div style="background:#064e3b;border-radius:16px 16px 0 0;padding:20px 24px;color:#ffffff;">
<div style="font-size:12px;font-weight:bold;letter-spacing:2px;opacity:0.8;">PSU STUDENT SUPPORT</div>
<h2 style="margin:6px 0 0;font-size:20px;">{$safeTitle}</h2>
</div>
<div style="background:#ffffff;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 16px 16px;padding:24px;color:#334155;font-size:15px;line-height:1.6;">
<p style="margin:0 0 16px;">{$safeBody}</p>
{$button}
<p style="font-size:12px;color:#94a3b8;margin:20px 0 0;">This is an automated message from the PSU Mental Health Portal. Please log in to review the details.</p>
</div>
</div>
</body>
</html>
HTML;
    }

    protected static function sendViaBrevo(string $email, string $title, string $body, ?string $actionUrl, string $actionLabel): void
    {
        try {
            $brevoKey = (string) env('BREVO_API_KEY', '');
            $smtpPassword = (string) config('mail.mailers.smtp.password', env('MAIL_PASSWORD'));
            if ($brevoKey === '' && str_starts_with($smtpPassword, 'xkeysib-')) {
                $brevoKey = $smtpPassword;
            }

            $response = Http::withHeaders([
                'api-key' => $brevoKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => ['name' => self::fromName(), 'email' => self::fromAddress()],
                'to' => [['email' => $email]],
                'subject' => '[PSU Mental Health Portal] ' . $title,
                'htmlContent' => self::htmlBody($title, $body, $actionUrl, $actionLabel),
                'textContent' => $title . "\n\n" . $body,
            ]);

            if (!$response->successful()) {
                Log::warning("CounselorMailer Brevo API HTTP Error [{$response->status()}] for {$email}: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('CounselorMailer Brevo failed: ' . $e->getMessage());
        }
    }

    protected static function sendViaResend(string $email, string $resendKey, string $title, string $body, ?string $actionUrl, string $actionLabel): void
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $resendKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => self::fromName() . ' <' . self::fromAddress() . '>',
                'to' => [$email],
                'reply_to' => self::fromAddress(),
                'subject' => '[PSU Mental Health Portal] ' . $title,
                'html' => self::htmlBody($title, $body, $actionUrl, $actionLabel),
                'text' => $title . "\n\n" . $body,
            ]);

            if (!$response->successful()) {
                Log::warning("CounselorMailer Resend API HTTP Error [{$response->status()}] for {$email}: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('CounselorMailer Resend failed: ' . $e->getMessage());
        }
    }

    /**
     * All counselors and admins.
     */
    public static function allStaff()
    {
        return User::whereIn('user_type', ['counselor', 'admin'])->get();
    }
}
