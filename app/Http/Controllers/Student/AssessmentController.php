<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentScore;
use App\Models\StudentResponse;
use App\Models\SessionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    public function index()
    {
        $questions = AssessmentQuestion::orderBy('question_number')->get();
        $categories = $questions->pluck('category')->unique()->values();
        
        return view('student.assessment', compact('questions', 'categories'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $questions = AssessmentQuestion::all();
        
        $depressionScore = 0;
        $anxietyScore = 0;
        $stressScore = 0;
        $totalScoreAll = 0;

        DB::beginTransaction();
        try {
            foreach ($questions as $question) {
                $key = 'q_' . $question->question_id;
                if ($request->has($key)) {
                    $val = (int)$request->input($key);
                    $totalScoreAll += $val;

                    // Save individual response
                    StudentResponse::create([
                        'user_id' => $userId,
                        'question_id' => $question->question_id,
                        'response_value' => $val,
                        'assessment_date' => now(),
                    ]);

                    $cat = strtolower(trim($question->category));
                    if ($cat === 'depression') $depressionScore += $val;
                    elseif ($cat === 'anxiety') $anxietyScore += $val;
                    elseif ($cat === 'stress') $stressScore += $val;
                }
            }

            // Fallback for category mismatch
            if ($depressionScore === 0 && $anxietyScore === 0 && $stressScore === 0 && $totalScoreAll > 0) {
                $per = (int)round($totalScoreAll / 3);
                $depressionScore = $per;
                $anxietyScore = $per;
                $stressScore = $totalScoreAll - ($per * 2);
            }

            $overallScore = round(($depressionScore + $anxietyScore + $stressScore) / 3);
            $riskLevel = $this->calculateRiskLevel($depressionScore, $anxietyScore, $stressScore);

            $score = AssessmentScore::create([
                'user_id' => $userId,
                'depression_score' => $depressionScore,
                'anxiety_score' => $anxietyScore,
                'stress_score' => $stressScore,
                'overall_score' => $overallScore,
                'risk_level' => $riskLevel,
                'assessment_date' => now(),
            ]);

            // Log activity (following legacy logActivity function)
            SessionLog::create([
                'user_id' => $userId,
                'login_time' => now(),
                'activity' => 'Completed assessment with score ' . $overallScore,
            ]);

            DB::commit();
            return redirect()->route('student.assessment.results', $score->score_id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save assessment: ' . $e->getMessage());
        }
    }

    public function results($score_id)
    {
        $score = AssessmentScore::where('score_id', $score_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $recommendations = [
            'Low'      => 'Your assessment indicates low risk. Continue with regular self-care and healthy habits.',
            'Moderate' => 'Your assessment indicates moderate risk. Consider speaking with a counselor for guidance.',
            'High'     => 'Your assessment indicates high risk. We recommend scheduling a session with a counselor.',
            'Critical' => 'Your assessment indicates critical risk. Please contact a counselor immediately.',
        ];

        $risk_colors = [
            'Low'      => '#10b981',
            'Moderate' => '#f59e0b',
            'High'     => '#f97316',
            'Critical' => '#ef4444',
        ];

        $dep_info = $this->getSeverityLabel($score->depression_score);
        $anx_info = $this->getSeverityLabel($score->anxiety_score);
        $str_info = $this->getSeverityLabel($score->stress_score);

        $raw_total = $score->depression_score + $score->anxiety_score + $score->stress_score;
        $display_score = ($raw_total > 0 || $score->overall_score > 0)
            ? min(100, round(($raw_total / 60) * 100))
            : 0;

        return view('student.assessment.results', compact(
            'score', 
            'recommendations', 
            'risk_colors', 
            'display_score',
            'dep_info',
            'anx_info',
            'str_info'
        ));
    }

    private function getSeverityLabel(int $val): array
    {
        return match (true) {
            $val < 5  => ['label' => 'Minimal',  'cls' => 'sev-minimal'],
            $val < 10 => ['label' => 'Mild',     'cls' => 'sev-mild'],
            $val < 15 => ['label' => 'Moderate', 'cls' => 'sev-moderate'],
            default   => ['label' => 'Severe',   'cls' => 'sev-severe'],
        };
    }

    private function calculateRiskLevel(int $depression, int $anxiety, int $stress): string
    {
        $avg = ($depression + $anxiety + $stress) / 3;

        return match (true) {
            $avg >= 16 => 'Critical',
            $avg >= 12 => 'High',
            $avg >= 8  => 'Moderate',
            default    => 'Low',
        };
    }
}
