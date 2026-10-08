<?php

namespace App\Services;

use App\Mail\CounselorAlert;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends Gmail/email alerts to counselors. Failures are logged only —
 * email must never break appointments, calls, or assessments.
 */
class CounselorMailer
{
    /**
     * @param \Illuminate\Support\Collection|array $recipients Users with email addresses
     */
    public static function send($recipients, string $title, string $body, ?string $actionUrl = null, string $actionLabel = 'Open Portal'): void
    {
        try {
            foreach ($recipients as $user) {
                if (empty($user->email)) {
                    continue;
                }
                Mail::to($user->email)->send(new CounselorAlert($title, $body, $actionUrl, $actionLabel));
            }
        } catch (\Throwable $e) {
            Log::warning('CounselorMailer failed: ' . $e->getMessage());
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
