<?php
/**
 * student_ai_chat_api.php
 * Backend handler for the Student AI Chat feature.
 * Accepts POST requests with a conversation history (JSON),
 * forwards them to Google Gemini, and returns the AI reply as JSON.
 */

require_once 'config.php';
require_once 'ai_config.php';
requireStudent(); // Must be logged-in student

header('Content-Type: application/json');

// ── Request Validation ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── Rate Limiting (Session-based) ─────────────────────
$now = time();
if (!isset($_SESSION['chat_rate_limit'])) {
    $_SESSION['chat_rate_limit'] = ['count' => 0, 'start' => $now];
}
// Reset every 60 seconds
if ($now - $_SESSION['chat_rate_limit']['start'] > 60) {
    $_SESSION['chat_rate_limit'] = ['count' => 0, 'start' => $now];
}
$_SESSION['chat_rate_limit']['count']++;
if ($_SESSION['chat_rate_limit']['count'] > 12) { // Max 12 messages per minute
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please wait a moment.']);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!isset($body['messages']) || !is_array($body['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request: messages array required']);
    exit;
}

// ── CSRF Protection ───────────────────────────────────
if (!isset($body['csrf_token']) || !verifyCSRFToken($body['csrf_token'])) {
    // Note: If testing via Postman/tools without session, you might need to bypass this carefully.
    // For now, enforcing it for production-grade security.
    http_response_code(403);
    echo json_encode(['error' => 'Invalid or missing CSRF token']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);
$name = htmlspecialchars(explode(' ', $user['full_name'])[0]);

// Messages come from the frontend as:
// [{role: 'user' | 'assistant', content: '...'}, ...]
$messages = $body['messages'];

// Last message must be from the student (user)
$last = end($messages);
if (!isset($last['role'], $last['content']) || $last['role'] !== 'user') {
    http_response_code(400);
    echo json_encode(['error' => 'Last message must be from user']);
    exit;
}
$userMessage = trim($last['content']);
if ($userMessage === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty message']);
    exit;
}
if (mb_strlen($userMessage) > 1200) {
    http_response_code(400);
    echo json_encode(['error' => 'Message too long (max 1200 characters)']);
    exit;
}

// ── SAVE USER MESSAGE TO DB ───────────────────────────
$stmt_save = $conn->prepare("INSERT INTO chat_history (student_id, sender, message) VALUES (?, 'user', ?)");
$stmt_save->bind_param("is", $user_id, $userMessage);
$stmt_save->execute();

// ── RETRIEVE DB HISTORY (Last 10 messages) ─────────────
// This ensures Aria remembers context even across page refreshes.
$stmt_hist = $conn->prepare("SELECT sender, message FROM chat_history WHERE student_id = ? ORDER BY created_at DESC LIMIT 11");
$stmt_hist->bind_param("i", $user_id);
$stmt_hist->execute();
$res_hist = $stmt_hist->get_result();

$db_history = [];
while ($row = $res_hist->fetch_assoc()) {
    $db_history[] = [
        'role' => ($row['sender'] === 'user' ? 'user' : 'assistant'),
        'content' => $row['message']
    ];
}
// Reverse to chronological order
$db_history = array_reverse($db_history);

// Use DB history for Gemini context (excluding the current user message which is already at the end)
// We take all but the last one (which we'll append manually later)
$history = $db_history;
array_pop($history); // Remove the message we just added so we can append it cleanly below

// ── Turn count (how many exchanges so far) ────────────
$userTurns = 0;
foreach ($history as $m) {
    if (($m['role'] ?? '') === 'user') {
        $userTurns++;
    }
}
$turnCount = $userTurns;

// ══════════════════════════════════════════════════════
// SMART LOCAL COUNSELOR — used when Gemini is unavailable
// ══════════════════════════════════════════════════════
function smartCounselorReply(string $msg, int $turn): string
{
    $m = strtolower($msg);

    // ── Profanity / cursing — respond with calm empathy ──
    $profanity = ['fuck', 'shit', 'damn', 'ass', 'bitch', 'crap', 'hell', 'bastard', 'idiot', 'stupid', 'wtf', 'stfu', 'fck', 'f*ck', 's*it'];
    foreach ($profanity as $word) {
        if (str_contains($m, $word)) {
            return "I can hear that you're feeling really frustrated right now, and that's completely okay — emotions can get intense sometimes. I'm not going anywhere, and I'm not going to judge you for how you're feeling. Whenever you're ready, I'd really like to understand what's been going on for you. What's been making things feel so difficult?";
        }
    }

    // ── Advice requests — give actual tips ──
    $advice_triggers = ['advice', 'advise', 'what should i do', 'help me', 'tips', 'suggest', 'how do i', 'how to', 'what can i', 'what do i', 'give me', 'tell me how'];
    foreach ($advice_triggers as $trigger) {
        if (str_contains($m, $trigger)) {
            // Detect the topic and give specific advice
            if (str_contains($m, 'stress') || str_contains($m, 'overwhelm') || str_contains($m, 'pressure')) {
                return "Here are some practical things that can help with stress:\n• Break big tasks into smaller steps — one at a time\n• Take 5-minute breaks every 45 minutes when studying\n• Try a quick breathing exercise: inhale 4 counts, hold 4, exhale 4\n• Write down your worries in the Private Mood Journal to get them out of your head\n• Talk to someone — even a short chat can lighten the load\nWhich of these feels most doable for you right now?";
            }
            if (str_contains($m, 'sleep') || str_contains($m, 'insomnia')) {
                return "For better sleep, here are a few things that genuinely help:\n• Set a consistent sleep and wake time — even on weekends\n• Avoid screens 30 minutes before bed (phones, laptops)\n• Keep your room cool and dark\n• Try the Mindfulness Corner for a calming bedtime breathing exercise\n• Avoid caffeine after 3pm\nHow does your current sleep routine look?";
            }
            if (str_contains($m, 'anxi') || str_contains($m, 'nervous') || str_contains($m, 'panic')) {
                return "When anxiety shows up, these can really help:\n• Ground yourself: name 5 things you can see, 4 you can touch, 3 you can hear\n• Try box breathing: inhale 4s, hold 4s, exhale 4s, hold 4s — repeat\n• Challenge the worry: ask yourself 'Is this likely? What's the worst that could happen?'\n• Use the Mindfulness Corner in your dashboard for guided exercises\n• Limit caffeine and social media when anxiety is high\nWould you like to try one of these right now?";
            }
            if (str_contains($m, 'sad') || str_contains($m, 'depress') || str_contains($m, 'low') || str_contains($m, 'empty')) {
                return "When you're feeling low, even small steps matter:\n• Get outside for even 10 minutes — sunlight and movement help\n• Reach out to one person today, even just to say hi\n• Write down three things — no matter how small — that went okay today\n• Be gentle with yourself; you don't have to feel okay all the time\n• If it's been going on for a while, talking to a counselor can really help — you can book one or leave an Anonymous Quick Note\nWhat feels most possible for you today?";
            }
            // General advice
            return "I'm glad you're looking for ways to help yourself — that's a really positive step. Here are some general wellbeing tips:\n• Talk about what you're feeling — to a friend, journal, or here with me\n• Move your body — even a short walk can shift your mood\n• Rest without guilt — your mind needs recovery time\n• Use the portal tools: Mood Journal, Mindfulness Corner, or Anonymous Quick Note\n• If things feel too heavy to carry alone, reach out to a counselor\nWhat area would you like more specific advice on?";
        }
    }

    // Closing after enough turns
    if ($turn >= 7) {
        return "Thank you so much for opening up today — that really takes courage. You've shared a lot, and it all matters. If anything feels heavy after this, please consider checking in with a trusted adult or your school counselor. You deserve support and care.";
    }

    $emotions = [
        ['words' => ['suicid', 'hurt myself', 'end it all', 'want to die', 'hopeless', 'can\'t go on'],
            'reply' => "I'm really glad you said that out loud — it means you're not facing this completely alone. You deserve help with feelings this big. If you're not ready to talk to a person yet, you can send an Anonymous Quick Note to our counselor team through the portal. But please, if you feel unsafe right now, reach out to a trusted adult or a crisis helpline immediately. Can you share a bit more about what's making things feel so heavy?"],

        ['words' => ['burnout', 'burn out', 'exhausted', 'drained', 'can\'t anymore', 'no energy', 'done'],
            'reply' => "That sounds like a deep level of exhaustion — the kind that builds up when you've been giving so much for so long. One thing that can help is giving yourself permission to rest without guilt, because rest is not laziness. Try breaking your day into smaller chunks with proper breaks in between. When did you first start feeling this drained?"],

        ['words' => ['suffocate', 'suffocated', 'trapped', 'stuck', 'no way out', 'can\'t breathe', 'choked'],
            'reply' => "It sounds like things have been feeling really tight and overwhelming, almost like there isn't space to breathe. When that happens, try this: pause, take 3 slow deep breaths, and name just ONE small thing you can do right now — even something tiny. What do you think is making you feel the most stuck right now?"],

        ['words' => ['stress', 'stressed', 'pressure', 'overwhelm', 'too much', 'so much'],
            'reply' => "I hear how much pressure you're under. Some things that genuinely help: break your tasks into smaller steps, schedule short breaks, and if your mind won't stop racing, try writing everything down in your Mood Journal to clear your head. What's the biggest source of pressure right now — school, family, or something else?"],

        ['words' => ['anxious', 'anxiety', 'worried', 'worry', 'nervous', 'panic', 'fear', 'scared', 'dread'],
            'reply' => "Anxiety can make even ordinary things feel much heavier. One technique that helps immediately is box breathing — inhale slowly for 4 counts, hold for 4, exhale for 4, hold for 4. You can also try grounding yourself by naming 5 things around you that you can see. The Mindfulness Corner in your dashboard has more exercises like this. What triggers your anxiety the most?"],

        ['words' => ['sad', 'sadness', 'low', 'depressed', 'depression', 'down', 'crying', 'cry', 'tears', 'empty', 'numb'],
            'reply' => "Thank you for trusting me with that. Feeling low or empty is really hard, and it's okay to not be okay. One small thing that can help is writing how you feel in the Private Mood Journal — sometimes getting it out of your head onto paper brings a little relief. Has this been going on for a while, or did something specific trigger it?"],

        ['words' => ['tired', 'fatigue', 'sleep', 'sleeping', 'insomnia', 'can\'t sleep', 'no sleep'],
            'reply' => "Poor sleep affects everything — mood, concentration, and how we handle stress. A few things that help: try to sleep and wake at the same time each day, avoid screens for 30 minutes before bed, and try a short relaxation exercise from the Mindfulness Corner. How long has sleep been a problem for you?"],

        ['words' => ['angry', 'anger', 'frustrated', 'frustrat', 'annoyed', 'irritated', 'mad', 'rage'],
            'replies' => ["I can hear a lot of frustration in what you're sharing, and that's valid. When anger gets intense, it helps to step away briefly — even a 5-minute walk can take the edge off. Once you're a little calmer, try journaling what triggered you. Anger usually points to something important. What do you feel is really at the heart of this?"]],

        ['words' => ['don\'t know', 'not sure', 'confused', 'can\'t explain', 'hard to say', 'idk', 'unsure'],
            'replies' => ["That's completely okay — feelings can be messy and hard to put into words. We can start wherever feels easiest. What's been taking up the most space in your head lately, even if it doesn't fully make sense yet?"]],

        ['words' => ['friend', 'friends', 'relationship', 'boyfriend', 'girlfriend', 'partner', 'crush', 'social'],
            'replies' => [
                "Relationships, whether with friends or partners, can be a huge source of both joy and stress. What's been on your mind regarding your social life?",
                "It sounds like your relationships are an important part of your world. How are things feeling in that area lately?",
                "Connecting with others is so vital. Is there something specific about your friendships or relationships you'd like to explore?"
            ]],

        ['words' => ['family', 'parents', 'mom', 'dad', 'brother', 'sister', 'home'],
            'replies' => [
                "Family dynamics can be really complex and impact us deeply. What's been happening at home that's on your mind?",
                "Our families often shape so much of who we are. How are things with your family affecting you right now?",
                "It takes courage to talk about family matters. Is there a particular situation or feeling related to your family you'd like to share?"
            ]],

        ['words' => ['future', 'career', 'college', 'university', 'path', 'goals', 'plan'],
            'replies' => [
                "Thinking about the future can bring both excitement and a lot of pressure. What aspects of your future are you contemplating?",
                "It's natural to feel a mix of emotions when considering your path ahead. What are some of your hopes or concerns about the future?",
                "Planning for what's next can be a big undertaking. What steps are you thinking about taking, or what feels uncertain?"
            ]],

        ['words' => ['health', 'sick', 'pain', 'body', 'doctor', 'hospital'],
            'replies' => [
                "Your physical health is so connected to your mental well-being. How are you feeling in your body lately?",
                "It sounds like you might be dealing with some physical challenges. How is that impacting your daily life and mood?",
                "Taking care of our bodies is a form of self-care. Is there anything specific about your health you'd like to discuss?"
            ]],

        ['words' => ['money', 'job', 'work', 'finances', 'afford', 'cost'],
            'replies' => [
                "Financial worries can add a significant layer of stress. What's been on your mind regarding money or work?",
                "It's completely understandable to feel concerned about finances. How are these worries affecting your overall well-being?",
                "Managing money can be tough, especially as a student. Is there a particular financial challenge you're facing?"
            ]],

        ['words' => ['bored', 'boring', 'nothing to do', 'stuck in a rut'],
            'replies' => [
                "Feeling bored or stuck can sometimes be a sign that you're ready for something new. What do you think might help shake things up?",
                "It sounds like you're in a bit of a rut. Sometimes even small changes can make a big difference. What's one thing you've been curious to try?",
                "Boredom can be surprisingly draining. Is there anything you used to enjoy that you haven't done in a while?"
            ]],

        ['words' => ['change', 'new', 'different', 'transition'],
            'replies' => [
                "Change, even positive change, can bring a lot of feelings with it. What kind of changes are you experiencing or thinking about?",
                "Transitions can be challenging, but they also offer opportunities for growth. How are you navigating this new phase?",
                "It sounds like you're in a period of change. What's the most exciting or most daunting part of it for you?"
            ]],

        ['words' => ['hope', 'hopeful', 'optimistic', 'positive outlook'],
            'replies' => [
                "It's wonderful to hear that you're feeling hopeful! What's contributing to this positive outlook?",
                "Holding onto hope is incredibly powerful. What are you looking forward to or feeling optimistic about?",
                "That's a beautiful sentiment. What helps you maintain a sense of hope, even when things are tough?"
            ]],

        ['words' => ['grateful', 'thankful', 'appreciate'],
            'replies' => [
                "Practicing gratitude is such a powerful tool for well-being. What are you feeling grateful for today?",
                "It's lovely to hear you're focusing on appreciation. What's something small or big that you're thankful for?",
                "That's a wonderful perspective. How does focusing on gratitude impact your overall mood?"
            ]],

        ['words' => ['goal', 'achieve', 'succeed', 'improve', 'progress'],
            'replies' => [
                "Setting goals and working towards them can be incredibly motivating. What are you hoping to achieve?",
                "It sounds like you're focused on growth and improvement. What's one step you're considering taking towards your goal?",
                "That's a fantastic mindset! What kind of progress are you hoping to make, and what support might help you get there?"
            ]],

        ['words' => ['identity', 'who am i', 'self-discovery', 'purpose'],
            'replies' => [
                "Exploring your identity and purpose is a profound journey. What questions are you asking yourself right now?",
                "It's a powerful thing to reflect on who you are and who you want to be. What aspects of yourself are you discovering?",
                "This journey of self-discovery is unique to everyone. What's one thing you've learned about yourself recently?"
            ]],

        ['words' => ['mindfulness', 'meditation', 'calm', 'peace'],
            'replies' => [
                "It sounds like you're exploring ways to find inner calm. What practices are you interested in or already using?",
                "Mindfulness can be a wonderful tool for managing stress and finding peace. How has it been helping you?",
                "That's a great focus. Our Mindfulness Corner has some guided exercises if you'd like to try one. What kind of calm are you seeking?"
            ]],

        ['words' => ['counselor', 'therapist', 'help', 'support'],
            'replies' => [
                "It's incredibly brave to consider reaching out for professional support. What makes you think about talking to a counselor?",
                "Seeking help is a sign of strength, not weakness. What kind of support are you looking for, and how can I help you explore that?",
                "I'm glad you're thinking about getting support. Remember, you can always send an Anonymous Quick Note to our counselors if you're not ready for a direct meeting yet."
            ]],
    ];

    foreach ($emotions as $group) {
        foreach ($group['words'] as $word) {
            if (str_contains($m, $word)) {
                return $group['replies'][array_rand($group['replies'])];
            }
        }
    }

    // Turn-based guidance (with more variety)
    $turnResponses = [
        0 => [
            "Hi there! I'm Aria, and I'm here to listen without judgment. How have you been feeling lately — emotionally, mentally, or just in general?",
            "Hello! I'm Aria. I'm here whenever you need someone to talk to. What's been on your mind today?",
            "Hi! I'm so glad you reached out. I'm Aria. How's your day been going so far?"
        ],
        1 => [
            "I appreciate you sharing that. It sounds like there's a lot on your plate. What has been affecting you the most lately?",
            "Thank you for being so open with me. How is that making you feel right now in this moment?",
            "I'm listening. Does this feel like a new challenge for you, or something you've been dealing with for a while?"
        ],
        2 => [
            "I'm really listening. When these feelings come up, how do they affect your energy or focus?",
            "That's a lot to handle. How are you coping when things get particularly difficult?",
            "It's brave of you to talk about this. Do you find these thoughts coming up more at a certain time of day?"
        ],
        3 => [
            "On a scale of 1 to 10 — 1 being very calm, 10 being extremely overwhelmed — where would you put yourself right now?",
            "If you had to pick one word to describe your current headspace, what would it be?",
            "I want to make sure I understand. What kind of support do you feel like you need most right now—just to vent, or some practical tips?"
        ],
        4 => [
            "That number tells me a lot. Is there one specific situation, person, or responsibility that feels like the biggest contributor to how you're feeling?",
            "Thank you for sharing that. What do you think is the root cause of these feelings?",
            "It sounds like there's a central theme here. Can you tell me more about what's at the heart of it?"
        ],
        5 => [
            "Thank you for sharing that. When things get this heavy, what has helped you even a little in the past? Also, our Mindfulness Corner has quick breathing tools if you'd like to try something right now.",
            "It's important to acknowledge what's helped you before. What coping strategies have you found useful?",
            "I'm here to help you find ways to navigate this. Have you explored any of the tools in our portal, like the Mood Journal or Mindfulness Corner?"
        ],
        6 => [
            "Everything you've shared matters. If you're not ready for an in-person appointment, you can always leave an Anonymous Quick Note for our counselors. Is there any specific advice or support you'd like before we wrap up?",
            "I truly appreciate you opening up to me. Remember, you don't have to carry this alone. What kind of support would feel most helpful to you right now?",
            "As we near the end of our chat, I want to remind you that your well-being is important. Is there anything else you'd like to discuss or any resources I can point you to?"
        ],
    ];

    $index = min($turn, count($turnResponses) - 1);
    $pool = $turnResponses[$index] ?? $turnResponses[count($turnResponses)-1];
    return $pool[array_rand($pool)];
}

// If no real key is configured, fall back to local engine
if (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
    echo json_encode(['reply' => smartCounselorReply($userMessage, $turnCount)]);
    exit;
}

// ── Build Gemini request ───────────────────────────────
$systemPrompt = "You are Aria, a compassionate and professional mental health support assistant for students using " . SITE_TITLE . ". "
    . "You are speaking with a student named {$name}. Your role is to listen warmly, validate feelings, AND provide helpful, practical advice when the student asks for it or would benefit from it.\n\n"
    . "Conversation guidelines:\n"
    . "- When the student is venting or sharing feelings, first acknowledge and validate what they said, then ask ONE thoughtful open-ended question to deepen the conversation.\n"
    . "- When the student ASKS for advice, tips, suggestions, or 'what should I do', give them clear, concrete, actionable advice. Do not just ask more questions — actually help them. You may include short bullet points only when listing practical tips.\n"
    . "- Balanced listening AND advising. A good counselor doesn't just ask questions — they also share coping strategies, reframing techniques, study tips, relaxation methods, or communication advice as appropriate.\n"
    . "- CRITICAL: Always acknowledge exactly what the student just said before moving to a new topic or question. If they mention a hobby, a movie, or a specific event, speak about that first.\n"
    . "- Use supportive, counselor-style language: 'It sounds like…', 'I'm hearing that…', 'That must be really hard', 'One thing that might help is…', 'Have you tried…'.\n"
    . "- Keep responses warm and human — not too long, not too short. Aim for 3–6 sentences or a short bulleted list when giving advice.\n"
    . "- Never diagnose, never prescribe medication, and never claim to replace a real counselor.\n"
    . "- If the student uses profanity, swear words, or curses at you: stay calm and professional. Do NOT repeat the offensive words. Gently acknowledge that they seem frustrated or upset, and redirect the conversation with empathy. Example: 'It sounds like you're really frustrated right now — that's completely okay. I'm here for you. Can you tell me more about what's been going on?'\n"
    . "- If the student is rude or hostile, remain kind and non-reactive. Set a gentle boundary if needed: 'I understand you're having a tough time. I'm here to help, and I work best when we can talk calmly together.'\n"
    . "- If the student mentions self-harm, suicidal thoughts, or feeling unsafe: acknowledge with extra care, validate their pain, and strongly encourage reaching out to a counselor, trusted adult, or crisis helpline. Mention the 'Anonymous Quick Note' feature for those not ready for a meeting.\n"
    . "- Suggest portal tools naturally when relevant: 'Private Mood Journal' for tracking feelings, 'Mindfulness Corner' for breathing/relaxation exercises, 'Anonymous Quick Note' for messaging counselors anonymously. Never mention .php filenames.\n"
    . "- After 6–8 meaningful exchanges, gently summarise the main themes discussed and encourage real-world support if needed.";

$contents = [];
$contents[] = [
    'role' => 'user',
    'parts' => [['text' => 'You are ' . $systemPrompt . ' Please acknowledge your role.']],
];
$contents[] = [
    'role' => 'model',
    'parts' => [['text' => "Understood. I'm Aria, your mental health support companion. I'm here to listen with care and without judgment."]],
];

foreach ($history as $turn) {
    if (!isset($turn['role'], $turn['content'])) {
        continue;
    }
    $role = $turn['role'] === 'assistant' ? 'model' : 'user';
    $contents[] = [
        'role' => $role,
        'parts' => [['text' => $turn['content']]],
    ];
}

$contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

$payload = [
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.93,
        'maxOutputTokens' => 280,
        'topP' => 0.95,
    ],
];

