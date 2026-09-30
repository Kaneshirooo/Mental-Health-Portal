<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AnonymousNoteMessage;
use App\Models\ChatHistory;
use App\Models\ChatConversation;
use App\Models\MoodLog;
use App\Models\AssessmentScore;
use App\Services\OpenRouterService;
use App\Services\PerspectiveService;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    protected $openRouter;
    protected $perspective;
    protected $activity;

    public function __construct(OpenRouterService $openRouter, PerspectiveService $perspective, ActivityService $activity)
    {
        $this->openRouter = $openRouter;
        $this->perspective = $perspective;
        $this->activity = $activity;
    }

    public function index()
    {
        $studentId = Auth::id();
        $conversations = ChatConversation::where('student_id', $studentId)
            ->orderBy('updated_at', 'desc')
            ->get();

        $activeConversationId = request()->integer('conversation');
        if (!$activeConversationId || !$conversations->contains('conversation_id', $activeConversationId)) {
            $activeConversationId = optional($conversations->first())->conversation_id;
        }

        $chat_history = collect();
        if ($activeConversationId) {
            $chat_history = ChatHistory::where('student_id', $studentId)
                ->where('conversation_id', $activeConversationId)
                ->orderBy('history_id', 'asc')
                ->get();
        }

        return view('student.chat', compact('chat_history', 'conversations', 'activeConversationId'));
    }

    public function startConversation()
    {
        $studentId = Auth::id();
        $conversation = ChatConversation::create([
            'student_id' => $studentId,
            'title' => 'New Conversation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Generate the initial smart greeting
        $this->initiateAriaGreeting($studentId, $conversation->conversation_id);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->conversation_id,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|integer',
        ]);

        $student_id = Auth::id();
        $user = Auth::user();
        $userMessage = $request->input('message');
        $conversationId = $this->resolveConversationId($student_id, $request->input('conversation_id'));
        $this->debugLog('H3', 'AiChatController.php:sendMessage', 'Received chat message request', [
            'messageLength' => strlen($userMessage),
            'conversationId' => $conversationId,
        ]);

        // 1. Safety Scan (Perspective API)
        $safetyReport = $this->perspective->analyzeText($userMessage);
        $safetyAlert = "";
        
        if (!$safetyReport['is_safe']) {
            $safetyAlert = "\n\n[SYSTEM SECURITY ALERT: The user message has been flagged for potential self-harm or high risk. Prioritize safety: be extremely supportive, validate their struggle, and explicitly suggest contacting a human counselor or a crisis hotline immediately. Avoid generic advice.]";
            
            // Log for Clinical Review
            Log::alert("Aria Safety Alert for User #{$user->user_id}: High toxicity/self-harm risk detected in chat.");
        }

        // 2. Proactive Activity Suggestion
        if (preg_match('/(bored|nothing to do|i\'m stuck|suggestion|help me do|activity)/i', $userMessage)) {
            $activitySuggestion = $this->activity->getRandomActivity();
            $safetyAlert .= "\n\n[SYSTEM SUGGESTION: Here is a real-world wellness activity you can suggest if appropriate: {$activitySuggestion}]";
        }

        // Save user message
        ChatHistory::create([
            'student_id' => $student_id,
            'conversation_id' => $conversationId,
            'sender' => 'user',
            'message' => $userMessage,
            'created_at' => now(),
        ]);

        // Get past context (last 10 messages)
        $history = ChatHistory::where('student_id', $student_id)
            ->where('conversation_id', $conversationId)
            ->orderBy('history_id', 'desc')
            ->limit(10)
            ->get()
            ->reverse();
        $this->debugLog('H6', 'AiChatController.php:sendMessage', 'Loaded context history', [
            'historyIds' => $history->pluck('history_id')->values()->all(),
            'historyCount' => $history->count(),
        ]);
        $recentAriaQuestionStems = $history
            ->where('sender', 'aria')
            ->take(-2)
            ->map(fn ($item) => $this->extractQuestionStem((string) $item->message))
            ->filter()
            ->values()
            ->all();
        $this->debugLog('H7', 'AiChatController.php:sendMessage', 'Recent Aria question stems', [
            'recentQuestionStems' => $recentAriaQuestionStems,
            'latestUserLength' => strlen($userMessage),
        ]);

        $messages = [];
        $firstUserFound = false;
        foreach ($history as $h) {
            $role = ($h->sender === 'user' ? 'user' : 'model');
            if (!$firstUserFound && $role !== 'user') {
                continue;
            }
            $firstUserFound = true;
            $messages[] = ['role' => $role, 'parts' => [['text' => $h->message]]];
        }
        $recentUserTurns = $history
            ->where('sender', 'user')
            ->take(-2)
            ->pluck('message')
            ->values()
            ->all();

        $detectedEmotion = $this->detectEmotion($userMessage);

        $conversationHistoryText = $this->formatConversationHistory($history);
        $wellnessContext = $this->buildStudentWellnessContext($student_id);

        $systemPrompt = $this->buildSystemPrompt(
            $user->full_name,
            $detectedEmotion,
            $conversationHistoryText,
            $userMessage,
            $recentAriaQuestionStems,
            $recentUserTurns,
            $safetyAlert,
            $wellnessContext
        );

        $ariaResponse = $this->openRouter->generateResponse($messages, $systemPrompt);
        $this->debugLog('H3', 'AiChatController.php:sendMessage', 'Gemini response generated', [
            'responseLength' => strlen((string) $ariaResponse),
            'isSafe' => $safetyReport['is_safe'] ?? null,
        ]);
        $newQuestionStem = $this->extractQuestionStem((string) $ariaResponse);
        $isRepeatedQuestion = $newQuestionStem !== '' && in_array($newQuestionStem, $recentAriaQuestionStems, true);
        
        if ($isRepeatedQuestion) {
            $this->debugLog('H7', 'AiChatController.php:sendMessage', 'Repetition detected! Triggering one-time smart retry.', [
                'repeatedStem' => $newQuestionStem
            ]);
            $retryInstruction = "\n\nCRITICAL: Your last response was too similar to a question you already asked recently ('{$newQuestionStem}'). Please provide a different follow-up that explores a NEW angle of the student's experience.";
            $ariaResponse = $this->openRouter->generateResponse($messages, $systemPrompt . $retryInstruction);
        }

        $this->debugLog('H7', 'AiChatController.php:sendMessage', 'Final response check', [
            'newQuestionStem' => $newQuestionStem,
            'isRepeatedQuestion' => $isRepeatedQuestion,
        ]);

        // -- CLINICAL SAFETY FALLBACK --
        // If Gemini is blocked/errored, or if our local Perspective scan (if enabled) finds high risk
        if (empty($ariaResponse) || strlen($ariaResponse) < 2) {
            Log::warning("Aria triggered a Safety Fallback for User #{$student_id}. Use localized response.");
            $ariaResponse = $this->openRouter->generateResponse($messages, $systemPrompt); // Simplified fallback
        }

        // Save Aria's response
        ChatHistory::create([
            'student_id' => $student_id,
            'conversation_id' => $conversationId,
            'sender' => 'aria',
            'message' => $ariaResponse,
            'created_at' => now(),
        ]);
        ChatConversation::where('conversation_id', $conversationId)->update([
            'updated_at' => now(),
            'title' => $this->buildConversationTitle($conversationId),
        ]);

        return response()->json([
            'success' => true,
            'message' => $ariaResponse,
            'conversation_id' => $conversationId,
        ]);
    }

    public function generatePreAssessment(Request $request)
    {
        $request->validate([
            'transcript' => 'required|string',
            'form' => 'required|array',
        ]);

        $student_id = Auth::id();
        $transcript = $request->input('transcript');
        $form = $request->input('form');
        $user = Auth::user();
        $name = explode(' ', $user->full_name)[0];
        $this->debugLog('H5', 'AiChatController.php:generatePreAssessment', 'Received pre-assessment generation request', [
            'transcriptLength' => strlen($transcript),
            'hasMainConcern' => !empty($form['main_concern'] ?? ''),
            'stressLevel' => $form['stress_level'] ?? null,
            'sleepQuality' => $form['sleep_quality'] ?? null,
        ]);

        $systemInstruction = "You are a professional counselor assistant. Analyze the student's self-report and the conversation transcript to generate a JSON report. "
            . "The JSON MUST contain: 'mood' (string), 'energy' (string), 'focus' (string), 'social' (string), 'appetite' (string), 'sleep' (string), 'risk_level' (Low/Medium/High/Critical), 'core_concerns' (text), 'clinical_observations' (text), 'follow_up_needed' (boolean). "
            . "Do not include any text other than the JSON object.";

        $messages = [
            ['role' => 'user', 'parts' => [['text' => "STUDENT: {$name}\n\nREPORT: " . json_encode($form) . "\n\nTRANSCRIPT: {$transcript} "]]]
        ];

        // Fetch historical data for Longitudinal Analysis
        $history = \App\Models\AssessmentScore::where('user_id', $student_id)
            ->latest('assessment_date')
            ->limit(3)
            ->get()
            ->map(fn($s) => [
                'date' => $s->assessment_date->format('Y-m-d'),
                'risk' => $s->risk_level,
                'overall' => $s->overall_score
            ]);

        if ($history->isNotEmpty()) {
            $historyJson = json_encode($history);
            $systemInstruction .= " IMPORTANT: Compare the current report with this student's historical trends: {$historyJson}. If there is a significant decline (e.g. Low to High risk), explicitly mention this 'Clinical Shift' in the 'clinical_observations'.";
        }

        try {
            $response = $this->openRouter->generateResponse($messages, $systemInstruction);
            
            // Strip markdown code fences Gemini sometimes wraps around JSON
            $cleaned = preg_replace('/^```(?:json)?\s*/im', '', $response);
            $cleaned = preg_replace('/```$/m', '', $cleaned);
            $cleaned = trim($cleaned);
            
            // If the response contains extra text before/after JSON, extract the JSON block
            if (preg_match('/\{(?:[^{}]|(?R))*\}/', $cleaned, $matches)) {
                $cleaned = $matches[0];
            }
            
            $report = json_decode($cleaned, true);

            if (!$report || !is_array($report) || !isset($report['risk_level'])) {
                throw new \Exception("Invalid AI response format or missing risk_level");
            }
            
            // Ensure all required keys exist to avoid UI errors
            $requiredKeys = ['mood', 'energy', 'focus', 'social', 'appetite', 'sleep', 'risk_level', 'core_concerns', 'clinical_observations', 'follow_up_needed'];
            foreach ($requiredKeys as $key) {
                if (!isset($report[$key])) {
                    $report[$key] = ($key === 'follow_up_needed') ? false : 'Not provided';
                }
            }
            $this->debugLog('H5', 'AiChatController.php:generatePreAssessment', 'Pre-assessment AI report parsed', [
                'usedFallback' => false,
                'riskLevel' => $report['risk_level'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::warning("PreAssessment AI fallback triggered: " . $e->getMessage());
            // Fallback logic
            $report = $this->localPreAssessmentFallback($transcript, $form);
            $this->debugLog('H5', 'AiChatController.php:generatePreAssessment', 'Pre-assessment fallback used', [
                'usedFallback' => true,
                'error' => $e->getMessage(),
                'riskLevel' => $report['risk_level'] ?? null,
            ]);
        }

        $preAssessment = \App\Models\AiPreassessment::create([
            'student_id' => $student_id,
            'conversation_transcript' => $transcript,
            'form_answers' => $form,
            'ai_report' => $report,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'pre_id' => $preAssessment->pre_id,
            'report' => $report,
        ]);
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

    private function localPreAssessmentFallback($transcript, $form)
    {
        $t = strtolower($transcript);
        $stress = (int)($form['stress_level'] ?? 5);
        $sleep  = (int)($form['sleep_quality'] ?? 3);
        $mood   = $form['mood_now'] ?? 'neutral';

        $risk = 'Low';
        if (str_contains($t, 'hurt myself') || str_contains($t, 'suicid') || str_contains($t, 'want to die')) {
            $risk = 'Critical';
        } elseif ($stress >= 8 || $sleep <= 2) {
            $risk = 'High';
        } elseif ($stress >= 6 || $sleep <= 3) {
            $risk = 'Medium';
        }

        $sleepLabel = match (true) {
            $sleep >= 5 => 'Excellent',
            $sleep == 4 => 'Good',
            $sleep == 3 => 'Fair',
            $sleep == 2 => 'Poor',
            default     => 'Very Poor',
        };

        $energyLevel = match (true) {
            $stress <= 3 => 'High',
            $stress <= 6 => 'Moderate',
            default      => 'Low',
        };

        $mainConcern = $form['main_concern'] ?? '';

        return [
            'mood'                  => $mood === 'positive' ? 'positive' : ($mood === 'low' ? 'low' : ($mood === 'concerning' ? 'concerning' : 'neutral')),
            'energy'                => $energyLevel,
            'focus'                 => $stress >= 7 ? 'A bit scattered' : 'Okay',
            'social'                => 'Not shared yet',
            'appetite'              => 'Not shared yet',
            'sleep'                 => $sleepLabel,
            'risk_level'            => $risk,
            'core_concerns'         => $mainConcern ?: 'Feeling pressured and having restless sleep lately.',
            'clinical_observations' => 'A short, kind note made from your chat and check-in. Your counselor can read it to support you better.',
            'follow_up_needed'      => in_array($risk, ['High', 'Critical']),
        ];
    }

    private function resolveConversationId(int $studentId, $requestedConversationId): int
    {
        if ($requestedConversationId) {
            $conversation = ChatConversation::where('student_id', $studentId)
                ->where('conversation_id', (int) $requestedConversationId)
                ->first();
            if ($conversation) {
                return (int) $conversation->conversation_id;
            }
        }

        $existing = ChatConversation::where('student_id', $studentId)
            ->orderBy('updated_at', 'desc')
            ->first();
        if ($existing) {
            return (int) $existing->conversation_id;
        }

        $new = ChatConversation::create([
            'student_id' => $studentId,
            'title' => 'New Conversation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->initiateAriaGreeting($studentId, $new->conversation_id);

        return (int) $new->conversation_id;
    }

    private function initiateAriaGreeting(int $studentId, int $conversationId): void
    {
        $user = Auth::user();
        $firstName = explode(' ', $user->full_name)[0];

        // Friendly first hello — simple words, no forms or scores.
        $ariaGreeting = "Hi {$firstName}! Thanks for dropping by. I'm really glad you're here. 🌿\n\n"
            . "This is just a safe space to talk about whatever's on your mind — school, friends, family, or just how today feels.\n\n"
            . "No need for perfect words. How are you feeling right now, in your own words?";

        try {
            // Save the first Aria message
            ChatHistory::create([
                'student_id' => $studentId,
                'conversation_id' => $conversationId,
                'sender' => 'aria',
                'message' => $ariaGreeting,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Aria Initiation Error: " . $e->getMessage());
        }
    }

    private function buildConversationTitle(int $conversationId): string
    {
        $firstUserMessage = ChatHistory::where('conversation_id', $conversationId)
            ->where('sender', 'user')
            ->where('message', 'not like', '[System:%')
            ->orderBy('history_id', 'asc')
            ->value('message');

        if (!$firstUserMessage) {
            return 'New Conversation';
        }

        $title = trim(preg_replace('/\s+/', ' ', $firstUserMessage));
        return mb_substr($title, 0, 60);
    }

    /**
     * Summarize recent Mood Journal entries and the student's own Quick Note messages for chat context.
     * Counselor replies are excluded so the model focuses on the student's own words.
     */
    private function buildStudentWellnessContext(int $studentId): string
    {
        $lines = [];

        // 1. Latest AI-Based Assessment Results
        $latestScore = AssessmentScore::where('user_id', $studentId)
            ->orderByDesc('assessment_date')
            ->first();

        if ($latestScore) {
            $date = $latestScore->assessment_date ? $latestScore->assessment_date->format('Y-m-d') : '?';
            $lines[] = "Latest Check-In ({$date}):";
            $lines[] = "  - How they were doing: {$latestScore->risk_level}";
            $lines[] = "  - Scores: Low mood ({$latestScore->depression_score}/27), Worries ({$latestScore->anxiety_score}/21), Pressure ({$latestScore->stress_score}/21)";
            if ($latestScore->ai_summary) {
                $lines[] = "  - Past summary: " . $this->truncateForPrompt((string) $latestScore->ai_summary, 200);
            }
            $lines[] = ""; // Spacer
        } else {
            $lines[] = "Latest Check-In: (no check-in done yet)";
            $lines[] = ""; // Spacer
        }

        $moodLogs = MoodLog::where('student_id', $studentId)
            ->orderByDesc('logged_at')
            ->limit(14)
            ->get();

        if ($moodLogs->isEmpty()) {
            $lines[] = 'Mood Journal: (no entries yet)';
        } else {
            $lines[] = 'Mood Journal (newest first):';
            foreach ($moodLogs as $log) {
                $date = $log->logged_at ? $log->logged_at->format('Y-m-d') : '?';
                $note = $log->note ? $this->truncateForPrompt((string) $log->note, 200) : '';
                $sentiment = $log->sentiment_label ? " | sentiment: {$log->sentiment_label}" : '';
                $lines[] = "  - {$date}: mood {$log->mood_score}/5 {$log->mood_emoji}{$sentiment}"
                    . ($note !== '' ? " | note: {$note}" : '');
            }
        }

        $quickNotes = AnonymousNoteMessage::query()
            ->where('sender_type', 'student')
            ->whereHas('note', fn ($q) => $q->where('student_id', $studentId))
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        if ($quickNotes->isEmpty()) {
            $lines[] = 'Quick Notes to counselor (student messages): (none yet)';
        } else {
            $lines[] = 'Quick Notes — recent messages from this student (newest first):';
            foreach ($quickNotes as $msg) {
                $date = $msg->created_at ? $msg->created_at->format('Y-m-d H:i') : '?';
                $text = $this->truncateForPrompt((string) $msg->message_text, 280);
                $lines[] = "  - [{$date}] {$text}";
            }
        }

        return implode("\n", $lines);
    }

    private function truncateForPrompt(string $text, int $maxChars): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        return mb_substr($text, 0, $maxChars - 1) . '…';
    }

    /**
     * Build the system prompt from the template, injecting all dynamic context.
     */
    private function buildSystemPrompt(
        string $userName,
        string $emotion,
        string $conversationHistory,
        string $userInput,
        array $recentAriaQuestionStems,
        array $recentUserTurns,
        string $safetyAlert,
        string $wellnessContext
    ): string {
        $firstName = explode(' ', $userName)[0];

        $prompt = "You are Aria, a warm and caring friend to talk to for students at PSU–San Carlos. "
            . "You chat in simple, everyday words a student can easily understand. You feel like a kind older sister — patient, gentle, and easy to talk to. This is text chat only — you never use voice or sound.\n\n"
            . "How to talk:\n"
            . "- Always be kind, respectful, and encouraging.\n"
            . "- Use short, simple sentences. No big or doctor-like words.\n"
            . "- Never use clinical words like: clinical, diagnosis, disorder, distress, assessment, intervention, symptoms, or risk level. Instead say: how you're doing, tough days, worries, pressure, low mood, check-in.\n"
            . "- Sound natural and human, never scripted or robotic.\n"
            . "- Keep replies short but caring (3–5 sentences).\n\n"
            . "Remembering them:\n"
            . "- Use the chat history so you remember what they told you.\n"
            . "- If you see their past check-ins, mood notes, or quick notes below, gently notice patterns (tired days, school pressure, sleep, friends) and connect it to what they say now. "
            . "Use their latest check-in only to understand if they've had heavier days lately. "
            . "Talk about it softly in your own words; never read back their scores, list their entries, or sound like a report. If a section is empty, do not pretend they wrote anything.\n\n"
            . "Feelings first:\n"
            . "- If they feel sad → say it's okay to feel that way and comfort them.\n"
            . "- If they feel nervous → calm them softly and suggest one tiny breathing step.\n"
            . "- If they feel pressured → suggest one small, doable step.\n"
            . "- If they feel mad → stay patient and listen.\n\n"
            . "Keep chatting:\n"
            . "- Ask just 1 gentle follow-up question each time.\n"
            . "- Never ask 2 or more questions at once.\n"
            . "- Talk like a real, caring chat — not a meeting or checkup.\n\n"
            . "Little helps you can suggest:\n"
            . "  - taking 3 slow breaths\n"
            . "  - taking a short pause\n"
            . "  - writing a few lines in their mood notes\n"
            . "  - talking to someone they trust\n"
            . "- Keep ideas small and easy, never overwhelming.\n\n"
            . "If they share very heavy or unsafe thoughts:\n"
            . "  - Show you care deeply and take them seriously.\n"
            . "  - Gently ask them to reach out now to someone they trust, their counselor, or a hotline.\n"
            . "  - Never give harmful instructions.\n"
            . "  - Care first, chat second.\n\n"
            . "Quality check:\n"
            . "- Do NOT repeat what you already asked or said.\n"
            . "- Do NOT sound like a robot or a form.\n"
            . "- Make every reply feel personal for {$firstName}.\n"
            . (!empty($recentAriaQuestionStems) ? "- RECENT QUESTIONS ASKED (DO NOT REPEAT): \n  - " . implode("\n  - ", $recentAriaQuestionStems) . "\n" : "")
            . "\n"
            . "Student:\n"
            . "Name: {$firstName}\n"
            . "Feeling now: {$emotion}\n"
            . "What we know (latest check-in, mood notes & quick notes — written by the student):\n{$wellnessContext}\n"
            . "Our chat so far:\n{$conversationHistory}\n"
            . (!empty($recentUserTurns) ? "What they just said: " . json_encode($recentUserTurns) . "\n" : "")
            . "\n"
            . "What they just wrote:\n{$userInput}\n"
            . "\n"
            . "Now reply:\n"
            . "Write a warm, simple, caring reply in plain everyday words. No clinical words. Not repetitive. End with one gentle question."
            . $safetyAlert;

        return $prompt;
    }

    /**
     * Detect the dominant emotion in a message using a lightweight Gemini call.
     * Falls back to local keyword matching if the API is unavailable.
     */
    private function detectEmotion(string $message): string
    {
        $validEmotions = ['sad', 'anxious', 'stressed', 'angry', 'happy', 'neutral'];

        // Fast local keyword fallback (used if API is unavailable or as a first pass)
        $m = strtolower($message);
        $localEmotion = match (true) {
            (bool) preg_match('/(suicid|hurt myself|want to die|end it all|hopeless|giving up)/i', $m) => 'sad',
            (bool) preg_match('/(sad|cry|tears|empty|numb|low|depressed|grief|lonely)/i', $m) => 'sad',
            (bool) preg_match('/(anxious|nervous|panic|worry|dread|fear|scared|overwhelm)/i', $m) => 'anxious',
            (bool) preg_match('/(stress|pressure|workload|deadline|overload|burned out|exhausted|so much)/i', $m) => 'stressed',
            (bool) preg_match('/(angry|mad|furious|irritat|frustrat|annoyed|pissed)/i', $m) => 'angry',
            (bool) preg_match('/(happy|great|good|excited|grateful|glad|calm|peaceful)/i', $m) => 'happy',
            default => null,
        };

        if ($localEmotion !== null) {
            return $localEmotion;
        }

        // API-based detection for ambiguous messages
        try {
            $classifyPrompt = "Classify the primary emotion in this student message into exactly one word from this list: sad, anxious, stressed, angry, happy, neutral. Reply with only the word.\n\nMessage: \"{$message}\"";
            $result = $this->openRouter->generateSingleTurn($classifyPrompt);
            $normalized = strtolower(trim(preg_replace('/[^a-z]/', '', $result)));
            if (in_array($normalized, $validEmotions, true)) {
                return $normalized;
            }
        } catch (\Throwable $e) {
            // Silent fallback — emotion detection is non-critical
        }

        return 'neutral';
    }

    /**
     * Format the conversation history into a clean, readable transcript for the AI prompt.
     */
    private function formatConversationHistory($history): string
    {
        if ($history->isEmpty()) {
            return 'No previous messages in this session.';
        }

        return $history->map(function ($h) {
            $role = $h->sender === 'user' ? 'Student' : 'Aria';
            $time = $h->created_at ? $h->created_at->format('H:i') : '';
            return "[{$time}] {$role}: {$h->message}";
        })->implode("\n");
    }

    private function extractQuestionStem(string $text): string
    {
        if (!str_contains($text, '?')) {
            return '';
        }

        // Find the last sentence that contains a question mark
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $lastQuestion = '';
        foreach (array_reverse($sentences) as $s) {
            if (str_contains($s, '?')) {
                $lastQuestion = $s;
                break;
            }
        }

        if (!$lastQuestion) return '';

        // Normalize: lowercase, remove punctuation except in-word, remove common filler words
        $normalized = strtolower($lastQuestion);
        $normalized = (string) preg_replace('/[^a-z0-9 ]/i', '', $normalized);
        
        // Remove common fillers to get the "intent"
        $fillers = ['could you', 'can you', 'do you', 'is there', 'are you', 'how does', 'what does', 'tell me about', 'so', 'well', 'and'];
        $words = explode(' ', $normalized);
        $filteredWords = array_filter($words, fn($w) => !in_array($w, $fillers) && strlen($w) > 1);
        
        $stem = implode(' ', array_slice($filteredWords, 0, 5)); // Take first 5 meaningful words as stem
        return trim($stem);
    }

}
