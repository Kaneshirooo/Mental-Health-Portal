<?php
/**
 * counselor_ai_api.php
 * Provides AI-suggested professional replies for counselors.
 */

require_once 'config.php';
require_once 'ai_config.php';
requireCounselor();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'suggest_reply') {
    $note_text = trim($input['note_text'] ?? '');
    if ($note_text === '') {
        echo json_encode(['success' => false, 'error' => 'No note text provided.']);
        exit;
    }

    $prompt = "You are a senior school counselor assistant. "
        . "A student has sent an anonymous note to the counseling office. "
        . "Your task is to provide ONE professional, compassionate, and supportive starting sentence or short paragraph for the counselor's reply. "
        . "Guidelines:\n"
        . "- Acknowledge the student's courage for reaching out.\n"
        . "- Use a warm, professional tone.\n"
        . "- Keep it to 1-2 sentences.\n"
        . "- Do not offer a full diagnosis; just a supportive opening.\n\n"
        . "STUDENT'S NOTE:\n\"{$note_text}\"";

    $payload = [
        'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 150,
        ]
    ];

    $suggestion = callGemini($payload);

    if ($suggestion === false) {
        echo json_encode(['success' => false, 'error' => 'Could not generate suggestion.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'suggestion' => trim($suggestion)
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
exit;
