<?php
require_once 'config.php';
require_once 'ai_config.php';
requireStudent();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$user    = getUserData($user_id);
$name    = htmlspecialchars(explode(' ', $user['full_name'])[0]);

// Fetch the absolute latest mood log
$stmt = $conn->prepare("SELECT mood_score, mood_emoji, note, logged_at FROM mood_logs WHERE student_id = ? ORDER BY logged_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$mood = $stmt->get_result()->fetch_assoc();

if (!$mood) {
    echo json_encode(['success' => false, 'error' => 'No mood logs found yet. Log your mood to get a recommendation!']);
    exit;
}

$prompt = "You are Aria, a mental health AI. "
    . "A student named {$name} just logged their mood: {$mood['mood_emoji']} (Score: {$mood['mood_score']}/5) with the note: \"{$mood['note']}\".\n\n"
    . "Recommend ONE of these specific mindfulness exercises on our portal:\n"
    . "1. 4-7-8 Breathing (for stress/insomnia)\n"
    . "2. 5-4-3-2-1 Grounding (for anxiety/panic)\n"
    . "3. Body Scan Meditation (for physical tension/burnout)\n\n"
    . "Instructions:\n"
    . "- Explain precisely WHY you are recommending this specific one based on their mood/note.\n"
    . "- Keep it very short and supportive (2 sentences max).\n"
    . "- If they are feeling 'Great' (5/5), suggest a quick breathing session to maintain the positive energy.";

$payload = [
    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 100,
    ]
];

$recommendation = callGemini($payload);

if ($recommendation === false) {
    echo json_encode(['success' => false, 'error' => 'Aria is busy right now. Try the 4-7-8 Breathing!']);
    exit;
}

echo json_encode([
    'success' => true,
    'recommendation' => trim($recommendation)
]);
exit;
