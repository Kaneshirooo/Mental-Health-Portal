<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $apiUrl = null,
    ) {
        $this->apiKey = $apiKey ?? config('services.gemini.key');
        $this->apiUrl = $apiUrl ?? config('services.gemini.url');
    }

    /**
     * Generate a response from Gemini AI or fallback to the local counselor logic.
     */
    /**
     * Lightweight single-turn call — ideal for quick classification tasks like emotion detection.
     * Does NOT use multi-turn history format, just sends a plain user prompt.
     */
    public function generateSingleTurn(string $prompt): string
    {
        if (empty($this->apiKey) || empty($this->apiUrl)) {
            return 'neutral';
        }

        try {
            $payload = [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 16,
                ],
            ];

            $url = $this->apiUrl;
            if (str_contains($url, '/v1/') && !str_contains($url, '/v1beta/')) {
                $url = str_replace('/v1/', '/v1beta/', $url);
            }

            $response = Http::timeout(8)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url . '?key=' . $this->apiKey, $payload);

            if ($response->successful()) {
                $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return strtolower(trim($text));
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateSingleTurn failed: ' . $e->getMessage());
        }

        return 'neutral';
    }

    public function generateResponse(array $messages, string $systemInstruction = '', bool $useSmartCounselorOnly = false): string
    {
        if ($useSmartCounselorOnly || empty($this->apiKey) || empty($this->apiUrl)) {
            $this->debugLog('H9', 'GeminiService.php:generateResponse', 'Using local fallback before API call', [
                'useSmartCounselorOnly' => $useSmartCounselorOnly,
                'hasApiKey' => !empty($this->apiKey),
                'hasApiUrl' => !empty($this->apiUrl),
            ]);
            return $this->getSmartCounselorReply($messages);
        }

        try {
            $payload = [
                'contents' => $messages,
                'generationConfig' => [
                    'temperature' => 0.8, // Slightly higher for more human-like variety
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ],
                'safetySettings' => [
                    ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
                ]
            ];

            if ($systemInstruction) {
                $payload['system_instruction'] = [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ];
            }

            $url = $this->apiUrl;
            if (str_contains($url, '/v1/') && !str_contains($url, '/v1beta/')) {
                $url = str_replace('/v1/', '/v1beta/', $url);
            }

            $response = Http::timeout(25)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url . '?key=' . $this->apiKey, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!empty($text)) {
                    $this->debugLog('H9', 'GeminiService.php:generateResponse', 'Using Gemini API response', [
                        'status' => $response->status(),
                        'responseLength' => strlen((string) $text),
                    ]);
                    return $text;
                }
                $this->debugLog('H9', 'GeminiService.php:generateResponse', 'Gemini API response empty; fallback used', [
                    'status' => $response->status(),
                ]);
                return $this->getSmartCounselorReply($messages);
            }

            Log::error("Gemini API Error: " . $response->status() . " | " . $response->body());
            $this->debugLog('H9', 'GeminiService.php:generateResponse', 'Gemini API non-success; fallback used', [
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Gemini API Error (Throwable): " . $e->getMessage());
            $this->debugLog('H9', 'GeminiService.php:generateResponse', 'Gemini throwable; fallback used', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->getSmartCounselorReply($messages);
    }

    /**
     * Local fallback replies for the chat companion.
     * Uses warm, simple, student-friendly words. Text chat only — no voice.
     */
    protected function getSmartCounselorReply(array $messages): string
    {
        $lastMessage = '';
        if (!empty($messages)) {
            $lastContent = end($messages);
            $lastMessage = $lastContent['parts'][0]['text'] ?? '';
        }
        
        $m = strtolower($lastMessage);

        // -- Profanity / cursing filter --
        $profanity = ['fuck', 'shit', 'damn', 'ass', 'bitch', 'crap', 'hell', 'bastard', 'idiot', 'stupid', 'wtf', 'stfu', 'fck'];
        foreach ($profanity as $word) {
            if (str_contains($m, $word)) {
                $profanityReplies = [
                    "I can hear that you're feeling really frustrated right now, and that's completely okay — emotions can get intense sometimes.",
                    "It sounds like you're going through a lot of pressure. I'm not here to judge you for how you express that.",
                    "I hear the frustration in your words. I'm here to listen whenever you're ready to share more about what's going on."
                ];
                return $profanityReplies[array_rand($profanityReplies)] . " What's been making things feel so difficult lately?";
            }
        }

        // -- Active Listening Bridge Phrases (to vary responses) --
        $bridges = [
            "I hear you, and I'm really listening. It sounds like you're going through a lot.",
            "Thank you for trusting me with that. I can hear the emotion in what you're sharing.",
            "That sounds like a lot to carry on your own. I'm here to support you.",
            "I'm glad you shared that with me. It takes strength to voice these feelings.",
            "I can tell this is something that's been weighing on you lately.",
            "I'm here, and I'm listening. What you're saying is important."
        ];
        $bridge = $bridges[array_rand($bridges)];

        // -- Advice triggers (simple, everyday words) --
        $adviceTriggers = ['advice', 'advise', 'what should i do', 'help me', 'tips', 'suggest', 'how do i', 'how to'];
        foreach ($adviceTriggers as $trigger) {
            if (str_contains($m, $trigger)) {
                if (str_contains($m, 'stress') || str_contains($m, 'overwhelm')) {
                    return "When school or life feels like a lot, try picking just 3 tiny things for today. You could also try one slow breathing break in the Calm Space. Which one feels easier to try first?";
                }
                if (str_contains($m, 'sleep')) {
                    return "For better sleep, try putting your phone away 30 minutes before bed and doing something calm. Have you noticed what keeps your mind busy at night?";
                }
                return "I'm really glad you asked — that takes courage. Writing a few lines in your Mood Notes is often a good first step. What part feels heaviest and you want to start with?";
            }
        }

        // -- Emotion & Topic keyword search with randomized replies --
        foreach ($this->getDynamicPatterns() as $pattern) {
            foreach ($pattern['words'] as $word) {
                if (str_contains($m, $word)) {
                    $reply = $pattern['replies'][array_rand($pattern['replies'])];
                    $question = $pattern['questions'][array_rand($pattern['questions'])];
                    return "{$reply} {$question}";
                }
            }
        }

        // -- General fallback (when no keywords match) --
        $genericQuestions = [
            "Could you share a little more about what's been on your mind in your own words?",
            "How has that been showing up in your day — like sleep, energy, or time with others?",
            "When did you first start feeling this way?",
            "What usually helps you feel even a tiny bit better when days are heavy?",
            "Was there one small moment today that made it feel heavier?"
        ];
        
        return "{$bridge} " . $genericQuestions[array_rand($genericQuestions)];
    }

    /**
     * Map of keywords to multiple randomized replies and follow-up questions.
     */
    private function getDynamicPatterns(): array
    {
        return [
            [
                'words' => ['exam', 'test', 'grade', 'study', 'school', 'professor', 'workload', 'assignment'],
                'replies' => [
                    "School pressure can feel really heavy sometimes.",
                    "It sounds like school is taking up a lot of space in your mind right now.",
                    "It's hard to rest when deadlines keep piling up."
                ],
                'questions' => [
                    "Which subject or school task feels heaviest today?",
                    "Do you have someone who can help a little with schoolwork?",
                    "What's one tiny thing that could make studying feel a little lighter?"
                ]
            ],
            [
                'words' => ['lonely', 'alone', 'friend', 'social', 'isolation', 'nobody', 'distance'],
                'replies' => [
                    "Feeling left out can make even small days feel bigger.",
                    "Wanting to feel seen and included is so normal.",
                    "I'm sorry you've been feeling far from others lately."
                ],
                'questions' => [
                    "Has this lonely feeling been around for a while, or is it newer?",
                    "Is there one person you feel even a tiny bit comfy talking to?",
                    "What does a cozy, friendly moment look like for you?"
                ]
            ],
            [
                'words' => ['tired', 'fatigue', 'sleep', 'insomnia', 'exhausted', 'drained'],
                'replies' => [
                    "Being tired can make feelings feel even heavier.",
                    "It sounds like your body and mind are asking for a little rest.",
                    "Feeling drained often means you've been carrying a lot."
                ],
                'questions' => [
                    "How did you sleep last night?",
                    "What feels like the biggest energy-drainer right now?",
                    "If you had one cozy hour to rest today, what would you do?"
                ]
            ],
            [
                'words' => ['anxious', 'nervous', 'panic', 'worry', 'dread', 'fear'],
                'replies' => [
                    "That worried feeling can make tomorrow seem really big.",
                    "That tight, uneasy feeling can be so tiring in your body too.",
                    "I hear how heavy those worries feel right now."
                ],
                'questions' => [
                    "Where do you feel it most in your body right now — like chest, head, or tummy?",
                    "What's one tiny thing that usually helps you feel even a little calmer?",
                    "Would you like to try one slow breathing break together from the Calm Space?"
                ]
            ],
            [
                'words' => ['sad', 'low', 'empty', 'cry', 'tears', 'numb', 'depressed'],
                'replies' => [
                    "It's okay to have heavy days, and it's okay to talk about them here.",
                    "Thank you for telling me you're feeling low — that takes real courage.",
                    "I hear how sad and heavy things feel right now."
                ],
                'questions' => [
                    "What part of today felt hardest for you?",
                    "Is there a small cozy thing that usually comforts you a little?",
                    "Is this a feeling that comes and goes, or has it been sticking around for a while?"
                ]
            ],
            [
                'words' => ['suicid', 'hurt myself', 'end it all', 'want to die', 'hopeless', 'giving up'],
                'replies' => [
                    "Thank you for telling me something so heavy — I'm really glad you did. You matter, and you don't have to carry this alone.",
                    "That sounds like an incredibly painful place to be. Thank you for being brave and telling me.",
                    "I hear how much pain you're in right now. Please know there are people who want to help, including me right here."
                ],
                'questions' => [
                    "Could you share a little more about what's been making days feel so heavy?",
                    "Are you in a safe place right now? I care about you.",
                    "Would you like to keep talking here, or reach out now to someone you trust or your counselor? There's also help in the Crisis Help box."
                ]
            ],
            [
                'words' => ['hello', 'hi', 'hey'],
                'replies' => [
                    "Hello! I'm really glad you're here. How are you feeling today?",
                    "Hi there. Thanks for stopping by. What's on your mind?",
                    "Hello! How has your day been so far?"
                ],
                'questions' => [
                    "What would you like to chat about today?",
                    "Is there something weighing on you, or do you just want to check in?",
                    "How are things going for you lately?"
                ]
            ]
        ];
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
