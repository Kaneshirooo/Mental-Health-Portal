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

        $prevScore = AssessmentScore::where('user_id', auth()->id())
            ->where('assessment_date', '<', $score->assessment_date)
            ->latest('assessment_date')
            ->first();
        $nextScore = AssessmentScore::where('user_id', auth()->id())
            ->where('assessment_date', '>', $score->assessment_date)
            ->oldest('assessment_date')
            ->first();

        return view('student.reports.show', compact('score', 'counselorNote', 'prevScore', 'nextScore'));
    }

    public function showSession($pre_id)
    {
        $session = AiPreassessment::where('pre_id', $pre_id)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        $prevSession = AiPreassessment::where('student_id', auth()->id())
            ->where('created_at', '<', $session->created_at)
            ->latest('created_at')
            ->first();
        $nextSession = AiPreassessment::where('student_id', auth()->id())
            ->where('created_at', '>', $session->created_at)
            ->oldest('created_at')
            ->first();

        return view('student.reports.session_show', compact('session', 'prevSession', 'nextSession'));
    }
}
