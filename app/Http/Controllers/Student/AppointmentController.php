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

        // Find available counselor (legacy logic)
        $counselors = User::whereIn('user_type', ['counselor', 'admin'])->orderBy('user_id', 'asc')->get();
        $assignedId = null;

        foreach ($counselors as $c) {
            $conflict = Appointment::where('counselor_id', $c->user_id)
                ->whereIn('status', ['requested', 'confirmed'])
                ->where(function($query) use ($scheduledAt, $endTime) {
                    $query->whereBetween('scheduled_at', [$scheduledAt->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s')])
                        ->orWhereRaw('DATE_ADD(scheduled_at, INTERVAL duration_min MINUTE) > ? AND scheduled_at < ?', [$scheduledAt->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s')]);
                })
                ->exists();

            if (!$conflict) {
                $assignedId = $c->user_id;
                break;
            }
        }

        if (!$assignedId) {
            return back()->with('error', 'No counselor is available at that time. Please choose another slot.')->with('booking_conflict', true);
        }

        $appointment = Appointment::create([
            'student_id' => auth()->id(),
            'counselor_id' => $assignedId,
            'scheduled_at' => $request->scheduled_at,
            'duration_min' => $durationMin,
            'status' => 'requested',
            'reason' => $request->reason,
        ]);

        Notification::create([
            'user_id' => $assignedId,
            'title' => 'New Appointment Request',
            'message' => auth()->user()->full_name . ' has requested an appointment on ' . $scheduledAt->format('F d, Y \a\t g:i A') . '.',
            'type' => 'appointment',
        ]);

        return back()->with('success', 'Appointment requested! Your counselor will confirm it soon.');
    }
}
