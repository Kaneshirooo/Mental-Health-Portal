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

    public function index(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $course = $request->course;
        $semester = $request->semester;
        $risk_level = $request->risk_level;

        $query = AssessmentScore::query()->with('user');

        if ($start_date && $end_date) {
            $query->whereBetween('assessment_date', [
                Carbon::parse($start_date)->startOfDay(), 
                Carbon::parse($end_date)->endOfDay()
            ]);
        }

        if ($course || $semester) {
            $query->whereHas('user', function($q) use ($course, $semester) {
                if ($course) $q->where('course', $course);
                if ($semester) $q->where('semester', $semester);
            });
        }

        if ($risk_level) {
            $query->where('risk_level', $risk_level);
        }

        $filtered_assessments = $query->get();

        $total_assessments = $filtered_assessments->count();
        $total_students = $filtered_assessments->pluck('user_id')->unique()->count();
        
        $risk_counts = $filtered_assessments->groupBy('risk_level')->map->count();

        // Monthly data 
        if ($start_date && $end_date) {
             $monthly_data = $filtered_assessments->groupBy(fn($d) => $d->assessment_date->format('Y-m'))
                ->map(fn($group) => $group->count())
                ->sortKeys();
        } else {
            $monthly_data = AssessmentScore::where('assessment_date', '>=', now()->subMonths(6))
                ->get()
                ->groupBy(fn($d) => $d->assessment_date->format('Y-m'))
                ->map(fn($group) => $group->count())
                ->sortKeys();
        }

        $monthly_labels = collect($monthly_data->keys())->map(fn($m) => Carbon::parse($m . '-01')->format('M Y'));
        $monthly_counts = collect($monthly_data->values());

        $recent_assessments = $filtered_assessments->sortByDesc('assessment_date')->take(25);

        // High Risk per Course (Full clinical objects for drill-down)
        $high_risk_by_course = $filtered_assessments->whereIn('risk_level', ['High', 'Critical'])
            ->groupBy(fn($a) => optional($a->user)->course ?? 'General');

        $courses = \App\Models\User::where('user_type', 'student')->whereNotNull('course')->distinct()->pluck('course');
        $semesters = \App\Models\User::where('user_type', 'student')->whereNotNull('semester')->distinct()->pluck('semester');

        $avg_depression = round($filtered_assessments->avg('depression_score') ?? 0, 1);
        $avg_anxiety = round($filtered_assessments->avg('anxiety_score') ?? 0, 1);
        $avg_stress = round($filtered_assessments->avg('stress_score') ?? 0, 1);

        $depression_severity = $this->getSeverity('depression', $avg_depression);
        $anxiety_severity = $this->getSeverity('anxiety', $avg_anxiety);
        $stress_severity = $this->getSeverity('stress', $avg_stress);

        $low_count = $risk_counts['Low'] ?? 0;
        $wellness_index = $total_assessments > 0 ? round(($low_count / $total_assessments) * 100) : 100;

        $top_risk_course = $high_risk_by_course->sortByDesc(fn($group) => $group->count())->keys()->first() ?? 'None';
        $top_risk_count = $high_risk_by_course->get($top_risk_course)?->count() ?? 0;

        // ── Period comparison (this month vs last month) ──
        $now = Carbon::now();
        $thisMonth = AssessmentScore::whereYear('assessment_date', $now->year)
            ->whereMonth('assessment_date', $now->month)->get();
        $lastMonthDate = $now->copy()->subMonth();
        $lastMonth = AssessmentScore::whereYear('assessment_date', $lastMonthDate->year)
            ->whereMonth('assessment_date', $lastMonthDate->month)->get();
        $comparison = [
            'this_label' => $now->format('M Y'),
            'last_label' => $lastMonthDate->format('M Y'),
            'this_total' => $thisMonth->count(),
            'last_total' => $lastMonth->count(),
            'this_high' => $thisMonth->whereIn('risk_level', ['High', 'Critical'])->count(),
            'last_high' => $lastMonth->whereIn('risk_level', ['High', 'Critical'])->count(),
        ];
        $comparison['total_change'] = $comparison['this_total'] - $comparison['last_total'];
        $comparison['high_change'] = $comparison['this_high'] - $comparison['last_high'];

        // ── Plain-language interpretation ──
        $dominantArea = 'low mood';
        $dominantScore = $avg_depression;
        if ($avg_anxiety >= $dominantScore && $avg_anxiety >= $avg_stress) {
            $dominantArea = 'worries';
            $dominantScore = $avg_anxiety;
        } elseif ($avg_stress >= $dominantScore) {
            $dominantArea = 'pressure';
            $dominantScore = $avg_stress;
        }
        $interpretation = [];
        $interpretation[] = "Across {$total_assessments} check-ins from {$total_students} students, {$wellness_index}% are doing okay (Low), while " . (($risk_counts['High'] ?? 0) + ($risk_counts['Critical'] ?? 0)) . " results need care or urgent support (High/Critical).";
        $interpretation[] = "The heaviest area on average is {$dominantArea} ({$dominantScore}), so support activities should focus there first.";
        if ($top_risk_course !== 'None') {
            $interpretation[] = "{$top_risk_course} has the most students needing care ({$top_risk_count}), and should be first in line for group sessions.";
        }
        if ($comparison['high_change'] > 0) {
            $interpretation[] = "Students needing urgent support rose from {$comparison['last_high']} ({$comparison['last_label']}) to {$comparison['this_high']} ({$comparison['this_label']}) — counselors should add extra slots this month.";
        } elseif ($comparison['high_change'] < 0) {
            $interpretation[] = "Students needing urgent support fell from {$comparison['last_high']} ({$comparison['last_label']}) to {$comparison['this_high']} ({$comparison['this_label']}) — current support steps appear to be helping.";
        } else {
            $interpretation[] = "Students needing urgent support held steady at {$comparison['this_high']} versus last month — keep the current support rhythm.";
        }

        // ── Priority programs per course ──
        $priority_programs = $high_risk_by_course
            ->sortByDesc(fn($group) => $group->count())
            ->take(5)
            ->map(function ($group, $courseName) {
                $count = $group->count();
                $crit = $group->where('risk_level', 'Critical')->count();
                return [
                    'course' => $courseName ?: 'General',
                    'count' => $count,
                    'critical' => $crit,
                    'program' => $crit > 0
                        ? 'Priority 1: one-on-one reach-outs + small group check-in this week.'
                        : 'Priority 2: study-pressure workshop + weekly mood check-ins.',
                ];
            })->values();

        // ── Risk matrix (risk level × course) ──
        $matrix_courses = $filtered_assessments
            ->groupBy(fn($a) => optional($a->user)->course ?? 'General')
            ->keys()->sort()->values();
        $risk_matrix = [];
        foreach (['Low', 'Moderate', 'High', 'Critical'] as $lvl) {
            $row = ['level' => $lvl, 'cells' => [], 'total' => 0];
            foreach ($matrix_courses as $c) {
                $n = $filtered_assessments->filter(fn($a) => (optional($a->user)->course ?? 'General') === $c && $a->risk_level === $lvl)->count();
                $row['cells'][] = $n;
                $row['total'] += $n;
            }
            $risk_matrix[] = $row;
        }

        return view('admin.reports.index', compact(
            'total_assessments', 'total_students', 'risk_counts',
            'monthly_labels', 'monthly_counts', 'recent_assessments',
            'start_date', 'end_date', 'course', 'semester', 'risk_level', 'courses', 'semesters', 'high_risk_by_course',
            'avg_depression', 'avg_anxiety', 'avg_stress',
            'depression_severity', 'anxiety_severity', 'stress_severity',
            'wellness_index', 'top_risk_course', 'top_risk_count',
            'comparison', 'interpretation', 'priority_programs', 'risk_matrix', 'matrix_courses'
        ));
    }

    private function getSeverity($type, $score)
    {
        if ($type === 'depression') {
            if ($score <= 9) return ['label' => 'Normal', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.12)'];
            if ($score <= 13) return ['label' => 'Mild', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.12)'];
            if ($score <= 20) return ['label' => 'Moderate', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.12)'];
            if ($score <= 27) return ['label' => 'Severe', 'color' => '#f97316', 'bg' => 'rgba(249, 115, 22, 0.12)'];
            return ['label' => 'Extremely Severe', 'color' => '#ef4444', 'bg' => 'rgba(239, 68, 68, 0.12)'];
        } elseif ($type === 'anxiety') {
            if ($score <= 7) return ['label' => 'Normal', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.12)'];
            if ($score <= 9) return ['label' => 'Mild', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.12)'];
            if ($score <= 14) return ['label' => 'Moderate', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.12)'];
            if ($score <= 19) return ['label' => 'Severe', 'color' => '#f97316', 'bg' => 'rgba(249, 115, 22, 0.12)'];
            return ['label' => 'Extremely Severe', 'color' => '#ef4444', 'bg' => 'rgba(239, 68, 68, 0.12)'];
        } else {
            if ($score <= 14) return ['label' => 'Normal', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.12)'];
            if ($score <= 18) return ['label' => 'Mild', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.12)'];
            if ($score <= 25) return ['label' => 'Moderate', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.12)'];
            if ($score <= 33) return ['label' => 'Severe', 'color' => '#f97316', 'bg' => 'rgba(249, 115, 22, 0.12)'];
            return ['label' => 'Extremely Severe', 'color' => '#ef4444', 'bg' => 'rgba(239, 68, 68, 0.12)'];
        }
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
