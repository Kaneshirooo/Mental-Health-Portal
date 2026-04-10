<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentScore;
use App\Traits\GeneratesClinicalExport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    use GeneratesClinicalExport;

    public function index()
    {
        $total_assessments = AssessmentScore::count();
        $total_students = AssessmentScore::distinct('user_id')->count();
        
        $risk_counts = AssessmentScore::selectRaw('risk_level, count(*) as cnt')
            ->groupBy('risk_level')
            ->pluck('cnt', 'risk_level');

        // Monthly data (last 6 months)
        $monthly_data = AssessmentScore::where('assessment_date', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(assessment_date, '%Y-%m') as month, count(*) as cnt")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthly_labels = $monthly_data->map(fn($d) => Carbon::parse($d->month . '-01')->format('M Y'));
        $monthly_counts = $monthly_data->pluck('cnt');

        $recent_assessments = AssessmentScore::with('user')
            ->latest('assessment_date')
            ->take(20)
            ->get();

        return view('admin.reports.index', compact(
            'total_assessments', 'total_students', 'risk_counts', 
            'monthly_labels', 'monthly_counts', 'recent_assessments'
        ));
    }

    public function export()
    {
        $headers = ['Student Name', 'Email', 'Roll Number', 'Department', 'Overall Score', 'Depression', 'Anxiety', 'Stress', 'Risk Level', 'Date'];

        $query = AssessmentScore::with('user');

        $callback = function ($s) {
            return [
                $s->user->full_name ?? 'Unknown',
                $s->user->email ?? 'Unknown',
                $s->user->roll_number ?? 'Unknown',
                $s->user->department ?? 'Unknown',
                $s->overall_score,
                $s->depression_score,
                $s->anxiety_score,
                $s->stress_score,
                $s->risk_level,
                $s->assessment_date,
            ];
        };

        return $this->streamCsvExport(
            $query,
            $headers,
            $callback,
            'assessments_' . date('Y-m-d') . '.csv'
        );
    }
}
