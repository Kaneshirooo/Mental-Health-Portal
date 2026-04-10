<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Services\GeminiService;
use App\Services\PerspectiveService;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    protected $gemini;
    protected $perspective;
    protected $activity;

    public function __construct(GeminiService $gemini, PerspectiveService $perspective, ActivityService $activity)
    {
        $this->gemini = $gemini;
        $this->perspective = $perspective;
        $this->activity = $activity;
    }

    public function index()
    {
        // Reset conversation by deleting old history to ensure a new conversation every time
        ChatHistory::where('student_id', Auth::id())->delete();
        
        $chat_history = collect();

        return view('student.chat', compact('chat_history'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $student_id = Auth::id();
        $user = Auth::user();
        $userMessage = $request->input('message');

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
            'sender' => 'user',
            'message' => $userMessage,
            'created_at' => now(),
        ]);

        // Get past context (last 10 messages)
        $history = ChatHistory::where('student_id', $student_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse();

        $messages = [];
        foreach ($history as $h) {
            $messages[] = ['role' => ($h->sender === 'user' ? 'user' : 'model'), 'parts' => [['text' => $h->message]]];
        }

        $systemPrompt = "You are Aria, a compassionate and professional mental health support assistant for PSU students. Your goal is to be a warm, human-like listener who validates feelings first. "
            . "Speak naturally like a caring counselor, not like an AI. Keep responses concise but deeply empathetic. "
            . "The student's name is {$user->full_name}. "
            . "Use phrases like 'I hear you', 'That sounds really heavy', or 'I'm glad you shared that'. "
            . "Conversation Guidelines:\n"
            . "- Validate and acknowledge what the student says BEFORE moving on.\n"
            . "- Provide actionable, practical advice ONLY when specifically asked or when clearly helpful.\n"
            . "- Don't be too repetitive with questions. Sometimes just a supportive statement is enough.\n"
            . "- Always mention PSU's portal tools (Mood Journal, Mindfulness Corner) when helpful.\n"
            . "- Never diagnose. If risk is high, encourage professional counselor help."
            . $safetyAlert;

        $ariaResponse = $this->gemini->generateResponse($messages, $systemPrompt);

        // -- CLINICAL SAFETY FALLBACK --
        // If Gemini is blocked/errored, or if our local Perspective scan (if enabled) finds high risk
        if (empty($ariaResponse) || strlen($ariaResponse) < 2) {
            Log::warning("Aria triggered a Safety Fallback for User #{$student_id}. Use localized response.");
            $ariaResponse = $this->gemini->generateResponse($messages, $systemPrompt, true); // Force local fallback
        }

        // Save Aria's response
        ChatHistory::create([
            'student_id' => $student_id,
            'sender' => 'aria',
            'message' => $ariaResponse,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $ariaResponse,
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

        $systemInstruction = "You are a professional counselor assistant. Analyze the student's self-report and the conversation transcript to generate a JSON report. "
            . "The JSON MUST contain: 'mood' (string), 'energy' (string), 'focus' (string), 'social' (string), 'appetite' (string), 'sleep' (string), 'risk_level' (Low/Medium/High/Critical), 'core_concerns' (text), 'clinical_observations' (text), 'follow_up_needed' (boolean). "
            . "Do not include any text other than the JSON object.";

        $messages = [
            ['role' => 'user', 'parts' => [['text' => "STUDENT: {$name}\n\nREPORT: " . json_encode($form) . "\n\nTRANSCRIPT: {$transcript} "]]]
        ];

        try {
            $response = $this->gemini->generateResponse($messages, $systemInstruction);
            $report = json_decode($response, true);

            if (!$report || !isset($report['mood'])) {
                throw new \Exception("Invalid AI response");
            }
        } catch (\Exception $e) {
            // Fallback logic
            $report = $this->localPreAssessmentFallback($transcript, $form);
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

    private function localPreAssessmentFallback($transcript, $form)
    {
        $t = strtolower($transcript);
        $stress = (int)($form['stress_level'] ?? 5);
        $sleep  = (int)($form['sleep_quality'] ?? 3);

        $risk = 'Low';
        if (str_contains($t, 'hurt myself') || str_contains($t, 'suicid') || str_contains($t, 'want to die')) {
            $risk = 'Critical';
        } elseif ($stress >= 8 || $sleep <= 2) {
            $risk = 'High';
        } elseif ($stress >= 6 || $sleep <= 3) {
            $risk = 'Moderate';
        }

        return [
            'mood' => ($risk === 'Critical' || $risk === 'High') ? 'concerning' : 'neutral',
            'risk_level' => $risk,
            'stress_level' => $stress,
            'energy_level' => 10 - $stress,
            'summary' => 'Assessment generated from conversation and self-report.',
            'key_concerns' => ['Self-reported stress'],
            'recommendations' => ['Consider scheduling a consultation with a counselor.'],
            'follow_up_needed' => in_array($risk, ['High', 'Critical']),
        ];
    }
}
