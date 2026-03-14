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

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!isset($body['messages']) || !is_array($body['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request: messages array required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user    = getUserData($user_id);
$name    = htmlspecialchars(explode(' ', $user['full_name'])[0]);

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

// History is everything before the last user message
array_pop($messages);
$history = $messages;

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
function smartCounselorReply(string $msg, int $turn): string {
    $m = strtolower($msg);

    // ── Profanity / cursing — respond with calm empathy ──
    $profanity = ['fuck','shit','damn','ass','bitch','crap','hell','bastard','idiot','stupid','wtf','stfu','fck','f*ck','s*it'];
    foreach ($profanity as $word) {
        if (str_contains($m, $word)) {
            return "I can hear that you're feeling really frustrated right now, and that's completely okay — emotions can get intense sometimes. I'm not going anywhere, and I'm not going to judge you for how you're feeling. Whenever you're ready, I'd really like to understand what's been going on for you. What's been making things feel so difficult?";
        }
    }

    // ── Advice requests — give actual tips ──
    $advice_triggers = ['advice','advise','what should i do','help me','tips','suggest','how do i','how to','what can i','what do i','give me','tell me how'];
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
        ['words' => ['suicid','hurt myself','end it all','want to die','hopeless','can\'t go on'],
         'reply' => "I'm really glad you said that out loud — it means you're not facing this completely alone. You deserve help with feelings this big. If you're not ready to talk to a person yet, you can send an Anonymous Quick Note to our counselor team through the portal. But please, if you feel unsafe right now, reach out to a trusted adult or a crisis helpline immediately. Can you share a bit more about what's making things feel so heavy?"],

        ['words' => ['burnout','burn out','exhausted','drained','can\'t anymore','no energy','done'],
         'reply' => "That sounds like a deep level of exhaustion — the kind that builds up when you've been giving so much for so long. One thing that can help is giving yourself permission to rest without guilt, because rest is not laziness. Try breaking your day into smaller chunks with proper breaks in between. When did you first start feeling this drained?"],

        ['words' => ['suffocate','suffocated','trapped','stuck','no way out','can\'t breathe','choked'],
         'reply' => "It sounds like things have been feeling really tight and overwhelming, almost like there isn't space to breathe. When that happens, try this: pause, take 3 slow deep breaths, and name just ONE small thing you can do right now — even something tiny. What do you think is making you feel the most stuck right now?"],

        ['words' => ['stress','stressed','pressure','overwhelm','too much','so much'],
         'reply' => "I hear how much pressure you're under. Some things that genuinely help: break your tasks into smaller steps, schedule short breaks, and if your mind won't stop racing, try writing everything down in your Mood Journal to clear your head. What's the biggest source of pressure right now — school, family, or something else?"],

        ['words' => ['anxious','anxiety','worried','worry','nervous','panic','fear','scared','dread'],
         'reply' => "Anxiety can make even ordinary things feel much heavier. One technique that helps immediately is box breathing — inhale slowly for 4 counts, hold for 4, exhale for 4, hold for 4. You can also try grounding yourself by naming 5 things around you that you can see. The Mindfulness Corner in your dashboard has more exercises like this. What triggers your anxiety the most?"],

        ['words' => ['sad','sadness','low','depressed','depression','down','crying','cry','tears','empty','numb'],
         'reply' => "Thank you for trusting me with that. Feeling low or empty is really hard, and it's okay to not be okay. One small thing that can help is writing how you feel in the Private Mood Journal — sometimes getting it out of your head onto paper brings a little relief. Has this been going on for a while, or did something specific trigger it?"],

        ['words' => ['tired','fatigue','sleep','sleeping','insomnia','can\'t sleep','no sleep'],
         'reply' => "Poor sleep affects everything — mood, concentration, and how we handle stress. A few things that help: try to sleep and wake at the same time each day, avoid screens for 30 minutes before bed, and try a short relaxation exercise from the Mindfulness Corner. How long has sleep been a problem for you?"],

        ['words' => ['angry','anger','frustrated','frustrat','annoyed','irritated','mad','rage'],
         'reply' => "I can hear a lot of frustration in what you're sharing, and that's valid. When anger gets intense, it helps to step away briefly — even a 5-minute walk can take the edge off. Once you're a little calmer, try journaling what triggered you. Anger usually points to something important. What do you feel is really at the heart of this?"],

        ['words' => ['school','class','classmate','teacher','professor','exam','grades','assignment','project','study','studies'],
         'reply' => "School pressure is real — it's not just about grades but also expectations from yourself and others. Some practical tips: prioritize your tasks by urgency, study in focused 45-minute blocks with short breaks, and don't be afraid to ask for help from teachers or groupmates. What part of school is weighing on you the most?"],

        ['words' => ['lonely','alone','isolated','no one','nobody','friendless','left out'],
         'reply' => "Feeling alone — even when surrounded by people — is one of the hardest feelings. One small step: reach out to even one person today, it doesn't have to be a deep conversation. You might also consider leaving a note for a counselor through the Anonymous Quick Note feature if you're not ready to talk face to face. Is there anyone you feel safe with, even a little?"],

        ['words' => ['good','great','fine','okay','alright','happy','better','well','positive','grateful'],
         'reply' => "It's really good to hear that! Even on better days, it's worth checking in with yourself. Is there anything on your mind — even something small — you'd like to talk through or any advice you're looking for?"],

        ['words' => ['don\'t know','not sure','confused','can\'t explain','hard to say','idk','unsure'],
         'reply' => "That's completely okay — feelings can be messy and hard to put into words. We can start wherever feels easiest. What's been taking up the most space in your head lately, even if it doesn't fully make sense yet?"],
    ];

    foreach ($emotions as $group) {
        foreach ($group['words'] as $word) {
            if (str_contains($m, $word)) {
                return $group['reply'];
            }
        }
    }

    // Turn-based guidance
    $turnResponses = [
        0 => "Hi there! I'm Aria, and I'm here to listen without judgment. There are no right or wrong answers here — just an honest conversation. To start, how have you been feeling lately — emotionally, mentally, or just in general?",
        1 => "I appreciate you sharing that. It sounds like there's quite a lot going on. What has been affecting you the most — school, relationships, family, or something else?",
        2 => "I'm really listening. When these feelings come up during your day, how do they affect your energy, focus, or motivation to do things?",
        3 => "That helps me understand better. On a scale of 1 to 10 — 1 being very calm, 10 being extremely overwhelmed — where would you put yourself right now?",
        4 => "That number tells me a lot. Is there one specific situation, person, or responsibility that feels like the biggest contributor to how you're feeling?",
        5 => "Thank you for sharing that. When things get this heavy, what has helped you even a little in the past? Also, our Mindfulness Corner has quick breathing tools if you'd like to try something right now.",
        6 => "Everything you've shared matters. If you're not ready for an in-person appointment, you can always leave an Anonymous Quick Note for our counselors. Is there any specific advice or support you'd like before we wrap up?",
    ];

    return $turnResponses[min($turn, 6)];
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
    . "- Balance listening AND advising. A good counselor doesn't just ask questions — they also share coping strategies, reframing techniques, study tips, relaxation methods, or communication advice as appropriate.\n"
    . "- Use supportive, counselor-style language: 'It sounds like…', 'I'm hearing that…', 'That must be really hard', 'One thing that might help is…', 'Have you tried…'.\n"
    . "- Keep responses warm and human — not too long, not too short. Aim for 3–6 sentences or a short bulleted list when giving advice.\n"
    . "- Never diagnose, never prescribe medication, and never claim to replace a real counselor.\n"
    . "- If the student uses profanity, swear words, or curses at you: stay calm and professional. Do NOT repeat the offensive words. Gently acknowledge that they seem frustrated or upset, and redirect the conversation with empathy. Example: 'It sounds like you're really frustrated right now — that's completely okay. I'm here for you. Can you tell me more about what's been going on?'\n"
    . "- If the student is rude or hostile, remain kind and non-reactive. Set a gentle boundary if needed: 'I understand you're having a tough time. I'm here to help, and I work best when we can talk calmly together.'\n"
    . "- If the student mentions self-harm, suicidal thoughts, or feeling unsafe: acknowledge with extra care, validate their pain, and strongly encourage reaching out to a counselor, trusted adult, or crisis helpline. Mention the 'Anonymous Quick Note' feature for those not ready for a meeting.\n"
    . "- Suggest portal tools naturally when relevant: 'Private Mood Journal' for tracking feelings, 'Mindfulness Corner' for breathing/relaxation exercises, 'Anonymous Quick Note' for messaging counselors anonymously. Never mention .php filenames.\n"
    . "- After 6–8 meaningful exchanges, gently summarise the main themes discussed and encourage real-world support if needed.";

$contents   = [];
$contents[] = [
    'role'  => 'user',
    'parts' => [['text' => 'You are ' . $systemPrompt . ' Please acknowledge your role.']],
];
$contents[] = [
    'role'  => 'model',
    'parts' => [['text' => "Understood. I'm Aria, your mental health support companion. I'm here to listen with care and without judgment."]],
];

foreach ($history as $turn) {
    if (!isset($turn['role'], $turn['content'])) {
        continue;
    }
    $role = $turn['role'] === 'assistant' ? 'model' : 'user';
    $contents[] = [
        'role'  => $role,
        'parts' => [['text' => $turn['content']]],
    ];
}

$contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

$payload = [
    'contents'         => $contents,
    'generationConfig' => [
        'temperature'     => 0.93,
        'maxOutputTokens' => 280,
        'topP'            => 0.95,
    ],
];

// ── Call Gemini with simple retry (handles 429) ────────
$maxRetries  = 2;
$retryDelays = [2, 4]; // seconds
$response    = false;
$httpCode    = 0;
$curlError   = '';

for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
    if ($attempt > 0) {
        usleep($retryDelays[$attempt - 1] * 1_000_000);
    }

    $ch = curl_init(GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 20,
        // Disable SSL peer verification — needed on XAMPP/Windows which lacks CA bundle
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response !== false && $httpCode !== 429) {
        break;
    }
}

// Any failure — fall back to local smart counselor
if ($response === false || $httpCode === 429 || $httpCode !== 200) {
    $debugMsg = $httpCode === 429 ? 'Rate limited — using local counselor' : "HTTP $httpCode / $curlError";

    // Log first message as starting a session
    if ($turnCount === 0) {
        logActivity($user_id, 'Student started AI chat session (fallback counselor)');
    }

    echo json_encode([
        'reply' => smartCounselorReply($userMessage, $turnCount),
        'debug' => $debugMsg,
    ]);
    exit;
}

$data  = json_decode($response, true);
$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (empty(trim($reply))) {
    if ($turnCount === 0) {
        logActivity($user_id, 'Student started AI chat session (empty Gemini reply)');
    }

    echo json_encode([
        'reply' => smartCounselorReply($userMessage, $turnCount),
        'debug' => 'Empty Gemini reply — using local counselor',
    ]);
    exit;
}

// Log first exchange as starting a chat session
if ($turnCount === 0) {
    logActivity($user_id, 'Student started AI chat session');
}

echo json_encode([
    'reply' => trim($reply),
]);
exit;
