<?php
require_once 'config.php';
requireStudent();

if (!isset($_GET['pre_id'])) redirect('student_ai_chat.php');

$pre_id = (int)$_GET['pre_id'];
$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT pre_id, conversation_transcript, form_answers, ai_report, created_at
     FROM ai_preassessments
     WHERE pre_id = ? AND student_id = ?
     LIMIT 1"
);
$stmt->bind_param("ii", $pre_id, $student_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) redirect('student_ai_chat.php');

$user = getUserData($student_id);
$report = json_decode($row['ai_report'], true) ?? [];
$form   = json_decode($row['form_answers'], true) ?? [];

$risk = $report['risk_level'] ?? 'Low';
$riskCls = 'risk-' . strtolower($risk);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Pre-Assessment Report</title>
    <link rel="stylesheet" href="styles.css?v=2.1">
    <?php include 'theme_init.php'; ?>
    <style>
        .report-wrap { max-width: 900px; margin: 0 auto; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size:.85rem; }
        .transcript { white-space: pre-wrap; background: var(--surface-2); border:1px solid var(--border); border-radius: var(--radius-sm); padding: 1rem 1.25rem; max-height: 320px; overflow:auto; }
    </style>
</head>
<body>
<nav class="navbar no-print">
    <div class="navbar-brand">Mental Health Portal</div>
    <button class="nav-toggle" onclick="document.querySelector('.navbar-menu').classList.toggle('open')">☰</button>
    <div class="navbar-menu">
        <a href="student_ai_chat.php" class="nav-link">Talk to Aria</a>
        <a href="student_appointments.php" class="nav-link">Appointments</a>
        <a href="student_dashboard.php" class="nav-link">Dashboard</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="report-wrap">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem" class="no-print">
            <div>
                <h1 style="margin-bottom:.2rem">AI Pre-Assessment Report</h1>
                <p class="text-muted">Generated: <?php echo date('M d, Y · g:i A', strtotime($row['created_at'])); ?></p>
            </div>
            <button onclick="window.print()" class="btn-secondary">🖨 Print / Save PDF</button>
        </div>

        <div class="card report-section">
            <h2>Student Information</h2>
            <div class="table-wrapper">
                <table class="table">
                    <tbody>
                        <tr><th>Name</th><td><?php echo htmlspecialchars($user['full_name']); ?></td></tr>
                        <tr><th>Roll No.</th><td><?php echo htmlspecialchars($user['roll_number'] ?: '—'); ?></td></tr>
                        <tr><th>Department</th><td><?php echo htmlspecialchars($user['department'] ?: '—'); ?></td></tr>
                        <tr><th>Semester</th><td><?php echo htmlspecialchars($user['semester'] ?: '—'); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card report-section">
            <h2>AI Summary</h2>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;margin-bottom:.75rem">
                <span class="risk-badge <?php echo htmlspecialchars($riskCls); ?>">
                    <?php echo htmlspecialchars($risk); ?> Risk
                </span>
                <span class="text-muted">Mood: <strong><?php echo htmlspecialchars($report['mood'] ?? '—'); ?></strong></span>
                <span class="text-muted">Stress: <strong><?php echo htmlspecialchars($report['stress_level'] ?? '—'); ?>/10</strong></span>
                <span class="text-muted">Energy: <strong><?php echo htmlspecialchars($report['energy_level'] ?? '—'); ?>/10</strong></span>
            </div>
            <div class="recommendation-box">
                <p><?php echo htmlspecialchars($report['summary'] ?? '—'); ?></p>
            </div>
        </div>

        <?php if (!empty($form) && (isset($form['mood_now']) && $form['mood_now'] !== '')): ?>
        <div class="card report-section">
            <h2>Student Self-Report (Form)</h2>
            <div class="table-wrapper">
                <table class="table">
                    <tbody>
                        <tr><th>Mood right now</th><td><?php echo htmlspecialchars($form['mood_now'] ?? '—'); ?></td></tr>
                        <tr><th>Stress level</th><td><?php echo htmlspecialchars($form['stress_level'] ?? '—'); ?>/10</td></tr>
                        <tr><th>Energy level</th><td><?php echo htmlspecialchars($form['energy_level'] ?? '—'); ?>/10</td></tr>
                        <tr><th>Sleep quality</th><td><?php echo htmlspecialchars($form['sleep_quality'] ?? '—'); ?>/5</td></tr>
                        <tr><th>Main concern</th><td><?php echo htmlspecialchars($form['main_concern'] ?? '—'); ?></td></tr>
                        <tr><th>Self-harm thoughts</th><td><?php echo !empty($form['self_harm_thoughts']) ? 'Yes' : 'No'; ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="card report-section">
            <h2>Key Concerns</h2>
            <?php if (!empty($report['key_concerns']) && is_array($report['key_concerns'])): ?>
                <ul style="margin-left:1.2rem">
                    <?php foreach ($report['key_concerns'] as $c): ?>
                        <li><?php echo htmlspecialchars($c); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">—</p>
            <?php endif; ?>
        </div>

        <div class="card report-section">
            <h2>Recommendations</h2>
            <?php if (!empty($report['recommendations']) && is_array($report['recommendations'])): ?>
                <ul style="margin-left:1.2rem">
                    <?php foreach ($report['recommendations'] as $r): ?>
                        <li><?php echo htmlspecialchars($r); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">—</p>
            <?php endif; ?>
        </div>

        <div class="card report-section">
            <h2>Conversation Transcript</h2>
            <div class="transcript mono"><?php echo htmlspecialchars($row['conversation_transcript']); ?></div>
        </div>

        <div class="action-buttons no-print">
            <a href="student_appointments.php" class="btn-primary">📅 Book an Appointment</a>
            <a href="student_ai_chat.php" class="btn-secondary">← Back to Aria</a>
        </div>
    </div>
</div>

<footer class="footer no-print">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>
</body>
</html>

