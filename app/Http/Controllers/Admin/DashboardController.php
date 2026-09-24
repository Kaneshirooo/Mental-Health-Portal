<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Appointment;
use App\Models\SessionLog;
use App\Models\AnonymousNote;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Basic Admin Stats
        $stats = [
            'total_users' => User::count(),
            'students_count' => User::where('user_type', 'student')->count(),
            'counselors_count' => User::where('user_type', 'counselor')->count(),
            'total_appointments' => Appointment::count(),
            'recent_logins' => SessionLog::with('user')
                ->orderBy('login_time', 'desc')
                ->limit(10)
                ->get(),
        ];

        // 2. Clinical Priority Queue (Shared with Counselors)
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
            ->sortByDesc(fn($u) => $u->latestAssessment->overall_score ?? 0)
            ->sortBy(function($u) {
                $level = $u->latestAssessment->risk_level ?? '';
                if ($level === 'Critical') return 0;
                if ($level === 'High') return 1;
                return 2;
            });

        // 3. Anonymous Clinical Feedback (Patient Voice)
        $anon_notes = AnonymousNote::whereIn('status', ['new', 'read', 'replied'])
            ->with(['messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Clinical Stats for Admin Overview
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

        $stats['critical_vector'] = $latestAssessmentsCount->get('High', 0) + $latestAssessmentsCount->get('Critical', 0);
        $stats['active_dialogues'] = $anon_notes->count();

        return view('admin.dashboard', compact('stats', 'priority_queue', 'anon_notes'));
    }
}
