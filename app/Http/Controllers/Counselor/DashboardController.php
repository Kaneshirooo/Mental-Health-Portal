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
use App\Services\OpenRouterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected readonly OpenRouterService $openRouter
    ) {}

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
            ->with(['latestAssessment', 'assessmentScores' => function($q) {
                $q->orderBy('assessment_date', 'desc')->limit(2);
            }])
            ->whereHas('assessmentScores', function($query) {
                $query->whereIn('risk_level', ['High', 'Critical'])
                    ->where('assessment_date', '=', function($sub) {
                        $sub->select(DB::raw('MAX(assessment_date)'))
                            ->from('assessment_scores')
                            ->whereColumn('user_id', 'users.user_id');
                    });
            })
            ->get()
            ->map(function($user) {
                $scores = $user->assessmentScores;
                $user->clinical_shift = 'Stable';
                $user->shift_direction = 'none';
                
                if ($scores->count() >= 2) {
                    $latest = $scores[0]->overall_score;
                    $previous = $scores[1]->overall_score;
                    
                    // Note: Higher score usually means higher distress in DASS-21
                    if ($latest > $previous + 3) {
                        $user->clinical_shift = 'Declining';
                        $user->shift_direction = 'down';
                    } elseif ($latest < $previous - 3) {
                        $user->clinical_shift = 'Improving';
                        $user->shift_direction = 'up';
                    }
                }
                return $user;
            })
            ->sortByDesc(fn($u) => $u->latestAssessment->overall_score ?? 0)
            ->sortBy(function($u) {
                $level = $u->latestAssessment->risk_level ?? '';
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
            'pending_triages' => Appointment::where('counselor_id', $counselor->user_id)->where('status', 'requested')->count(),
            'critical_vector' => $latestAssessmentsCount->get('High', 0) + $latestAssessmentsCount->get('Critical', 0),
            'active_dialogues' => AnonymousNote::whereIn('status', ['new', 'read', 'replied'])->count(),
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

        $noteText = $request->note_text;

        $prompt = "You are a professional mental health counselor at a university. A student sent this anonymous message:\n\n"
            . "\"{$noteText}\"\n\n"
            . "Write a warm, professional, and supportive reply (max 80 words). "
            . "Acknowledge their concern, offer brief reassurance, and invite them to schedule a private session if needed. "
            . "Do not use clinical jargon. Be human and empathetic.";

        $messages = [
            ['role' => 'user', 'parts' => [['text' => $prompt]]]
        ];

        $suggestion = $this->openRouter->generateResponse($messages);

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion ?: 'I understand your concern. Would you like to schedule a private session to discuss this further?'
        ]);
    }

    public function toggleEmergencyStatus()
    {
        $user = Auth::user();
        $user->is_emergency_available = !$user->is_emergency_available;
        $user->save();

        return response()->json([
            'success' => true,
            'is_available' => $user->is_emergency_available,
            'message' => $user->is_emergency_available ? 'Emergency Status: Available' : 'Emergency Status: Unavailable'
        ]);
    }
}
