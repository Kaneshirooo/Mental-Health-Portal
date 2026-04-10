<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with('student')
            ->where('counselor_id', auth()->id())
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $now = now();
        $upcoming = $appointments->filter(fn($a) => $a->scheduled_at >= $now);
        $past = $appointments->filter(fn($a) => $a->scheduled_at < $now);

        return view('counselor.appointments', compact('upcoming', 'past'));
    }

    /**
     * Trigger Emergency Reassignment for all upcoming appointments.
     */
    public function triggerEmergency(Request $request)
    {
        $counselor = auth()->user();
        $upcoming = Appointment::where('counselor_id', $counselor->user_id)
            ->whereIn('status', ['requested', 'confirmed'])
            ->where('scheduled_at', '>=', now())
            ->get();

        $reassignedCount = 0;
        $cancelledCount = 0;

        foreach ($upcoming as $appointment) {
            $scheduledAt = $appointment->scheduled_at;
            $endTime = (clone $scheduledAt)->addMinutes($appointment->duration_min);

            // Find an alternative counselor
            $replacement = User::whereIn('user_type', ['counselor', 'admin'])
                ->where('user_id', '!=', $counselor->user_id)
                ->whereDoesntHave('counselorAppointments', function ($query) use ($scheduledAt, $endTime) {
                    $query->whereIn('status', ['requested', 'confirmed'])
                        ->where(function ($q) use ($scheduledAt, $endTime) {
                            $q->whereBetween('scheduled_at', [$scheduledAt, $endTime])
                                ->orWhereRaw('DATE_ADD(scheduled_at, INTERVAL duration_min MINUTE) > ? AND scheduled_at < ?', [$scheduledAt, $endTime]);
                        });
                })
                ->first();

            if ($replacement) {
                $appointment->update([
                    'counselor_id' => $replacement->user_id,
                    'counselor_message' => "Emergency Reassignment: Counselor {$counselor->full_name} had an emergency. This session has been transferred to Counselor {$replacement->full_name}."
                ]);

                // Notify Student
                Notification::create([
                    'user_id' => $appointment->student_id,
                    'title' => 'Counselor Emergency Reassignment 🚨',
                    'message' => "Your counselor had an emergency. Your session on {$scheduledAt->format('F d, g:i A')} has been reassigned to Counselor {$replacement->full_name}.",
                    'type' => 'appointment',
                ]);

                // Notify New Counselor
                Notification::create([
                    'user_id' => $replacement->user_id,
                    'title' => 'Emergency Reassignment ⚠️',
                    'message' => "You have been reassigned an emergency session with {$appointment->student->full_name} on {$scheduledAt->format('F d, g:i A')}.",
                    'type' => 'appointment',
                ]);

                $reassignedCount++;
            } else {
                // No counselor available
                $appointment->update([
                    'status' => 'cancelled',
                    'counselor_message' => 'Emergency Reassignment Failed: No other counselor was available during this time slot.'
                ]);

                Notification::create([
                    'user_id' => $appointment->student_id,
                    'title' => 'Appointment Cancelled 🚨',
                    'message' => "Your counselor had an emergency, and no others were available. Please reschedule your session for another time.",
                    'type' => 'appointment',
                ]);

                $cancelledCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Emergency protocol complete. {$reassignedCount} appointments reassigned, {$cancelledCount} cancelled."
        ]);
    }

    public function handleAction(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'appt_action' => 'required|in:confirm,decline,cancel,complete,reschedule',
            'counselor_message' => 'nullable|string|max:1000',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $appointment = Appointment::where('appointment_id', $request->appointment_id)
            ->where('counselor_id', auth()->id())
            ->firstOrFail();

        $action = $request->appt_action;
        $msg = $request->counselor_message;

        if ($action === 'reschedule') {
            $appointment->update([
                'scheduled_at' => $request->scheduled_at,
                'counselor_message' => $msg,
                'status' => 'confirmed' // Rescheduling confirmed appointments stays confirmed
            ]);
            
            Notification::create([
                'user_id' => $appointment->student_id,
                'title' => 'Appointment Rescheduled',
                'message' => 'Your session with ' . auth()->user()->full_name . ' has been moved to ' . $appointment->scheduled_at->format('F d, Y \a\t g:i A') . '.',
                'type' => 'appointment',
            ]);

            return response()->json(['success' => true, 'newStatus' => 'confirmed']);
        }

        $statuses = [
            'confirm' => 'confirmed',
            'decline' => 'declined',
            'cancel' => 'cancelled',
            'complete' => 'completed'
        ];

        $newStatus = $statuses[$action];
        $appointment->update([
            'status' => $newStatus,
            'counselor_message' => $msg
        ]);

        $notifData = [
            'confirmed' => ['Appointment Confirmed ✅', 'Your session with ' . auth()->user()->full_name . ' on ' . $appointment->scheduled_at->format('F d, Y \a\t g:i A') . ' has been confirmed.'],
            'declined' => ['Appointment Declined', 'Your session request for ' . $appointment->scheduled_at->format('F d, Y \a\t g:i A') . ' was declined.'],
            'cancelled' => ['Appointment Cancelled', 'Your session on ' . $appointment->scheduled_at->format('F d, Y \a\t g:i A') . ' has been cancelled.'],
            'completed' => ['Session Completed 🎓', 'Your counseling session on ' . $appointment->scheduled_at->format('F d, Y \a\t g:i A') . ' has been marked complete.'],
        ];

        Notification::create([
            'user_id' => $appointment->student_id,
            'title' => $notifData[$newStatus][0],
            'message' => $notifData[$newStatus][1],
            'type' => 'appointment',
        ]);

        return response()->json(['success' => true, 'newStatus' => $newStatus]);
    }
}
