<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MoodLog;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;

class MindfulnessController extends Controller
{
    protected $openRouter;

    public function __construct(OpenRouterService $openRouter)
    {
        $this->openRouter = $openRouter;
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

        $systemInstruction = "You are a warm, gentle friend guiding a short calm moment for a student. "
            . "Create a cozy, one-minute breathing or rest message in very simple everyday words. "
            . "Write it as a short script with no bold or headers. Keep it under 100 words.";

        $messages = [
            ['role' => 'user', 'parts' => [['text' => "I am $context"]]]
        ];
        $script = $this->openRouter->generateResponse($messages, $systemInstruction);

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

        $systemInstruction = "You are a caring friend for students. "
            . "Suggest ONE cozy idea (slow breathing, noticing 5 things around you, or a quiet rest) based on how they feel. "
            . "Use very simple everyday words. Keep it to exactly two warm sentences, text only, no voice.";

        $messages = [
            ['role' => 'user', 'parts' => [['text' => "Mood: {$mood->mood_emoji} (Score: {$mood->mood_score}/5). Note: \"{$mood->note}\""]]]
        ];
        $cacheKey = "recommendation_auth_" . auth()->id() . "_mood_" . $mood->id;
        $recommendation = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHour(), function() use ($messages, $systemInstruction) {
            return $this->openRouter->generateResponse($messages, $systemInstruction);
        });

        if (!$recommendation) {
            return response()->json(['success' => false, 'error' => 'That is taking a moment. Try one slow breath for now.']);
        }

        return response()->json([
            'success' => true,
            'recommendation' => trim($recommendation)
        ]);
    }
}
