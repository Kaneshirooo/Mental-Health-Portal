<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentScore;
use App\Models\CounselorNote;
use App\Models\AiPreassessment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = AssessmentScore::where('user_id', auth()->id())
            ->latest('assessment_date')
            ->get();

        $sessions = AiPreassessment::where('student_id', auth()->id())
            ->latest('created_at')
            ->get();

        return view('student.reports.index', compact('reports', 'sessions'));
    }

    public function show(AssessmentScore $score)
    {
        if ($score->user_id !== auth()->id()) {
            abort(403);
        }

        $counselorNote = CounselorNote::where('student_id', auth()->id())
            ->latest('created_at')
            ->first();

        return view('student.reports.show', compact('score', 'counselorNote'));
    }

    public function showSession($pre_id)
    {
        $session = AiPreassessment::where('pre_id', $pre_id)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        return view('student.reports.session_show', compact('session'));
    }
}
