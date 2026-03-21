<?php
/**
 * student_mood_insight_api.php
 * Generates a compassionate AI insight based on the student's recent mood logs.
 */

require_once 'config.php';
require_once 'ai_config.php';
requireStudent();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$user    = getUserData($user_id);
$name    = htmlspecialchars(explode(' ', $user['full_name'])[0]);

// Fetch last 7 mood entries
$stmt = $conn->prepare("SELECT mood_score, mood_emoji, note, logged_at FROM mood_logs WHERE student_id = ? ORDER BY logged_at DESC LIMIT 7");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($history)) {
    echo json_encode(['success' => false, 'error' => 'Not enough entries yet. Log a few moods first!']);
    exit;
}

// Build the prompt
$history_text = "";
foreach (array_reverse($history) as $entry) {
    $date = date('M d', strtotime($entry['logged_at']));
    $note = $entry['note'] ?: "(No note)";
    $history_text .= "- {$date}: Mood {$entry['mood_score']}/5 ({$entry['mood_emoji']}). Note: {$note}\n";
}

$prompt = "You are Aria, a compassionate AI mental health companion. "
    . "Below is a student's recent mood journal history (last few days). "
    . "Analyze the trends and provide a short, supportive, and 100% judgment-free insight. "
    . "Acknowledge their feelings, offer a tiny bit of encouragement, and highlight any positive small steps they've taken. "
    . "Limit your response to exactly 2-3 warm, human-like sentences. Address the student as {$name}.\n\n"
    . "STUDENT HISTORY:\n" . $history_text;

$payload = [
    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
    'generationConfig' => [
        'temperature' => 0.8,
        'maxOutputTokens' => 150,
    ]
];

$insight = callGemini($payload);

if ($insight === false) {
    echo json_encode(['success' => false, 'error' => 'Aria is resting right now. Please try again later.']);
    exit;
}

echo json_encode([
    'success' => true,
    'insight' => trim($insight)
]);
exit;
