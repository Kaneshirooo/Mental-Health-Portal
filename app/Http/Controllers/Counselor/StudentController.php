<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AssessmentScore;
use App\Models\MoodLog;
use App\Models\CounselorNote;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentController extends Controller
{
    public function __construct(
        protected readonly GeminiService $gemini
    ) {}

    /**
     * Display a listing of students with their latest assessment data.
     */
    public function index(Request $request): View
    {
        $query = User::where('user_type', 'student')
            ->with('latestAssessment')
            ->orderBy('full_name');

        if ($search = $request->search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('roll_number', 'like', "%$search%");
            });
        }

        if ($filter = $request->filter) {
            $query->whereHas('latestAssessment', function($q) use ($filter) {
                $q->where('risk_level', $filter);
            });
        }

        $students = $query->get();

        return view('counselor.students.index', compact('students', 'search', 'filter'));
    }

    /**
     * Display the clinical profile for a specific student.
     */
    public function show(User $student): View
    {
        if ($student->user_type !== 'student') {
            abort(404);
        }

        $assessments = AssessmentScore::where('user_id', $student->user_id)
            ->orderBy('assessment_date', 'asc')
            ->get();

        $notes = CounselorNote::where('student_id', $student->user_id)
            ->latest('created_at')
            ->get();

        // Chart data mapping
        $chart_labels = $assessments->map(fn($r) => $r->assessment_date->format('M d'));
        $chart_scores = $assessments->pluck('overall_score');

        return view('counselor.students.show', compact('student', 'assessments', 'notes', 'chart_labels', 'chart_scores'));
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

        CounselorNote::create([
            'counselor_id' => auth()->id(),
            'student_id' => $student->user_id,
            'note_text' => $request->note_text,
            'recommendation' => $request->recommendation,
            'follow_up_date' => $request->follow_up_date,
        ]);

        return back()->with('success', 'Clinical note archived successfully.');
    }

    /**
     * Generate an AI-driven clinical summary for the counselor.
     */
    public function aiSummary(Request $request, User $student): JsonResponse
    {
        if ($student->user_type !== 'student') {
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
        // lint-id: efc96ed3-824c-4566-a9d9-d056adc28a08
        $summary = $this->gemini->generateResponse([], $prompt);

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
        if ($student->user_type !== 'student') {
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
}
