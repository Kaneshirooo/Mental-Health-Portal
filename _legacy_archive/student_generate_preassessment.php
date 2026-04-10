<?php
require_once 'config.php';
require_once 'ai_config.php';
requireStudent();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid or missing CSRF token']);
    exit;
}

$transcript = trim($input['transcript'] ?? '');
$form = $input['form'] ?? null;

if ($transcript === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Transcript is required']);
    exit;
}

$student_id = $_SESSION['user_id'];
$user = getUserData($student_id);
$name = htmlspecialchars(explode(' ', $user['full_name'])[0]);

function localPreAssessmentFallback(string $transcript, array $form = []): array {
    $t = strtolower($transcript);
    $stress = (int)($form['stress_level'] ?? 5);
    $energy = (int)($form['energy_level'] ?? 5);
    $sleep  = (int)($form['sleep_quality'] ?? 3); // 1-5
    $safety = !empty($form['self_harm_thoughts']);

    $risk = 'Low';
    if ($safety || str_contains($t, 'hurt myself') || str_contains($t, 'suicid') || str_contains($t, 'want to die')) {
        $risk = 'Critical';
    } elseif ($stress >= 8 || $energy <= 3 || $sleep <= 2 || str_contains($t, 'hopeless')) {
        $risk = 'High';
    } elseif ($stress >= 6 || $sleep <= 3 || str_contains($t, 'anxious') || str_contains($t, 'depressed')) {
        $risk = 'Moderate';
    }

    $mood = 'neutral';
    if ($risk === 'Critical' || $risk === 'High') $mood = 'concerning';
    elseif ($risk === 'Moderate') $mood = 'low';

    return [
        'mood' => $mood,
        'risk_level' => $risk,
        'stress_level' => max(1, min(10, $stress)),
        'energy_level' => max(1, min(10, $energy)),
        'summary' => 'This pre-assessment was generated from your conversation and self-report. It suggests your current stress/energy levels and highlights areas that may benefit from support.',
        'key_concerns' => array_values(array_filter([
            $stress >= 7 ? 'High stress reported' : null,
            $sleep <= 2 ? 'Sleep difficulties reported' : null,
            $safety ? 'Safety concerns indicated' : null,
        ])),
        'recommendations' => [
            'Consider booking a session with your guidance counselor.',
            'Try a short grounding or breathing exercise today (2–3 minutes).',
        ],
        'follow_up_needed' => in_array($risk, ['High', 'Critical'], true),
    ];
}

// Normalize/validate form inputs (optional)
$form_norm = [];
if (is_array($form)) {
    $form_norm = [
        'mood_now' => trim((string)($form['mood_now'] ?? '')),
        'stress_level' => (int)($form['stress_level'] ?? 0),
        'energy_level' => (int)($form['energy_level'] ?? 0),
        'sleep_quality' => (int)($form['sleep_quality'] ?? 0),
        'main_concern' => trim((string)($form['main_concern'] ?? '')),
        'self_harm_thoughts' => !empty($form['self_harm_thoughts']),
    ];
}

// If no real key is configured, return fallback
if (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
    $report = localPreAssessmentFallback($transcript, $form_norm);
    $stmt = $conn->prepare("INSERT INTO ai_preassessments (student_id, conversation_transcript, form_answers, ai_report) VALUES (?,?,?,?)");
    $form_json = json_encode($form_norm);
    $rep_json  = json_encode($report);
    $stmt->bind_param("isss", $student_id, $transcript, $form_json, $rep_json);
    $stmt->execute();
    echo json_encode(['success' => true, 'pre_id' => $conn->insert_id, 'report' => $report, 'fallback' => true]);
    exit;
}

$formBlock = '';
if (!empty($form_norm)) {
    $formBlock = "STUDENT SELF-REPORT (FORM ANSWERS):\n" . json_encode($form_norm, JSON_PRETTY_PRINT) . "\n\n";
}

$prompt = <<<PROMPT
You are generating a student wellness pre-assessment for a school mental health portal.
Analyze the student's conversation with Aria (AI support) and the student's self-report answers (if provided).

Return ONLY valid JSON with EXACTLY these fields:
{
  "mood": "<one of: positive, neutral, low, concerning>",
  "risk_level": "<one of: Low, Moderate, High, Critical>",
  "stress_level": <integer 1-10, estimate from chat if form missing>,
  "energy_level": <integer 1-10, estimate from chat if form missing>,
  "summary": "<2-3 sentences plain English summary of their mental state based on the chat>",
  "key_concerns": ["<concern 1>", "<concern 2>"],
  "recommendations": ["<recommendation 1>", "<recommendation 2>"],
  "follow_up_needed": <true or false>
}

Guidelines:
- If 'STUDENT SELF-REPORT' is missing or incomplete, derive the scores and mood purely from the CONVERSATION TRANSCRIPT.
- Be cautious: if self-harm / suicidal ideation is present, set risk_level to Critical and follow_up_needed to true.
- Keep key_concerns and recommendations practical and non-medical.
- Do NOT diagnose. Do NOT mention that you are an AI model. Do NOT output markdown. Do NOT refer to specific file names (like .php).

STUDENT NAME: {$name}

{$formBlock}CONVERSATION TRANSCRIPT:
{$transcript}
PROMPT;

$payload = [
    'contents' => [[
        'role' => 'user',
        'parts' => [['text' => $prompt]]
    ]],
    'generationConfig' => [
        'temperature' => 0.3,
        'maxOutputTokens' => 700,
        'responseMimeType' => 'application/json',
    ]
];

// ── Call Gemini via shared helper ─────────────────────
$response_text = callGemini($payload);

if ($response_text === false) {
    $report = localPreAssessmentFallback($transcript, $form_norm);
    $stmt = $conn->prepare("INSERT INTO ai_preassessments (student_id, conversation_transcript, form_answers, ai_report) VALUES (?,?,?,?)");
    $form_json = json_encode($form_norm);
    $rep_json  = json_encode($report);
    $stmt->bind_param("isss", $student_id, $transcript, $form_json, $rep_json);
    $stmt->execute();
    echo json_encode(['success' => true, 'pre_id' => $conn->insert_id, 'report' => $report, 'fallback' => true, 'debug' => "Gemini call failed"]);
    exit;
}

$report = json_decode($response_text, true);

if (!$report || !isset($report['mood'], $report['risk_level'])) {
    $report = localPreAssessmentFallback($transcript, $form_norm);
    $stmt = $conn->prepare("INSERT INTO ai_preassessments (student_id, conversation_transcript, form_answers, ai_report) VALUES (?,?,?,?)");
    $form_json = json_encode($form_norm);
    $rep_json  = json_encode($report);
    $stmt->bind_param("isss", $student_id, $transcript, $form_json, $rep_json);
    $stmt->execute();
    echo json_encode(['success' => true, 'pre_id' => $conn->insert_id, 'report' => $report, 'fallback' => true, 'raw' => $rawText]);
    exit;
}

// Persist
$stmt = $conn->prepare("INSERT INTO ai_preassessments (student_id, conversation_transcript, form_answers, ai_report) VALUES (?,?,?,?)");
$form_json = json_encode($form_norm);
$rep_json  = json_encode($report);
$stmt->bind_param("isss", $student_id, $transcript, $form_json, $rep_json);
$stmt->execute();

echo json_encode([
    'success' => true,
    'pre_id' => $conn->insert_id,
    'report' => $report,
]);
exit;