// ── Call Gemini via shared helper ─────────────────────
$response_text = callGemini($payload);

// Any failure — fall back to local smart counselor
if ($response_text === false) {
    // Log first message as starting a session
    if ($turnCount === 0) {
        logActivity($user_id, 'Student started AI chat session (fallback counselor)');
    }

    $reply = smartCounselorReply($userMessage, $turnCount);

    // SAVE ARIA'S REPLY (FALLBACK)
    $stmt_save_aria = $conn->prepare("INSERT INTO chat_history (student_id, sender, message) VALUES (?, 'aria', ?)");
    $stmt_save_aria->bind_param("is", $user_id, $reply);
    $stmt_save_aria->execute();

    echo json_encode([
        'reply' => $reply,
        'debug' => 'Gemini call failed — using local counselor',
    ]);
    exit;
}

$reply = trim($response_text);

if (empty(trim($reply))) {
    if ($turnCount === 0) {
        logActivity($user_id, 'Student started AI chat session (empty Gemini reply)');
    }

    $reply = smartCounselorReply($userMessage, $turnCount);

    // SAVE ARIA'S REPLY (FALLBACK)
    $stmt_save_aria = $conn->prepare("INSERT INTO chat_history (student_id, sender, message) VALUES (?, 'aria', ?)");
    $stmt_save_aria->bind_param("is", $user_id, $reply);
    $stmt_save_aria->execute();

    echo json_encode([
        'reply' => $reply,
        'debug' => 'Empty Gemini reply — using local counselor',
    ]);
    exit;
}

// SAVE ARIA'S REPLY (GEMINI)
$stmt_save_aria = $conn->prepare("INSERT INTO chat_history (student_id, sender, message) VALUES (?, 'aria', ?)");
$stmt_save_aria->bind_param("is", $user_id, $reply);
$stmt_save_aria->execute();

// Log first exchange as starting a chat session
if ($turnCount === 0) {
    logActivity($user_id, 'Student started AI chat session');
}

echo json_encode([
    'reply' => trim($reply),
]);
exit;
