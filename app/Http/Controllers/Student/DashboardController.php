<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Appointment;
use App\Models\MoodLog;
use App\Models\Notification;
use App\Models\AssessmentScore;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Fetch stats
        $mood_logs = MoodLog::where('student_id', $user->user_id)
            ->orderBy('logged_at', 'desc')
            ->limit(7)
            ->get();

        $upcoming_appointments = Appointment::where('student_id', $user->user_id)
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['requested', 'confirmed'])
            ->with('counselor')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $latest_score = AssessmentScore::where('user_id', $user->user_id)
            ->orderBy('assessment_date', 'desc')
            ->first();

        $notifications = Notification::where('user_id', $user->user_id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data (Last 10 assessments)
        $history = AssessmentScore::where('user_id', $user->user_id)
            ->orderBy('assessment_date', 'asc')
            ->limit(10)
            ->get();
            
        $chart_labels = $history->map(fn($r) => $r->assessment_date->format('M d'));
        $chart_scores = $history->pluck('overall_score');

        // Mood Data (Last 14)
        $mood_history = MoodLog::where('student_id', $user->user_id)
            ->orderBy('logged_at', 'asc')
            ->limit(14)
            ->get();
            
        $mood_labels = $mood_history->map(fn($m) => $m->logged_at->format('M d'));
        $mood_data = $mood_history->pluck('mood_score');

        // Fetch Daily Quote from ZenQuotes (Cached for 24 hours)
        $quoteData = cache()->remember('daily_zen_quote', 86400, function () {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(3)->get('https://zenquotes.io/api/random');
                if ($response->successful()) {
                    return $response->json()[0];
                }
            } catch (\Exception $e) {
                return null;
            }
            return null;
        });

        $tip = $quoteData ? $quoteData['q'] . " — " . $quoteData['a'] : "Take a deep breath. You are doing better than you think.";

        return view('student.dashboard', compact(
            'user',
            'mood_logs',
            'upcoming_appointments',
            'latest_score',
            'notifications',
            'chart_labels',
            'chart_scores',
            'mood_labels',
            'mood_data',
            'tip',
            'history'
        ));
    }
}
