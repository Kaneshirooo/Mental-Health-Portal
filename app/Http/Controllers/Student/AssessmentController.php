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
                'ai_analysis' => "Your summary is getting ready...",
                'ai_summary' => "Getting your summary ready...",
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
        $prompt = "You are a kind friend writing for a student. Check-in scores — low mood {$depressionScore}/27, worries {$anxietyScore}/21, pressure {$stressScore}/21, overall feeling: {$riskLevel}. Past check-ins: {$historyJson}. Write in very simple everyday words, no clinical or doctor words. Output exactly: 1. ### A gentle next step (1 sentence) 2. ### What I see in your answers (2 sentences, kindly note if days got heavier or lighter) 3. ### Something to remember (1-2 short kind sentences).";

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
                    'ai_summary' => 'Summary ready.',
                ]);
            } catch (\Throwable $ae) {
                \Illuminate\Support\Facades\Log::warning('AI insight fast-path failed, using local fallback: ' . $ae->getMessage());
                $score->update([
                    'ai_analysis' => $this->buildLocalInsight($depressionScore, $anxietyScore, $stressScore, $riskLevel),
                    'ai_summary' => 'Friendly summary ready (made on this device).',
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
            'Low'      => "You're doing okay overall. Keep doing the small things that help you feel steady.",
            'Moderate' => "You've had some ups and downs lately. If you'd like, talking with your counselor can help lighten things a little.",
            'High'     => "Things have felt heavy lately. It would really help to spend some time with your counselor soon.",
            'Critical' => "Things feel really heavy right now. Please reach out to your counselor as soon as you can — you don't have to go through this alone.",
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
     * Translates check-in summary text or questions into Tagalog.
     */
    public function translate(Request $request, \App\Services\OpenRouterService $ai)
    {
        $request->validate(['text' => 'required|string']);
        
        try {
            $text = $request->text;

            if (str_contains($text, ' | ')) {
                $prompt = "You are a precise translation helper for a student check-in page.\n"
                        . "The input string contains multiple check-in questions separated strictly by ' | '.\n\n"
                        . "RULES:\n"
                        . "1. Translate each item into natural, warm Tagalog (Filipino) a student can easily understand.\n"
                        . "2. You MUST preserve the exact ' | ' separator between each translated item.\n"
                        . "3. DO NOT add any introductory text (such as 'Narito ang pagsasalin...'), concluding advice, disclaimers, bullet points (-), or newlines.\n"
                        . "4. Output ONLY the pipe-separated translated string on a single line.\n\n"
                        . "INPUT:\n" . $text;
            } else {
                $prompt = "You are a kind translator for a student support page.\n"
                        . "Translate the following friendly summary into natural, warm Tagalog (Filipino) that is easy for a student to understand.\n\n"
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
                $prompt = "You are a kind friend writing for a student. Check-in scores — low mood {$score->depression_score}/27, worries {$score->anxiety_score}/21, pressure {$score->stress_score}/21, overall: {$score->risk_level}. Use very simple everyday words, no clinical words. Output exactly: 1. ### A gentle next step (1 sentence) 2. ### What I see in your answers (2 sentences) 3. ### Something to remember (concise).";
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
            'Critical' => 'Things sound really heavy right now — please reach out to your counselor soon. You deserve care and you are not alone.',
            'High' => 'It might really help to set a time to talk with your counselor this week about what has been weighing on you.',
            default => 'Keep doing small kind things for yourself each day, and jot down your feelings so you can see your brighter days too.',
        };
        return "### A gentle next step\n{$top}\n\n### What I see in your answers\nYour recent check-in shows you've had some tougher days with mood and pressure. Small steady steps and talking to someone you trust can help days feel lighter.\n\n### Something to remember\nYou've been carrying a lot lately, and it makes sense to feel tired. Be gentle with yourself and reach out whenever you need a hand.";
    }

    private function getWellnessLabel(int $val): array
    {
        return match (true) {
            $val <= 20 => ['label' => 'Having a Really Tough Time', 'cls' => 'wellness-critical'],
            $val <= 40 => ['label' => 'Going Through a Tough Patch', 'cls' => 'wellness-low'],
            $val <= 60 => ['label' => 'Doing Okay, Ups and Downs', 'cls' => 'wellness-moderate'],
            $val <= 80 => ['label' => 'Feeling Good', 'cls' => 'wellness-well'],
            default    => ['label' => 'Feeling Bright', 'cls' => 'wellness-excellent'],
        };
    }

    private function getSeverityLabel(int $val, string $type): array
    {
        if ($type === 'depression') {
            return match (true) {
                $val <= 4  => ['label' => 'Mostly Okay',          'cls' => 'sev-minimal'],
                $val <= 9  => ['label' => 'A Little Low',             'cls' => 'sev-mild'],
                $val <= 14 => ['label' => 'Feeling Low', 'cls' => 'sev-moderate'],
                $val <= 19 => ['label' => 'Really Low', 'cls' => 'sev-high'],
                default    => ['label' => 'Very Heavy',           'cls' => 'sev-severe'],
            };
        }
        
        if ($type === 'anxiety') {
            return match (true) {
                $val <= 4  => ['label' => 'Mostly Calm',  'cls' => 'sev-minimal'],
                $val <= 9  => ['label' => 'A Bit Worried',     'cls' => 'sev-mild'],
                $val <= 14 => ['label' => 'Quite Worried', 'cls' => 'sev-moderate'],
                default    => ['label' => 'Very Worried',   'cls' => 'sev-severe'],
            };
        }

        // Pressure levels — everyday words
        return match (true) {
            $val <= 7  => ['label' => 'Feeling Calm',   'cls' => 'sev-minimal'],
            $val <= 9  => ['label' => 'A Bit Pressured',     'cls' => 'sev-mild'],
            $val <= 12 => ['label' => 'Feeling Pressured', 'cls' => 'sev-moderate'],
            $val <= 16 => ['label' => 'Really Pressured',   'cls' => 'sev-high'],
            default    => ['label' => 'Overloaded',  'cls' => 'sev-severe'],
        };
    }

    private function calculateRiskLevel(int $depression, int $anxiety, int $stress): string
    {
        // Risk is set by the heaviest score. Uses raw numbers so friendly labels can't break it.
        // Cutoffs match PHQ-9 / GAD-7 / DASS-stress standards.
        $isCritical = ($depression >= 15) || ($anxiety >= 15) || ($stress >= 13);
        if ($isCritical) {
            return 'Critical';
        }

        $isHigh = ($depression >= 10 && $depression <= 14) || ($anxiety >= 10 && $anxiety <= 14) || ($stress >= 10 && $stress <= 12);
        if ($isHigh) {
            return 'High';
        }

        $isModerate = ($depression >= 5 && $depression <= 9) || ($anxiety >= 5 && $anxiety <= 9) || ($stress >= 8 && $stress <= 9);
        if ($isModerate) {
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
