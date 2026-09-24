<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\SatisfactionSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SurveyController extends Controller
{
    public function show(Appointment $appointment)
    {
        // Ensure only the student who attended can see the survey
        if ($appointment->student_id !== Auth::id()) {
            abort(403);
        }

        // Ensure appointment is completed
        if ($appointment->status !== \App\Enums\AppointmentStatus::COMPLETED) {
            return redirect()->route('student.dashboard')->with('error', 'Survey only available after session completion.');
        }

        // Check if already submitted
        $existing = SatisfactionSurvey::where('appointment_id', $appointment->appointment_id)->first();
        if ($existing) {
            return view('student.survey_thank_you');
        }

        return view('student.survey', compact('appointment'));
    }

    public function store(Request $request, Appointment $appointment)
    {
        if ($appointment->student_id !== Auth::id()) {
            abort(403);
        }

        // Prevent duplicate submissions
        $exists = SatisfactionSurvey::where('appointment_id', $appointment->appointment_id)->exists();
        if ($exists) {
            return redirect()->route('student.dashboard')->with('info', 'Feedback already submitted for this session.');
        }

        $request->validate([
            'sqd0' => 'required|integer|min:1|max:5',
            'sqd1' => 'required|integer|min:1|max:5',
            'sqd2' => 'required|integer|min:1|max:5',
            'sqd3' => 'required|integer|min:1|max:5',
            'sqd4' => 'required|integer|min:1|max:5',
            'sqd5' => 'required|integer|min:0|max:5', // 0 = N/A
            'sqd6' => 'required|integer|min:1|max:5',
            'sqd7' => 'required|integer|min:1|max:5',
            'sqd8' => 'required|integer|min:1|max:5',
            'feedback_text' => 'nullable|string|max:1000',
        ]);

        $survey = SatisfactionSurvey::create([
            'appointment_id' => $appointment->appointment_id,
            'student_id' => Auth::id(),
            'counselor_id' => $appointment->counselor_id,
            'sqd0' => $request->sqd0,
            'sqd1' => $request->sqd1,
            'sqd2' => $request->sqd2,
            'sqd3' => $request->sqd3,
            'sqd4' => $request->sqd4,
            'sqd5' => $request->sqd5,
            'sqd6' => $request->sqd6,
            'sqd7' => $request->sqd7,
            'sqd8' => $request->sqd8,
            'suggestions' => $request->feedback_text,
        ]);

        // Notify session counselor
        \App\Models\Notification::create([
            'user_id' => $appointment->counselor_id,
            'title' => 'New Feedback Submitted',
            'message' => "Student " . Auth::user()->full_name . " has submitted a satisfaction survey for their session on " . $appointment->scheduled_at->format('M d, Y') . ".",
            'type' => 'clinical_feedback',
            'is_read' => false,
        ]);

        // Notify Head Counselor (User ID 3)
        if ($appointment->counselor_id != 3) {
            \App\Models\Notification::create([
                'user_id' => 3,
                'title' => 'New Feedback Submitted',
                'message' => "A new clinical survey has been submitted for " . $appointment->counselor->full_name . "'s session with student " . Auth::user()->full_name . ".",
                'type' => 'clinical_feedback',
                'is_read' => false,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Institutional feedback successfully integrated.'
            ]);
        }

        return view('student.survey_thank_you');
    }
}
