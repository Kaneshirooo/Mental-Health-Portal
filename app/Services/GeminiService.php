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
    public function generateResponse(array $messages, string $systemInstruction = '', bool $useSmartCounselorOnly = false): string
    {
        if ($useSmartCounselorOnly || empty($this->apiKey) || empty($this->apiUrl)) {
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

            $response = Http::timeout(20)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl . '?key=' . $this->apiKey, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? $this->getSmartCounselorReply($messages);
            }

            Log::error("Gemini API Error: " . $response->status() . " | " . $response->body());
        } catch (\Throwable $e) {
            Log::error("Gemini API Error (Throwable): " . $e->getMessage());
        }

        return $this->getSmartCounselorReply($messages);
    }

    /**
     * Local fallback logic for the AI Counselor (Aria personality).
     */
    protected function getSmartCounselorReply(array $messages): string
    {
        $lastMessage = '';
        $turnCount = 0;
        
        if (!empty($messages)) {
            foreach ($messages as $m) {
                if (($m['role'] ?? '') === 'user') $turnCount++;
            }
            $lastContent = end($messages);
            $lastMessage = $lastContent['parts'][0]['text'] ?? '';
        }
        
        $m = strtolower($lastMessage);

        // -- Profanity / cursing filter --
        $profanity = ['fuck', 'shit', 'damn', 'ass', 'bitch', 'crap', 'hell', 'bastard', 'idiot', 'stupid', 'wtf', 'stfu', 'fck', 'f\*ck', 's\*it'];
        foreach ($profanity as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $m)) {
                return "I can hear that you're feeling really frustrated right now, and that's completely okay — emotions can get intense sometimes. I'm not going anywhere, and I'm not going to judge you for how you're feeling. Whenever you're ready, I'd really like to understand what's been going on for you. What's been making things feel so difficult?";
            }
        }

        // -- Advice requests (Refactored using match for performance) --
        $adviceTriggers = ['advice', 'advise', 'what should i do', 'help me', 'tips', 'suggest', 'how do i', 'how to', 'what can i', 'what do i', 'give me', 'tell me how'];
        foreach ($adviceTriggers as $trigger) {
            if (str_contains($m, $trigger)) {
                return match (true) {
                    str_contains($m, 'stress') || str_contains($m, 'overwhelm') || str_contains($m, 'pressure') => 
                        "Here are some practical things that can help with stress:\n• Break big tasks into smaller steps — one at a time\n• Take 5-minute breaks every 45 minutes when studying\n• Try a quick breathing exercise: inhale 4 counts, hold 4, exhale 4\n• Write down your worries in the Mood Journal to get them out of your head\n• Talk to someone — even a short chat can lighten the load\nWhich of these feels most doable for you right now?",
                    
                    str_contains($m, 'sleep') || str_contains($m, 'insomnia') => 
                        "For better sleep, here are a few things that genuinely help:\n• Set a consistent sleep and wake time\n• Avoid screens 30 minutes before bed\n• Keep your room cool and dark\n• Try the local relaxation tools in your portal\n• Avoid caffeine after 3pm\nHow does your current sleep routine look?",
                    
                    str_contains($m, 'anxi') || str_contains($m, 'nervous') || str_contains($m, 'panic') => 
                        "When anxiety shows up, these can really help:\n• Ground yourself: name 5 things you can see, 4 you can touch, 3 you can hear\n• Try box breathing: inhale 4s, hold 4s, exhale 4s, hold 4s\n• Challenge the worry: ask 'Is this likely? What is the worst that could happen?'\n• Use the Mindfulness Corner in your dashboard\n• Limit caffeine and social media\nWould you like to try one of these right now?",
                    
                    default => "I'm glad you're looking for ways to help yourself! Here are some general thoughts:\n• Talk about what you're feeling — to a friend or journal\n• Move your body — even a short walk can shift your mood\n• Rest without guilt — your mind needs recovery time\n• Use the portal tools like the Mood Journal.\nWhat area would you like more specific advice on?",
                };
            }
        }

        // -- Mindfulness / Meditation scripts fallback --
        if (str_contains($m, 'mindfulness') || str_contains($m, 'meditation') || str_contains($m, 'breathing exercise')) {
            return "Take a deep breath and settle into a comfortable position. Close your eyes if you feel safe to do so. Notice the weight of your feet on the floor and the air moving in and out of your body. Inhale the calm, and as you exhale, let go of any tension in your shoulders. You are present, you are safe, and you are doing your best. Take one more deep breath before slowly opening your eyes.";
        }

        if ($turnCount >= 7) {
            return "Thank you so much for opening up today — that really takes courage. You've shared a lot, and it all matters. If anything feels heavy after this, please consider checking in with a trusted adult or your school counselor. You deserve support and care.";
        }

        // Emotion keyword search
        foreach ($this->getEmotionPatterns() as $group) {
            foreach ($group['words'] as $word) {
                if (str_contains($m, $word)) return $group['reply'];
            }
        }

        // -- Specific student life topics --
        $studentTopics = [
            'grade' => "Grades can feel like a huge weight, but remember they don't define your worth as a person. Are you feeling pressure from a specific subject, or is it the overall workload?",
            'exam' => "Exams are definitely high-stress times. One thing that helps is the 'Pomodoro technique' — 25 mins of study, then a 5-min break. Have you been getting enough sleep between study sessions?",
            'teacher' => "Relationships with instructors can be tricky. It's often helpful to reach out during office hours for a quick chat — they usually appreciate the initiative. What's been the hardest part of communicating with them?",
            'professor' => "Relationships with instructors can be tricky. It's often helpful to reach out during office hours for a quick chat — they usually appreciate the initiative. What's been the hardest part of communicating with them?",
            'friend' => "Friendships are such a big part of the college experience, but they can be complicated. It sounds like something is on your mind regarding your social circle. Do you feel supported by them right now?",
            'family' => "Family expectations can sometimes feel overwhelming when you're trying to find your own path. How are things at home affecting your focus lately?",
            'career' => "It's completely normal to feel uncertain about the future. You don't have to have everything figured out right now. What's one thing you're curious about exploring, even if you're not sure yet?",
            'lonely' => "I'm sorry you're feeling lonely. Even in a crowded school, it's possible to feel isolated. I'm here to listen, and sometimes joining just one small club or group can make a difference. What's one thing you enjoy doing for yourself?",
        ];

        foreach ($studentTopics as $key => $reply) {
            if (str_contains($m, $key)) return $reply;
        }

        // Turn-based fallbacks (More Aria-like)
        return match ($turnCount) {
            0 => "Hi there! I'm Aria, your wellness companion. I'm here to listen to whatever is on your mind today — no judgment, just support. How have you been feeling lately?",
            1 => "Thank you for sharing that with me. It takes strength to open up. Could you tell me a little more about what's been making things feel that way for you?",
            2 => "I'm really listening. When you feel this way, does it affect your sleep, your energy, or maybe your focus on schoolwork?",
            3 => "On a scale of 1 to 10 (1 being very calm, 10 being very overwhelmed), where would you say you are right now? Knowing where you are helps me understand how to best support you.",
            4 => "I appreciate your honesty with that number. What do you think is the biggest thing contributing to that feeling right now?",
            5 => "When things get heavy, remember you don't have to carry it all at once. What's one small thing you could do for yourself today — maybe a short walk, or using one of the breathing tools in our Mindfulness Corner?",
            default => "I've really appreciated our talk. Remember, your feelings are valid and you're doing your best. If you ever feel like you need more specialized support, our school counselors are wonderful. is there anything else you'd like to share before we wrap up?",
        };
    }

    /**
     * Map of emotion keywords and their empathetic replies.
     */
    private function getEmotionPatterns(): array
    {
        return [
            ['words' => ['suicid', 'hurt myself', 'end it all', 'want to die', 'hopeless', 'can\'t go on'],
                'reply' => "I'm really glad you said that out loud — it means you're not facing this completely alone. You deserve help with feelings this big. If you're not ready to talk to a person yet, you can send an Anonymous Quick Note to our counselor team through the portal. But please, if you feel unsafe right now, reach out to a trusted adult or a crisis helpline immediately. Can you share a bit more about what's making things feel so heavy?"],

            ['words' => ['burnout', 'burn out', 'exhausted', 'drained', 'can\'t anymore', 'no energy', 'done'],
                'reply' => "That sounds like a deep level of exhaustion — the kind that builds up when you've been giving so much for so long. One thing that can help is giving yourself permission to rest without guilt, because rest is not laziness. Try breaking your day into smaller chunks with proper breaks in between. When did you first start feeling this drained?"],

            ['words' => ['suffocate', 'suffocated', 'trapped', 'stuck', 'no way out', 'can\'t breathe', 'choked'],
                'reply' => "It sounds like things have been feeling really tight and overwhelming, almost like there isn't space to breathe. When that happens, try this: pause, take 3 slow deep breaths, and name just ONE small thing you can do right now — even something tiny. What do you think is making you feel the most stuck right now?"],

            ['words' => ['stress', 'stressed', 'pressure', 'overwhelm', 'too much', 'so much'],
                'reply' => "I hear how much pressure you're under. Some things that genuinely help: break your tasks into smaller steps, schedule short breaks, and if your mind won't stop racing, try writing everything down in your Mood Journal to clear your head. What's the biggest source of pressure right now — school, family, or something else?"],

            ['words' => ['anxious', 'anxiety', 'worried', 'worry', 'nervous', 'panic', 'fear', 'scared', 'dread'],
                'reply' => "Anxiety can make even ordinary things feel much heavier. One technique that helps immediately is box breathing — inhale slowly for 4 counts, hold for 4, exhale for 4, hold for 4. Every PSU student has access to the Mindfulness Corner in their dashboard for more exercises like this. What triggers your anxiety the most?"],

            ['words' => ['sad', 'sadness', 'low', 'depressed', 'depression', 'down', 'crying', 'cry', 'tears', 'empty', 'numb'],
                'reply' => "Thank you for trusting me with that. Feeling low or empty is really hard, and it's okay to not be okay. One small thing that can help is writing how you feel in the Private Mood Journal — sometimes getting it out of your head onto paper brings a little relief. Has this been going on for a while, or did something specific trigger it?"],

            ['words' => ['tired', 'fatigue', 'sleep', 'sleeping', 'insomnia', 'can\'t sleep', 'no sleep'],
                'reply' => "Poor sleep affects everything — mood, concentration, and how we handle stress. A few things that help: try to sleep and wake at the same time each day, avoid screens for 30 minutes before bed, and try a short relaxation exercise from the Mindfulness Corner. How long has sleep been a problem for you?"],

            ['words' => ['angry', 'anger', 'frustrated', 'frustrat', 'annoyed', 'irritated', 'mad', 'rage'],
                'reply' => "I can hear a lot of frustration in what you're sharing, and that's valid. When anger gets intense, it helps to step away briefly — even a 5-minute walk can take the edge off. Anger usually points to something important. What's at the heart of this?"],

            ['words' => ['don\'t know', 'not sure', 'confused', 'can\'t explain', 'hard to say', 'idk', 'unsure'],
                'reply' => "That's completely okay — feelings can be messy and hard to put into words. We can start wherever feels easiest. What's been taking up the most space in your head lately?"],

            ['words' => ['friend', 'friends', 'relationship', 'boyfriend', 'girlfriend', 'partner', 'crush', 'social'],
                'reply' => "Relationships can be a huge source of both joy and stress. It sounds like your social life is an important part of your world. What's been on your mind regarding your social connections lately?"],

            ['words' => ['family', 'parents', 'mom', 'dad', 'brother', 'sister', 'home'],
                'reply' => "Family dynamics can be really complex and impact us deeply. What's been happening at home, or how are things with your family affecting you right now?"],

            ['words' => ['future', 'career', 'college', 'university', 'path', 'goals', 'plan'],
                'reply' => "Thinking about the future can bring both excitement and a lot of pressure. It's natural to feel a mix of emotions when considering your path ahead. What's the most daunting part of it for you?"],

            ['words' => ['health', 'sick', 'pain', 'body', 'doctor', 'hospital'],
                'reply' => "Your physical health is so connected to your mental well-being. How are you feeling in your body lately, and how is that impacting your daily life and mood?"],

            ['words' => ['money', 'job', 'work', 'finances', 'afford', 'cost'],
                'reply' => "Financial worries can add a significant layer of stress, especially as a student. How are these concerns affecting your overall well-being and ability to focus?"],

            ['words' => ['bored', 'boring', 'nothing to do', 'stuck in a rut'],
                'reply' => "Feeling bored or stuck can sometimes be a sign that you're ready for something new. Is there anything you used to enjoy that you haven't done in a while?"],

            ['words' => ['goal', 'achieve', 'succeed', 'improve', 'progress'],
                'reply' => "Setting goals and working towards them is a fantastic mindset! What's one step you're considering taking towards your goal, and what support might help you get there?"],

            ['words' => ['counselor', 'therapist', 'help', 'support'],
                'reply' => "It's incredibly brave to consider reaching out for professional support. Remember, you can always send an Anonymous Quick Note to our counselors if you're not ready for a direct meeting yet. What kind of support feels most helpful right now?"],
        ];
    }
}
