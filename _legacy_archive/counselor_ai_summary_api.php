<?php
require_once 'config.php';
require_once 'ai_config.php';

header('Content-Type: application/json');

// Security check
if (!isCounselor() && !isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$student_id = intval($input['student_id'] ?? 0);

if ($student_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid student ID']);
    exit;
}

// 1. Fetch Student Info
$student = getUserData($student_id);
if (!$student) {
    echo json_encode(['success' => false, 'error' => 'Student not found']);
    exit;
}

// 2. Fetch Latest Assessments (Last 5)
$assessment_query = "SELECT overall_score, depression_score, anxiety_score, stress_score, risk_level, assessment_date 
                    FROM assessment_scores WHERE user_id = ? ORDER BY assessment_date DESC LIMIT 5";
$stmt = $conn->prepare($assessment_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$assessments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Fetch Recent Mood Logs (Last 10)
$mood_query = "SELECT mood_score, note, logged_at FROM mood_logs WHERE student_id = ? ORDER BY logged_at DESC LIMIT 10";
$stmt = $conn->prepare($mood_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$moods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. Fetch Recent Counselor Notes (Last 3)
$notes_query = "SELECT note_text, recommendation, created_at FROM counselor_notes WHERE student_id = ? ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($notes_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$past_notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 5. Construct Prompt
$prompt = "Act as a clinical psychologist providing a concise student wellness summary for a fellow counselor. 
Based on the following data for student: " . $student['full_name'] . " (ID: " . $student['roll_number'] . ").

### RECENT ASSESSMENTS:
";
foreach ($assessments as $a) {
    $prompt .= "- Date: " . $a['assessment_date'] . " | Overall: " . $a['overall_score'] . "% | Risk: " . $a['risk_level'] . " (D: " . $a['depression_score'] . ", A: " . $a['anxiety_score'] . ", S: " . $a['stress_score'] . ")\n";
}

$prompt .= "\n### RECENT MOOD LOGS & NOTES:
";
foreach ($moods as $m) {
    $prompt .= "- Date: " . $m['logged_at'] . " | Score: " . $m['mood_score'] . " | Note: " . ($m['note'] ?: 'No note') . "\n";
}

$prompt .= "\n### PREVIOUS CLINICAL NOTES:
";
foreach ($past_notes as $n) {
    $prompt .= "- Date: " . $n['created_at'] . " | Note: " . $n['note_text'] . " | Rec: " . $n['recommendation'] . "\n";
}

$prompt .= "\nINSTRUCTIONS:
Provide a professional, concise clinical summary (max 250 words) including:
1. **Current Status**: Brief overview of recent trends (improving/worsening).
2. **Key Risk Factors**: Any highlighted symptoms or patterns (e.g., high anxiety consistently).
3. **Clinical Recommendation**: Suggested focus areas for the next session.

Format using bolding for key terms. Avoid generic advice.";

// 6. Call Gemini
$response = callGemini($prompt);

if ($response) {
    echo json_encode(['success' => true, 'summary' => $response]);
}
else {
    echo json_encode(['success' => false, 'error' => 'AI Service unavailable']);
}
