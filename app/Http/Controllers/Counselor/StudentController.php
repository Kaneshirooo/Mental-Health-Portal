<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AssessmentScore;
use App\Models\MoodLog;
use App\Models\CounselorNote;
use App\Models\EmergencyCall;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function __construct(
        protected readonly OpenRouterService $openRouter
    ) {}

    /**
     * Display a listing of students with their latest assessment data.
     */
    public function index(Request $request): View
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $course = $request->course;
        $semester = $request->semester;
        $filter = $request->filter;
        $search = $request->search;

        $query = User::where('user_type', 'student')
            ->with('latestAssessment')
            ->orderBy('full_name');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('roll_number', 'like', "%$search%");
            });
        }

        if ($filter) {
            $query->whereHas('latestAssessment', function($q) use ($filter) {
                $q->where('risk_level', $filter);
            });
        }

        if ($course) {
            $query->where('course', $course);
        }

        if ($semester) {
            $query->where('semester', $semester);
        }

        if ($start_date && $end_date) {
            $query->whereHas('assessmentScores', function($q) use ($start_date, $end_date) {
                $q->whereBetween('assessment_date', [
                    Carbon::parse($start_date)->startOfDay(), 
                    Carbon::parse($end_date)->endOfDay()
                ]);
            });
        }

        $students = $query->get();

        // Get unique courses and semesters for filters
        $courses = User::where('user_type', 'student')->whereNotNull('course')->distinct()->pluck('course');
        $semesters = User::where('user_type', 'student')->whereNotNull('semester')->distinct()->pluck('semester');

        // Analytics Data
        $risk_distribution = $students->groupBy(fn($s) => $s->latestAssessment->risk_level ?? 'Uncategorized')
            ->map(fn($group) => $group->count());
            
        $course_distribution = $students->groupBy('course')
            ->map(fn($group) => $group->count());

        // High Risk per Course (considering ANY assessment in the range if provided)
        $high_risk_query = User::where('user_type', 'student')
            ->whereHas('assessmentScores', function($q) use ($start_date, $end_date) {
                if ($start_date && $end_date) {
                    $q->whereBetween('assessment_date', [
                        Carbon::parse($start_date)->startOfDay(), 
                        Carbon::parse($end_date)->endOfDay()
                    ]);
                }
                $q->whereIn('risk_level', ['High', 'Critical']);
            });
        
        if ($course) $high_risk_query->where('course', $course);
        if ($semester) $high_risk_query->where('semester', $semester);

        $high_risk_by_course = $high_risk_query->get()
            ->groupBy('course')
            ->map(fn($group) => $group->count());

        return view('counselor.students.index', compact(
            'students', 'search', 'filter', 'course', 'semester', 'start_date', 'end_date',
            'courses', 'semesters', 'risk_distribution', 'course_distribution', 'high_risk_by_course'
        ));
    }

    /**
     * Display the clinical profile for a specific student.
     */
    public function show(User $student): View
    {
        if (!$student->isStudent()) {
            abort(404);
        }

        $assessments = AssessmentScore::where('user_id', $student->user_id)
            ->orderBy('assessment_date', 'asc')
            ->get();

        $notes = CounselorNote::where('student_id', $student->user_id)
            ->latest('created_at')
            ->get();

        $sessions = \App\Models\AiPreassessment::where('student_id', $student->user_id)
            ->latest('created_at')
            ->get();

        $emergencyCalls = EmergencyCall::where('student_id', $student->user_id)
            ->where('counselor_id', auth()->id())
            ->whereIn('status', ['active', 'ended'])
            ->latest('created_at')
            ->get();

        // Chart data mapping
        $chart_labels = $assessments->map(fn($r) => $r->assessment_date->format('M d'));
        $chart_scores = $assessments->pluck('overall_score');

        return view('counselor.students.show', compact('student', 'assessments', 'notes', 'sessions', 'chart_labels', 'chart_scores', 'emergencyCalls'));
    }

    /**
     * Archive a clinical note for the student.
     */
    public function addNote(Request $request, User $student): RedirectResponse
    {
        $request->validate([
            'note_text' => 'required|string|min:3',
            'recommendation' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $note = CounselorNote::create([
            'counselor_id' => auth()->id(),
            'student_id' => $student->user_id,
            'note_text' => $request->note_text,
            'recommendation' => $request->recommendation,
            'follow_up_date' => $request->follow_up_date,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Clinical note archived successfully.',
                'note' => [
                    'date' => $note->created_at->format('M d, Y'),
                    'text' => e($note->note_text),
                    'recommendation' => e($note->recommendation),
                    'follow_up' => $note->follow_up_date ? $note->follow_up_date->format('M d, Y') : null
                ]
            ]);
        }

        return back()->with('success', 'Clinical note archived successfully.');
    }

    /**
     * Generate an AI-driven clinical summary for the counselor.
     */
    public function aiSummary(Request $request, User $student): JsonResponse
    {
        if (!$student->isStudent()) {
            return response()->json(['success' => false, 'error' => 'Invalid student.']);
        }

        $assessments = AssessmentScore::where('user_id', $student->user_id)
            ->latest('assessment_date')
            ->limit(5)
            ->get();

        $moods = MoodLog::where('student_id', $student->user_id)
            ->latest('logged_at')
            ->limit(10)
            ->get();

        $pastNotes = CounselorNote::where('student_id', $student->user_id)
            ->latest('created_at')
            ->limit(3)
            ->get();

        $prompt = $this->buildClinicalPrompt($student, $assessments, $moods, $pastNotes);
        
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];
        
        $summary = $this->openRouter->generateResponse($messages);

        if ($summary) {
            return response()->json(['success' => true, 'summary' => nl2br(e($summary))]);
        }

        return response()->json(['success' => false, 'error' => 'AI Service unavailable.']);
    }

    /**
     * Build a structured prompt for the clinical AI summary.
     */
    private function buildClinicalPrompt(User $student, $assessments, $moods, $pastNotes): string
    {
        $prompt = "Act as a clinical psychologist providing a concise student wellness summary for a fellow counselor.\n"
            . "Based on the following data for student: {$student->full_name} (ID: {$student->roll_number}).\n\n"
            . "### RECENT ASSESSMENTS:\n";

        foreach ($assessments as $a) {
            $prompt .= "- Date: {$a->assessment_date} | Overall: {$a->overall_score}% | Risk: {$a->risk_level} "
                . "(D: {$a->depression_score}, A: {$a->anxiety_score}, S: {$a->stress_score})\n";
        }

        $prompt .= "\n### RECENT MOOD LOGS:\n";
        foreach ($moods as $m) {
            $prompt .= "- Date: {$m->logged_at} | Score: {$m->mood_score} | Note: " . ($m->note ?: 'No note') . "\n";
        }

        $prompt .= "\n### PREVIOUS CLINICAL NOTES:\n";
        foreach ($pastNotes as $n) {
            $prompt .= "- Date: {$n->created_at} | Note: {$n->note_text} | Rec: {$n->recommendation}\n";
        }

        $prompt .= "\nINSTRUCTIONS:\nProvide a professional, concise clinical summary (max 250 words) including:\n"
            . "1. **Current Status**: Brief overview of recent trends.\n"
            . "2. **Key Risk Factors**: Any highlighted symptoms or patterns.\n"
            . "3. **Clinical Recommendation**: Suggested focus areas for the next session.\n"
            . "Format using bolding for key terms. Avoid generic advice.";

        return $prompt;
    }
    /**
     * Export the clinical profile as a print-optimized report.
     */
    public function export(User $student): View
    {
        if (!$student->isStudent()) {
            abort(404);
        }

        $assessments = AssessmentScore::where('user_id', $student->user_id)
            ->orderBy('assessment_date', 'asc')
            ->get();

        $moods = MoodLog::where('student_id', $student->user_id)
            ->orderBy('logged_at', 'desc')
            ->limit(30)
            ->get();

        $notes = CounselorNote::where('student_id', $student->user_id)
            ->latest('created_at')
            ->get();

        $chart_labels = $assessments->map(fn($r) => $r->assessment_date->format('M d'));
        $chart_scores = $assessments->pluck('overall_score');

        return view('counselor.students.clinical_report', compact(
            'student', 
            'assessments', 
            'moods', 
            'notes', 
            'chart_labels', 
            'chart_scores'
        ));
    }

    /**
     * Display a specific AI session summary.
     */
    public function showSession(User $student, $pre_id): View
    {
        if (!$student->isStudent()) {
            abort(404);
        }

        $session = \App\Models\AiPreassessment::where('pre_id', $pre_id)
            ->where('student_id', $student->user_id)
            ->firstOrFail();

        return view('counselor.students.session_show', compact('student', 'session'));
    }
}
