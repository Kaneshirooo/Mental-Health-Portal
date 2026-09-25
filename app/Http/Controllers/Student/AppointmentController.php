<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with('counselor')
            ->where('student_id', auth()->id())
            ->latest('scheduled_at')
            ->get();

        return view('student.appointments', compact('appointments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'reason' => 'nullable|string|max:1000',
        ]);

        $scheduledAt = Carbon::parse($request->scheduled_at);
        $durationMin = 30;
        $endTime = (clone $scheduledAt)->addMinutes($durationMin);
        
        $dayOfWeek = $scheduledAt->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $requestedStart = $scheduledAt->format('H:i:s');
        $requestedEnd = $endTime->format('H:i:s');

        // Find available counselors who are ACTIVE during this time slot
        $counselors = User::whereIn('user_type', ['counselor', 'admin'])
            ->whereHas('availabilities', function($query) use ($dayOfWeek, $requestedStart, $requestedEnd) {
                $query->where('day_of_week', $dayOfWeek)
                    ->where('start_time', '<=', $requestedStart)
                    ->where('end_time', '>=', $requestedEnd)
                    ->where('is_active', 1);
            })
            ->orderBy('user_id', 'asc')
            ->get();

        $assignedId = null;
        $hasAvailableCounselors = $counselors->isNotEmpty();

        foreach ($counselors as $c) {
            // Check for overlapping appointments
            $conflict = Appointment::where('counselor_id', $c->user_id)
                ->whereIn('status', ['requested', 'confirmed'])
                ->where(function($query) use ($scheduledAt, $endTime) {
                    $query->where(function($q) use ($scheduledAt, $endTime) {
                        $q->where('scheduled_at', '>=', $scheduledAt->format('Y-m-d H:i:s'))
                          ->where('scheduled_at', '<', $endTime->format('Y-m-d H:i:s'));
                    })
                    ->orWhere(function($q) use ($scheduledAt, $endTime) {
                        $q->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_min MINUTE) > ?', [$scheduledAt->format('Y-m-d H:i:s')])
                          ->where('scheduled_at', '<', $scheduledAt->format('Y-m-d H:i:s'));
                    });
                })
                ->exists();

            if (!$conflict) {
                $assignedId = $c->user_id;
                break;
            }
        }

        if (!$assignedId) {
            // If counselors exist for this day/time but all are booked → real conflict
            // If no counselors have this slot in their availability → availability gap
            $isRealConflict = $hasAvailableCounselors;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'  => false,
                    'conflict' => $isRealConflict,
                    'error'    => $isRealConflict
                        ? 'That time slot is fully booked. Please choose a different date or time.'
                        : 'No counselor has availability configured for that day and time. Please choose a different slot.',
                ], 422);
            }
            return back()->with('error', $isRealConflict
                ? 'That time slot is fully booked. Please choose another slot.'
                : 'No counselor has availability for that time. Please choose a different slot.'
            );
        }

        $appointment = Appointment::create([
            'student_id'   => auth()->id(),
            'counselor_id' => $assignedId,
            'scheduled_at' => $request->scheduled_at,
            'duration_min' => $durationMin,
            'status'       => 'requested',
            'reason'       => $request->reason,
        ]);

        $appointment->load('counselor');

        Notification::create([
            'user_id' => $assignedId,
            'title'   => 'New Appointment Request',
            'message' => auth()->user()->full_name . ' has requested an appointment on ' . $scheduledAt->format('F d, Y \a\t g:i A') . '.',
            'type'    => 'appointment',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Appointment requested! Your counselor will confirm it soon.',
                'appointment' => [
                    'appointment_id'       => $appointment->appointment_id,
                    'counselor_name'       => $appointment->counselor->full_name ?? 'Counselor',
                    'scheduled_at'         => $appointment->scheduled_at,
                    'scheduled_at_formatted' => $appointment->scheduled_at->format('M d, Y • g:i A'),
                    'status'               => 'requested',
                    'reason'               => $appointment->reason,
                ],
            ]);
        }

        return back()->with('success', 'Appointment requested! Your counselor will confirm it soon.');
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        // 1. Authorization: Ensure the student owns this appointment
        if ((int)$appointment->student_id !== (int)auth()->id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        // 2. State Check: Can only cancel requested or confirmed appointments
        if (!$appointment->status->isActionable()) {
            return back()->with('error', 'This appointment cannot be cancelled in its current state.');
        }

        // 3. Update Status
        $appointment->update([
            'status' => \App\Enums\AppointmentStatus::CANCELLED
        ]);

        // 4. Notify Counselor
        Notification::create([
            'user_id' => $appointment->counselor_id,
            'title' => 'Appointment Cancelled',
            'message' => auth()->user()->full_name . ' has cancelled their appointment scheduled for ' . $appointment->scheduled_at->format('M d, g:i A') . '.',
            'type' => 'appointment',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Appointment cancelled successfully.']);
        }

        return back()->with('success', 'Appointment cancelled successfully.');
    }
}
