<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MoodLog;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class MindfulnessController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function index()
    {
        return view('student.mindfulness.index');
    }

    public function generateAiSession(Request $request)
    {
        $request->validate(['mood' => 'required|string']);
        
        $moodPrompts = [
            'stressed'  => 'feeling very stressed and overwhelmed with school pressure.',
            'anxious'   => 'feeling anxious and having racing thoughts.',
            'sad'       => 'feeling low, sad, or a bit lonely today.',
            'tired'     => 'feeling physically and mentally exhausted.',
            'neutral'   => 'feeling okay but wanting to maintain their peace.',
            'happy'     => 'feeling good and wanting to savor this positive moment.'
        ];

        $context = $moodPrompts[$request->mood] ?? $moodPrompts['neutral'];

        $systemInstruction = "You are a warm, soothing mindfulness and meditation guide for students. "
            . "Create a unique, personalized 1-minute mindfulness exercise based on the provided context. "
            . "Structure it as a short script without markdown (no bold/headers). Keep it under 100 words.";

        $messages = [
            ['role' => 'user', 'parts' => [['text' => "I am $context"]]]
        ];
        $script = $this->gemini->generateResponse($messages, $systemInstruction);

        if (!$script) {
            return response()->json(['success' => false, 'error' => 'The Zen garden is being watered. Please try again in a moment.']);
        }

        return response()->json([
            'success' => true,
            'script' => trim($script)
        ]);
    }

    public function getRecommendation()
    {
        $mood = MoodLog::where('student_id', auth()->id())
            ->latest('logged_at')
            ->first();

        if (!$mood) {
            return response()->json(['success' => false, 'error' => 'No mood logs found yet.']);
        }

        $name = explode(' ', auth()->user()->full_name)[0];

        $systemInstruction = "You are Aria, a mental health AI counselor. "
            . "Recommend ONE specific mindfulness exercise (4-7-8 Breathing, 5-4-3-2-1 Grounding, or Body Scan) based on the student's mood log. "
            . "Keep the response to exactly two supportive sentences.";

        $messages = [
            ['role' => 'user', 'parts' => [['text' => "Mood: {$mood->mood_emoji} (Score: {$mood->mood_score}/5). Note: \"{$mood->note}\""]]]
        ];
        $cacheKey = "recommendation_auth_" . auth()->id() . "_mood_" . $mood->id;
        $recommendation = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHour(), function() use ($messages, $systemInstruction) {
            return $this->gemini->generateResponse($messages, $systemInstruction);
        });

        if (!$recommendation) {
            return response()->json(['success' => false, 'error' => 'Aria is busy right now.']);
        }

        return response()->json([
            'success' => true,
            'recommendation' => trim($recommendation)
        ]);
    }
}
