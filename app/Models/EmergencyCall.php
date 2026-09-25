<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyCall extends Model
{
    protected $primaryKey = 'call_id';

    protected $fillable = [
        'student_id',
        'counselor_id',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'student_id'  => 'integer',
        'counselor_id' => 'integer',
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(CallMessage::class, 'call_id', 'call_id')->orderBy('created_at', 'asc');
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isActive(): bool   { return $this->status === 'active'; }
    public function isEnded(): bool    { return $this->status === 'ended'; }
    public function isDeclined(): bool { return $this->status === 'declined'; }
    public function isMissed(): bool   { return $this->status === 'missed'; }

    /**
     * Clean up unanswered / stale pending call requests.
     * Automatically transitions pending calls to 'missed' status and dispatches missed call notifications.
     */
    public static function cleanupStaleCalls(int $timeoutSeconds = 90): void
    {
        $threshold = now()->subSeconds($timeoutSeconds);

        $staleCalls = static::where('status', 'pending')
            ->where('created_at', '<=', $threshold)
            ->with(['student'])
            ->get();

        foreach ($staleCalls as $call) {
            $call->update([
                'status'   => 'missed',
                'ended_at' => now(),
            ]);

            $studentName = $call->student->full_name ?? 'A student';
            $callTime = $call->created_at ? $call->created_at->format('h:i A') : 'earlier';

            // Notify all counselors and admins about the missed call
            $counselors = User::whereIn('user_type', ['counselor', 'admin'])->get();
            foreach ($counselors as $counselor) {
                $alreadyNotified = Notification::where('user_id', $counselor->user_id)
                    ->where('type', 'emergency')
                    ->where('title', '🚨 Missed Emergency Call')
                    ->where('message', 'like', "%{$studentName}%")
                    ->where('created_at', '>=', now()->subMinutes(10))
                    ->exists();

                if (!$alreadyNotified) {
                    Notification::create([
                        'user_id' => $counselor->user_id,
                        'title'   => '🚨 Missed Emergency Call',
                        'message' => "Missed call from {$studentName} requested at {$callTime} — call went unanswered.",
                        'type'    => 'emergency',
                    ]);
                }
            }

            // Also notify the student
            Notification::create([
                'user_id' => $call->student_id,
                'title'   => 'Call Unanswered',
                'message' => 'Your emergency call request went unanswered. Please schedule an appointment or reach out to emergency hotlines.',
                'type'    => 'emergency',
            ]);
        }

        // Auto-cleanup abandoned active calls older than 60 minutes
        static::where('status', 'active')
            ->where('updated_at', '<=', now()->subMinutes(60))
            ->update([
                'status'   => 'ended',
                'ended_at' => now(),
            ]);
    }
}
