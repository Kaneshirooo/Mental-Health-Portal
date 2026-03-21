<?php
/**
 * mindfulness_ai_api.php
 * Generates a personalized 1-minute mindfulness script based on student mood.
 */

require_once 'config.php';
require_once 'ai_config.php';
requireStudent();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$mood = trim($input['mood'] ?? 'neutral');

$moodPrompts = [
    'stressed'  => 'feeling very stressed and overwhelmed with school pressure.',
    'anxious'   => 'feeling anxious and having racing thoughts.',
    'sad'       => 'feeling low, sad, or a bit lonely today.',
    'tired'     => 'feeling physically and mentally exhausted.',
    'neutral'   => 'feeling okay but wanting to maintain their peace.',
    'happy'     => 'feeling good and wanting to savor this positive moment.'
];

$context = $moodPrompts[$mood] ?? $moodPrompts['neutral'];

$prompt = "You are a mindfulness and meditation guide for students. "
    . "A student is currently {$context} "
    . "Create a unique, personalized 1-minute mindfulness exercise for them. "
    . "Structure it as a short script they can read or follow. "
    . "Include:\n"
    . "- A gentle opening sentence.\n"
    . "- A specific breathing or grounding instruction (e.g., 'notice the weight of your feet', 'inhale the calm').\n"
    . "- A closing positive affirmation.\n"
    . "Keep it warm, soothing, and strictly under 100 words. Do not use markdown like bolding or headers; just plain text with line breaks.";

$payload = [
    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
    'generationConfig' => [
        'temperature' => 0.9,
        'maxOutputTokens' => 200,
    ]
];

$script = callGemini($payload);

if ($script === false) {
    echo json_encode(['success' => false, 'error' => 'The Zen garden is being watered. Please try again in a moment.']);
    exit;
}

echo json_encode([
    'success' => true,
    'script' => trim($script)
]);
exit;
