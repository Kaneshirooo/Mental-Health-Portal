<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Appointment;
use App\Models\User;
use App\Models\CounselorNote;
use App\Models\AssessmentScore;
use App\Models\AnonymousNote;
use App\Models\AnonymousNoteMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $counselor = Auth::user();

        // Handle archiving (marking as read)
        if ($request->has('archive_note')) {
            AnonymousNote::where('note_id', $request->archive_note)->update(['status' => 'archived']);
            return redirect()->route('counselor.dashboard')->with('success', 'Note archived.');
        }

        $pending_appointments = Appointment::where('counselor_id', $counselor->user_id)
            ->where('status', 'requested')
            ->with('student')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $confirmed_appointments = Appointment::where('counselor_id', $counselor->user_id)
            ->where('status', 'confirmed')
            ->where('scheduled_at', '>=', now())
            ->with('student')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $recent_notes = CounselorNote::where('counselor_id', $counselor->user_id)
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 1. High-risk students (Critical first, then High)
        $priority_queue = User::where('user_type', 'student')
            ->with('latestAssessment')
            ->whereHas('assessmentScores', function($query) {
                $query->whereIn('risk_level', ['High', 'Critical'])
                    ->where('assessment_date', function($sub) {
                        $sub->select(DB::raw('MAX(assessment_date)'))
                            ->from('assessment_scores')
                            ->whereColumn('user_id', 'users.user_id');
                    });
            })
            ->get()
            ->sortByDesc(fn($u) => $u->latest_assessment->overall_score ?? 0)
            ->sortBy(function($u) {
                $level = $u->latest_assessment->risk_level ?? '';
                if ($level === 'Critical') return 0;
                if ($level === 'High') return 1;
                return 2;
            });

        // 2. Stats
        $latestAssessmentsCount = DB::table('assessment_scores as a1')
            ->select('risk_level', DB::raw('count(*) as count'))
            ->where('assessment_date', function ($query) {
                $query->select(DB::raw('max(assessment_date)'))
                    ->from('assessment_scores as a2')
                    ->whereColumn('a1.user_id', 'a2.user_id');
            })
            ->groupBy('risk_level')
            ->get()
            ->pluck('count', 'risk_level');

        $stats = [
            'total_students' => User::where('user_type', 'student')->count(),
            'low_risk' => $latestAssessmentsCount->get('Low', 0),
            'moderate_risk' => $latestAssessmentsCount->get('Moderate', 0),
            'high_risk' => $latestAssessmentsCount->get('High', 0),
            'critical_risk' => $latestAssessmentsCount->get('Critical', 0),
        ];

        // 3. Anonymous Notes (Status: new, read, replied)
        $anon_notes = AnonymousNote::whereIn('status', ['new', 'read', 'replied'])
            ->with(['messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('counselor.dashboard', compact(
            'pending_appointments',
            'confirmed_appointments',
            'recent_notes',
            'priority_queue',
            'stats',
            'anon_notes'
        ));
    }

    public function suggestReply(Request $request)
    {
        $request->validate(['note_text' => 'required|string']);
        
        $gemini = app(\App\Services\GeminiService::class);
        $prompt = "Act as a clinical psychologist. A student sent this anonymous note: \"{$request->note_text}\".\n"
                . "Provide a professional, empathetic, and concise clinical response (max 100 words) that a counselor could send back.\n"
                . "Focus on validation and recommending a session if appropriate. Do not use placeholders.";
                
        $suggestion = $gemini->generateResponse([], $prompt);
        
        return response()->json([
            'success' => true,
            'suggestion' => $suggestion ?: 'I understand your concern. Would you like to schedule a private session to discuss this further?'
        ]);
    }
}
