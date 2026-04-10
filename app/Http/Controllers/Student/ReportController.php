<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentScore;
use App\Models\CounselorNote;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = AssessmentScore::where('user_id', auth()->id())
            ->latest('assessment_date')
            ->get();

        return view('student.reports.index', compact('reports'));
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
}
