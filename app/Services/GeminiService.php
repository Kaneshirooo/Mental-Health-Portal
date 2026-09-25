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
     * Local fallback logic for the AI Counselor (Aria personality).
     * Now significantly more dynamic to avoid repetitive questioning and patterns.
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

        // -- Advice triggers --
        $adviceTriggers = ['advice', 'advise', 'what should i do', 'help me', 'tips', 'suggest', 'how do i', 'how to'];
        foreach ($adviceTriggers as $trigger) {
            if (str_contains($m, $trigger)) {
                if (str_contains($m, 'stress') || str_contains($m, 'overwhelm')) {
                    return "When school or life feels overwhelming, I suggest trying the 'Rule of Three': pick just three small things to do today. You could also try a 5-minute grounding exercise from our Mindfulness Corner. Which of those sounds easier to try first?";
                }
                if (str_contains($m, 'sleep')) {
                    return "For better rest, try keeping your phone across the room 30 minutes before bed. Have you noticed if anything specific keeps your mind racing at night?";
                }
                return "I'm glad you're looking for ways to help yourself! A good first step is often writing how you feel in the Mood Journal. What area would you like to focus on fixing first?";
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
            "Could you tell me a little more about what's been on your mind lately?",
            "How has that been affecting your day-to-day life?",
            "When did you first start noticing these feelings?",
            "What do you usually do to take care of yourself when things feel this way?",
            "Is there something specific that triggered this feeling today?"
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
                    "Academic pressure is a huge weight for many students.",
                    "It sounds like school is taking up a lot of your mental energy right now.",
                    "Balance can be so hard to find when deadlines are looming."
                ],
                'questions' => [
                    "Which specific subject or project is feeling the heaviest today?",
                    "Do you feel like you have enough support with your current workload?",
                    "What's one small thing that would make your study time feel a little lighter?"
                ]
            ],
            [
                'words' => ['lonely', 'alone', 'friend', 'social', 'isolation', 'nobody', 'distance'],
                'replies' => [
                    "Feeling isolated can make even small challenges feel much larger.",
                    "It's a very human thing to want to feel connected and seen.",
                    "I'm sorry you're feeling a distance from others right now."
                ],
                'questions' => [
                    "Have you felt this way for a while, or is it a more recent feeling?",
                    "Is there anyone in your life you feel even a little bit comfortable talking to?",
                    "What does a 'good' social connection look like for you?"
                ]
            ],
            [
                'words' => ['tired', 'fatigue', 'sleep', 'insomnia', 'exhausted', 'drained'],
                'replies' => [
                    "Exhaustion can make it so much harder to manage our emotions.",
                    "It sounds like your body and mind are really asking for some rest.",
                    "Being drained like this is often a sign of how much you've been handling."
                ],
                'questions' => [
                    "How has your sleep been lately?",
                    "What do you think is the biggest thing draining your energy right now?",
                    "If you could have one hour of pure rest today, how would you spend it?"
                ]
            ],
            [
                'words' => ['anxious', 'nervous', 'panic', 'worry', 'dread', 'fear'],
                'replies' => [
                    "Anxiety has a way of making the future feel very uncertain.",
                    "That tight feeling of worry can be so physically draining.",
                    "I hear the weight of that anxiety in your words."
                ],
                'questions' => [
                    "Where do you feel that anxiety most in your body right now?",
                    "What's one thing that usually helps you feel even 1% calmer?",
                    "Would you like to try a quick breathing tool from our Mindfulness Corner?"
                ]
            ],
            [
                'words' => ['sad', 'low', 'empty', 'cry', 'tears', 'numb', 'depressed'],
                'replies' => [
                    "It's okay to not be okay, and it's okay to sit with these heavy feelings.",
                    "Thank you for being honest about feeling low; that takes courage.",
                    "I can hear the sadness you're carrying right now."
                ],
                'questions' => [
                    "What's been the hardest part of today for you?",
                    "Do you have a safe space or a Comfort Activity you can turn to?",
                    "Is this a feeling that comes and goes, or has it been staying for a while?"
                ]
            ],
            [
                'words' => ['suicid', 'hurt myself', 'end it all', 'want to die', 'hopeless', 'giving up'],
                'replies' => [
                    "I'm really glad you shared that with me — it means you don't have to carry this alone. Your life has immense value.",
                    "Thank you for being brave enough to voice these very heavy thoughts. They sound incredibly painful.",
                    "I hear how much pain you are in right now. Please know that help is available and you're not alone in this."
                ],
                'questions' => [
                    "Could you tell me a bit more about what's making the world feel so heavy today?",
                    "Do you feel safe where you are right now? I'm concerned about you.",
                    "Would you be open to talk more about these feelings, or perhaps reaching out to the crisis resources listed on the side?"
                ]
            ],
            [
                'words' => ['hello', 'hi', 'hey', 'aria'],
                'replies' => [
                    "Hello! I'm Aria. How are you feeling today?",
                    "Hi there. I'm glad you're here. What's on your mind?",
                    "Hello. I'm Aria, your wellness companion. How's your day been going so far?"
                ],
                'questions' => [
                    "What would you like to talk about today?",
                    "Is there anything specific weighing on you, or are you just checking in?",
                    "How are you holding up with everything going on?"
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
