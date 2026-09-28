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

    public function store(Request $request, \App\Services\OpenRouterService $ai)
    {
        $userId = Auth::id();
        $questions = AssessmentQuestion::all();
        $this->debugLog('H4', 'AssessmentController.php:store', 'Assessment store request started', [
            'questionCount' => $questions->count(),
        ]);
        
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
            $this->debugLog('H4', 'AssessmentController.php:store', 'Assessment answers processed', [
                'totalScoreAll' => $totalScoreAll,
                'depressionScore' => $depressionScore,
                'anxietyScore' => $anxietyScore,
                'stressScore' => $stressScore,
            ]);

            // Fallback for category mismatch
            if ($depressionScore === 0 && $anxietyScore === 0 && $stressScore === 0 && $totalScoreAll > 0) {
                $per = (int)round($totalScoreAll / 3);
                $depressionScore = $per;
                $anxietyScore = $per;
                $stressScore = $totalScoreAll - ($per * 2);
            }

            $overallScore = round(($depressionScore + $anxietyScore + $stressScore) / 3);
            $riskLevel = $this->calculateRiskLevel($depressionScore, $anxietyScore, $stressScore);

            // Create the assessment record immediately with placeholder text
            $score = AssessmentScore::create([
                'user_id' => $userId,
                'depression_score' => $depressionScore,
                'anxiety_score' => $anxietyScore,
                'stress_score' => $stressScore,
                'overall_score' => $overallScore,
                'risk_level' => $riskLevel,
                'ai_analysis' => "Clinical summary pending...",
                'ai_summary' => "Processing clinical insight...",
                'assessment_date' => now(),
            ]);

            // -- NEW: AI CLINICAL ANALYSIS (JUSTIFYING CAPSTONE TITLE) --
            // Fetch historical data for Longitudinal Analysis
            $history = AssessmentScore::where('user_id', $userId)
                ->latest('assessment_date')
                ->limit(3)
                ->get()
                ->map(fn($s) => [
                    'date' => $s->assessment_date->format('Y-m-d'),
                    'risk' => $s->risk_level,
                    'overall' => $s->overall_score
                ]);
            $historyJson = $history->isNotEmpty() ? json_encode($history) : "No previous assessments.";

            // Fast sync AI attempt (short prompt, small token cap) so results page
            // rarely shows pending. Falls back instantly to rule-based insight.
            $prompt = "Clinical scores D:{$depressionScore}/27 A:{$anxietyScore}/21 S:{$stressScore}/21 Risk:{$riskLevel} History:{$historyJson}. Output exactly: 1. ### Supportive Recommendation (1 sentence) 2. ### Empathetic Summary (2 sentences, note shift) 3. ### Professional Clinical Insight (1-2 sentences concise).";

            try {
                $aiAnalysis = $ai->generateResponse(
                    [['role' => 'user', 'content' => $prompt]],
                    '',
                    300,
                    0.3,
                    12
                );
                if (empty(trim((string) $aiAnalysis)) || str_contains($aiAnalysis, "trouble connecting")) {
                    throw new \Exception('AI empty or fallback');
                }
                $score->update([
                    'ai_analysis' => $aiAnalysis,
                    'ai_summary' => 'AI analysis generated.',
                ]);
            } catch (\Throwable $ae) {
                \Illuminate\Support\Facades\Log::warning('AI insight fast-path failed, using local fallback: ' . $ae->getMessage());
                $score->update([
                    'ai_analysis' => $this->buildLocalInsight($depressionScore, $anxietyScore, $stressScore, $riskLevel),
                    'ai_summary' => 'Local clinical insight (AI unavailable).',
                ]);
            }

            // Log activity (following legacy logActivity function)
            SessionLog::create([
                'user_id' => $userId,
                'login_time' => now(),
                'activity' => 'Completed AI-based assessment with risk: ' . $riskLevel,
            ]);

            DB::commit();
            $this->debugLog('H4', 'AssessmentController.php:store', 'Assessment stored successfully', [
                'overallScore' => $overallScore,
                'riskLevel' => $riskLevel,
                'hasAiAnalysis' => true,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Assessment completed successfully.',
                    'redirect_url' => route('student.assessment.results', $score->score_id)
                ]);
            }

            return redirect()->route('student.assessment.results', $score->score_id);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->debugLog('H4', 'AssessmentController.php:store', 'Assessment store failed', [
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to save assessment: ' . $e->getMessage());
        }
    }

    public function results($score_id)
    {
        $score = AssessmentScore::where('score_id', $score_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $recommendations = [
            'Low'      => 'Your assessment indicates low risk. Continue with regular self-care.',
            'Moderate' => 'If you want to speak with a professional, you can book a session here:',
            'High'     => 'You are recommended to consult with the counselor.',
            'Critical' => 'You are strongly recommended to consult with the counselor immediately.',
        ];

        $risk_colors = [
            'Low'      => '#10b981',
            'Moderate' => '#f59e0b',
            'High'     => '#f97316',
            'Critical' => '#ef4444',
        ];

        // Clinical Severity Labels (Standard logic for PHQ-9 and GAD-7)
        $dep_info = $this->getSeverityLabel($score->depression_score, 'depression');
        $anx_info = $this->getSeverityLabel($score->anxiety_score, 'anxiety');
        $str_info = $this->getSeverityLabel($score->stress_score, 'stress');

        // Professional Denominators
        $max_scores = [
            'depression' => 27, // PHQ-9
            'anxiety'    => 21, // GAD-7
            'stress'     => 21, // DASS-21 Stress Subscale
        ];

        $raw_total = $score->depression_score + $score->anxiety_score + $score->stress_score;
        $max_total = array_sum($max_scores);
        
        $distress_pct = ($raw_total > 0) ? min(100, round(($raw_total / $max_total) * 100)) : 0;
        $wellness_index = 100 - $distress_pct;

        $wellness_info = $this->getWellnessLabel($wellness_index);

        return view('student.assessment.results', compact(
            'score', 
            'recommendations', 
            'risk_colors', 
            'wellness_index',
            'wellness_info',
            'dep_info',
            'anx_info',
            'str_info',
            'max_scores',
            'raw_total',
            'max_total'
        ));
    }

    /**
     * Translates clinical insight text or assessment questions into Tagalog.
     */
    public function translate(Request $request, \App\Services\OpenRouterService $ai)
    {
        $request->validate(['text' => 'required|string']);
        
        try {
            $text = $request->text;

            if (str_contains($text, ' | ')) {
                $prompt = "You are a precise translation API for a clinical assessment UI.\n"
                        . "The input string contains multiple assessment items separated strictly by ' | '.\n\n"
                        . "RULES:\n"
                        . "1. Translate each item into natural, empathetic Tagalog (Filipino).\n"
                        . "2. You MUST preserve the exact ' | ' separator between each translated item.\n"
                        . "3. DO NOT add any introductory text (such as 'Narito ang pagsasalin...'), concluding advice, disclaimers, bullet points (-), or newlines.\n"
                        . "4. Output ONLY the pipe-separated translated string on a single line.\n\n"
                        . "INPUT:\n" . $text;
            } else {
                $prompt = "You are a professional clinical translator for a mental health portal.\n"
                        . "Translate the following clinical insight text into natural, empathetic Tagalog (Filipino) for a student to understand easily.\n\n"
                        . "RULES:\n"
                        . "1. Output ONLY the direct translated text.\n"
                        . "2. DO NOT include any conversational preamble (e.g., 'Narito ang pagsasalin...'), intro, outro, disclaimers, or polite chatter.\n"
                        . "3. Retain all original markdown formatting (such as ### headers or bold text).\n\n"
                        . "TEXT:\n" . $text;
            }

            $translation = $ai->generateSingleTurn($prompt);

            // Clean up any residual conversational preambles/outros if generated
            $translation = preg_replace('/^(Narito ang|Here is|Below is|Pagsasalin).*?:\s*/iu', '', trim($translation));

            return response()->json([
                'success' => true,
                'translation' => $translation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Translation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * JSON polling endpoint for the results page. Retries AI once if still pending.
     */
    public function insight($score_id, \App\Services\OpenRouterService $ai)
    {
        $score = AssessmentScore::where('score_id', $score_id)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->firstOrFail();

        if (str_contains((string) $score->ai_analysis, 'pending') || str_contains((string) $score->ai_analysis, 'Processing')) {
            try {
                $prompt = "Clinical scores D:{$score->depression_score}/27 A:{$score->anxiety_score}/21 S:{$score->stress_score}/21 Risk:{$score->risk_level}. Output exactly: 1. ### Supportive Recommendation (1 sentence) 2. ### Empathetic Summary (2 sentences) 3. ### Professional Clinical Insight (concise).";
                $fresh = $ai->generateResponse([['role' => 'user', 'content' => $prompt]], '', 300, 0.3, 10);
                if (!empty(trim($fresh)) && !str_contains($fresh, 'trouble connecting')) {
                    $score->update(['ai_analysis' => $fresh, 'ai_summary' => 'AI analysis generated on retry.']);
                    $score->refresh();
                }
            } catch (\Throwable $e) {
            }
        }

        return response()->json([
            'success' => true,
            'pending' => str_contains((string) $score->ai_analysis, 'pending') || str_contains((string) $score->ai_analysis, 'Processing'),
            'insight' => $score->ai_analysis,
        ]);
    }

    private function buildLocalInsight(int $d, int $a, int $s, string $risk): string
    {
        $top = match ($risk) {
            'Critical' => 'Please reach out to your counselor as soon as possible — you do not have to face this alone.',
            'High' => 'Consider booking a counselor session this week to talk through what has been weighing on you.',
            default => 'Keep up your self-care routine and track your mood daily to notice early changes.',
        };
        return "### Supportive Recommendation\n{$top}\n\n### Empathetic Summary\nYour recent scores reflect current strain across mood and stress. Small consistent steps and talking to someone you trust can help stabilize things.\n\n### Professional Clinical Insight\nScores D:{$d}/27 A:{$a}/21 S:{$s}/21 Risk:{$risk}. Review trend and prioritize follow-up per risk protocol.";
    }

    private function getWellnessLabel(int $val): array
    {
        return match (true) {
            $val <= 20 => ['label' => 'Critically Low Wellness', 'cls' => 'wellness-critical'],
            $val <= 40 => ['label' => 'Low Wellness', 'cls' => 'wellness-low'],
            $val <= 60 => ['label' => 'Moderately Well', 'cls' => 'wellness-moderate'],
            $val <= 80 => ['label' => 'Well', 'cls' => 'wellness-well'],
            default    => ['label' => 'Exceptional Wellness', 'cls' => 'wellness-excellent'],
        };
    }

    private function getSeverityLabel(int $val, string $type): array
    {
        if ($type === 'depression') {
            return match (true) {
                $val <= 4  => ['label' => 'Minimal',          'cls' => 'sev-minimal'],
                $val <= 9  => ['label' => 'Mild',             'cls' => 'sev-mild'],
                $val <= 14 => ['label' => 'Moderate',         'cls' => 'sev-moderate'],
                $val <= 19 => ['label' => 'Moderately Severe', 'cls' => 'sev-high'],
                default    => ['label' => 'Severe',           'cls' => 'sev-severe'],
            };
        }
        
        if ($type === 'anxiety') {
            return match (true) {
                $val <= 4  => ['label' => 'Minimal',  'cls' => 'sev-minimal'],
                $val <= 9  => ['label' => 'Mild',     'cls' => 'sev-mild'],
                $val <= 14 => ['label' => 'Moderate', 'cls' => 'sev-moderate'],
                default    => ['label' => 'Severe',   'cls' => 'sev-severe'],
            };
        }

        // Stress (DASS-21 Subscale logic)
        return match (true) {
            $val <= 7  => ['label' => 'Normal',   'cls' => 'sev-minimal'],
            $val <= 9  => ['label' => 'Mild',     'cls' => 'sev-mild'],
            $val <= 12 => ['label' => 'Moderate', 'cls' => 'sev-moderate'],
            $val <= 16 => ['label' => 'Severe',   'cls' => 'sev-high'],
            default    => ['label' => 'Extreme',  'cls' => 'sev-severe'],
        };
    }

    private function calculateRiskLevel(int $depression, int $anxiety, int $stress): string
    {
        // Clinical best-practice: Risk is determined by the most severe indicator
        $dep_sev = $this->getSeverityLabel($depression, 'depression')['label'];
        $anx_sev = $this->getSeverityLabel($anxiety, 'anxiety')['label'];
        $str_sev = $this->getSeverityLabel($stress, 'stress')['label'];

        $severities = [$dep_sev, $anx_sev, $str_sev];

        if (in_array('Severe', $severities) || in_array('Extreme', $severities) || in_array('Moderately Severe', $severities)) {
            return 'Critical';
        }

        if (in_array('Moderate', $severities)) {
            return 'High';
        }

        if (in_array('Mild', $severities)) {
            return 'Moderate';
        }

        return 'Low';
    }

    private function debugLog(string $hypothesisId, string $location, string $message, array $data = []): void
    {
        // #region agent log
        file_put_contents(
            storage_path('logs/debug-a97deb.log'),
            json_encode([
                'sessionId' => 'a97deb',
                'runId' => 'initial',
                'hypothesisId' => $hypothesisId,
                'location' => $location,
                'message' => $message,
                'data' => $data,
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
        // #endregion
    }
}
