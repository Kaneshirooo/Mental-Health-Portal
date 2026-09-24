<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MoodLog;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;

use App\Services\ClinicalAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MoodJournalController extends Controller
{
    public function __construct(
        protected readonly \App\Services\OpenRouterService $openRouter,
        protected readonly ClinicalAlertService $clinicalAlerts
    ) {}

    public function index()
    {
        $user = Auth::user();
        $history = MoodLog::where('student_id', $user->user_id)
            ->orderBy('logged_at', 'desc')
            ->limit(30)
            ->get();

        return view('student.mood', compact('history'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mood_score' => 'required|integer|min:1|max:5',
            'note' => 'nullable|string|max:1000',
        ]);

        $emojis = [
            1 => '😢', 2 => '😕', 3 => '😐', 4 => '🙂', 5 => '😊'
        ];
        
        $mood_emoji = $emojis[$request->mood_score] ?? '😐';

        $sentiment_score = null;
        $sentiment_label = null;

        if ($request->note) {
            try {
                $prompt = "Analyze the sentiment of this student's journal entry. 
                Return JSON only: {\"score\": 0.0-1.0, \"label\": \"String\"}
                Label MUST be one of: [Positive, Neutral, Anxious, Sad, Frustrated, Grateful, Overwhelmed].
                Student Note: \"{$request->note}\"";

                $response = $this->openRouter->generateSingleTurn($prompt);
                
                // Clean markdown from response if present
                $cleanJson = preg_replace('/^```json\s*|\s*```$/m', '', trim($response));
                $data = json_decode($cleanJson, true);
                
                if ($data && isset($data['label'])) {
                    $sentiment_score = $data['score'] ?? null;
                    $sentiment_label = $data['label'];
                }
            } catch (\Exception $e) {
                \Log::error("Mood Sentiment Analysis failed: " . $e->getMessage());
                // Silently continue - clinical safety is handled by score even if AI fails
            }
        }

        $log = MoodLog::create([
            'student_id' => Auth::id(),
            'mood_score' => $request->mood_score,
            'mood_emoji' => $mood_emoji,
            'note' => $request->note,
            'sentiment_score' => $sentiment_score,
            'sentiment_label' => $sentiment_label,
            'logged_at' => now(),
        ]);

        // Proactive Counselor Alert with AI Sentiment context
        $this->clinicalAlerts->triggerMoodAlert(Auth::user(), $request->mood_score, $mood_emoji, $request->note, $sentiment_label);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'log_id'  => $log->log_id,
                'emoji' => $mood_emoji,
                'note' => e($request->note),
                'sentiment_label' => $sentiment_label,
                'time' => now()->format('g:i A'),
                'date' => now()->format('F d, Y')
            ]);
        }

        return back()->with('success', 'Mood logged successfully.');
    }

    public function update(Request $request, MoodLog $mood)
    {
        // Ensure student owns the log
        if ($mood->student_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'mood_score' => 'required|integer|min:1|max:5',
            'note' => 'nullable|string|max:1000',
        ]);

        $emojis = [
            1 => '😢', 2 => '😕', 3 => '😐', 4 => '🙂', 5 => '😊'
        ];
        
        $mood_emoji = $emojis[$request->mood_score] ?? '😐';

        $sentiment_score = $mood->sentiment_score;
        $sentiment_label = $mood->sentiment_label;

        // Re-analyze sentiment only if note changed
        if ($request->note && $request->note !== $mood->note) {
            try {
                $prompt = "Analyze the sentiment of this student's journal entry. 
                Return JSON only: {\"score\": 0.0-1.0, \"label\": \"String\"}
                Label MUST be one of: [Positive, Neutral, Anxious, Sad, Frustrated, Grateful, Overwhelmed].
                Student Note: \"{$request->note}\"";

                $response = $this->openRouter->generateSingleTurn($prompt);
                $cleanJson = preg_replace('/^```json\s*|\s*```$/m', '', trim($response));
                $data = json_decode($cleanJson, true);
                
                if ($data && isset($data['label'])) {
                    $sentiment_score = $data['score'] ?? null;
                    $sentiment_label = $data['label'];
                }
            } catch (\Exception $e) {
                \Log::error("Mood Sentiment Analysis failed on update: " . $e->getMessage());
            }
        }

        $mood->update([
            'mood_score'      => $request->mood_score,
            'mood_emoji'      => $mood_emoji,
            'note'            => $request->note,
            'sentiment_score' => $sentiment_score,
            'sentiment_label' => $sentiment_label,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success'         => true,
                'emoji'           => $mood_emoji,
                'note'            => e($request->note),
                'sentiment_label' => $sentiment_label,
            ]);
        }

        return back()->with('success', 'Mood entry updated.');
    }

    public function insight()
    {
        $user = Auth::user();
        $logs = MoodLog::where('student_id', $user->user_id)
            ->latest('logged_at')
            ->limit(7)
            ->get();

        if ($logs->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No logs found.']);
        }

        $prompt = "Act as Aria, a supportive AI wellness companion. Based on the student's recent mood logs:\n";
        foreach ($logs as $log) {
            $prompt .= "- {$log->logged_at->format('M d')}: Score {$log->mood_score}/5 ({$log->mood_emoji}). Note: " . ($log->note ?: 'N/A') . "\n";
        }
        $prompt .= "\nProvide a warm, empathetic, and concise insight (max 60 words) about their emotional trend. Suggest a small positive action if needed. Use 'we' and 'you' to feel like a companion.";

        $systemInstruction = "You are a mental health professional assistant. Analyze the student's mood log and provide ONE sentence of deep, supportive insight. Avoid generic advice.";

        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];
        $insight = $this->openRouter->generateResponse($messages, $systemInstruction);

        return response()->json([
            'success' => true,
            'insight' => $insight ?: "I'm observing your patterns, but I need a few more logs to give you a detailed insight. Keep sharing with me!"
        ]);
    }
}
