<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Enums\UserRole;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\DB;

class ClinicalAlertService
{
    /**
     * Trigger a proactive alert for low mood logs.
     */
    public function triggerMoodAlert(User $student, int $score, string $emoji, ?string $note, ?string $sentimentLabel = null): void
    {
        if ($score > 2 && !$sentimentLabel) {
            return;
        }

        // Higher priority if AI detects strong negative sentiment
        $isHighRiskSentiment = in_array($sentimentLabel, ['Sad', 'Anxious', 'Frustrated', 'Overwhelmed']);
        
        $alertTitle = "Mood Alert: " . ($score === 1 || $isHighRiskSentiment ? "Critical" : "Concerning");
        $sentimentContext = $sentimentLabel ? " [AI Sentiment: {$sentimentLabel}]" : "";
        
        $alertMsg = "{$student->full_name} just logged a {$emoji} mood score ({$score}/5).{$sentimentContext} " . 
                     ($note ? "Note: \"{$note}\"" : "No note was provided.");

        // Find assigned counselors via confirmed appointments
        $counselorIds = DB::table('appointments')
            ->where('student_id', $student->user_id)
            ->where('status', AppointmentStatus::CONFIRMED->value)
            ->distinct()
            ->pluck('counselor_id');

        if ($counselorIds->isNotEmpty()) {
            foreach ($counselorIds as $id) {
                $this->createNotification((int) $id, $alertTitle, $alertMsg);
            }
        } else {
            $this->notifyAdmins("[Global] {$alertTitle}", "Unassigned Student: {$alertMsg}");
        }
    }

    /**
     * Notify all administrators.
     */
    private function notifyAdmins(string $title, string $message): void
    {
        $admins = User::where('user_type', UserRole::ADMIN)->get();
        foreach ($admins as $admin) {
            $this->createNotification($admin->user_id, $title, $message);
        }
    }

    /**
     * Internal helper to create a clinical notification.
     */
    private function createNotification(int $userId, string $title, string $message): void
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => 'mood_alert'
        ]);
    }
}
