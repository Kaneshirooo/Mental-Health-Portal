<?php
require_once 'config.php';
requireLogin();
if (!isCounselor() && !isAdmin())
    redirect('unauthorized.php');

if (!isset($_GET['user_id']))
    redirect(isAdmin() ? 'head_counselor_appointments.php' : 'student_list.php');

$student_id = intval($_GET['user_id']);
$counselor_id = $_SESSION['user_id'];

$student = getUserData($student_id);
if (!$student || $student['user_type'] !== 'student')
    redirect('student_list.php');

// Assessment history for chart
$history_stmt = $conn->prepare(
    "SELECT score_id, overall_score, depression_score, anxiety_score, stress_score, risk_level, assessment_date
     FROM assessment_scores WHERE user_id = ? ORDER BY assessment_date ASC"
);
$history_stmt->bind_param("i", $student_id);
$history_stmt->execute();
$history_rows = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle note submission
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $note_text = sanitize($_POST['note_text']);
    $recommendation = sanitize($_POST['recommendation']);
    $follow_up_date = sanitize($_POST['follow_up_date']);

    if (!empty($note_text)) {
        $ins = $conn->prepare(
            "INSERT INTO counselor_notes (counselor_id, student_id, note_text, recommendation, follow_up_date)
             VALUES (?, ?, ?, ?, ?)"
        );
        $ins->bind_param("iisss", $counselor_id, $student_id, $note_text, $recommendation, $follow_up_date);
        $ins->execute() ? ($success_msg = 'Note added successfully') && queueToast($success_msg, 'success', 'Note Saved') : null;
    }
}

// Notes (all, newest first)
$notes_stmt = $conn->prepare(
    "SELECT note_id, note_text, recommendation, follow_up_date, created_at
     FROM counselor_notes WHERE student_id = ? ORDER BY created_at DESC"
);
$notes_stmt->bind_param("i", $student_id);
$notes_stmt->execute();
$notes_rows = $notes_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Chart data
$chart_labels = array_map(fn($r) => date('M d', strtotime($r['assessment_date'])), $history_rows);
$chart_scores = array_map(fn($r) => (int)$r['overall_score'], $history_rows);

// Avatar initials
$initials = '';
foreach (explode(' ', $student['full_name']) as $part)
    $initials .= strtoupper($part[0] ?? '');
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($student['full_name']); ?> — Profile</title>
    <link rel="stylesheet" href="styles.css?v=2.1">
    <?php include 'theme_init.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="width: 64px; height: 64px; border-radius: var(--radius-sm); background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; font-family: 'Outfit', sans-serif;">
                <?php echo $initials; ?>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem;">
                    <div style="font-weight: 600; color: var(--primary); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em;">Clinical Case Profile</div>
                    <button id="generateAISummary" class="btn-sm" style="padding: 0.25rem 0.65rem; border-radius: 4px; background: var(--surface-solid); border: 1.5px solid var(--primary); color: var(--primary); font-weight: 700; font-size: 0.65rem; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; transition: var(--transition-fast);">
                        ✨ Generate AI Summary
                    </button>
                </div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($student['full_name']); ?></h1>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="padding: 0.35rem 0.85rem; border-radius: 4px; background: var(--surface-2); border: 1px solid var(--border); font-weight: 600; font-size: 0.72rem; color: var(--text-muted);">
                        ID: <?php echo htmlspecialchars($student['roll_number'] ?: 'N/A'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.25rem;">Last Session</div>
            <div style="font-weight: 700; color: var(--text); font-size: 0.95rem;"><?php echo !empty($history_rows) ? date('M d, Y', strtotime($history_rows[count($history_rows) - 1]['assessment_date'])) : 'No record'; ?></div>
        </div>
    </div>    <!-- AI Summary Result Container -->
    <div id="aiSummaryBox" style="display: none; margin-bottom: 2.5rem; background: var(--surface-solid); border: 1.5px solid var(--border); border-radius: var(--radius); padding: 2rem; position: relative; animation: fadeInUp 0.5s ease;">
        <button onclick="document.getElementById('aiSummaryBox').style.display='none'" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.25rem; color: var(--text-dim); cursor: pointer;">×</button>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
            <span style="font-size: 1.5rem;">✨</span>
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--primary); margin: 0;">AI Clinical Insight</h3>
            <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-dim); background: var(--surface-2); padding: 0.2rem 0.6rem; border-radius: 4px;">Gemini Powered</span>
        </div>
        <div id="aiSummaryContent" style="color: var(--text); line-height: 1.7; font-size: 0.95rem; white-space: pre-wrap;">
            Loading clinical assessment...
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 4rem; align-items: start; margin-bottom: 6rem;">
        
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <!-- Diagnostic Mirror -->
            <?php if (count($history_rows) > 0): ?>
            <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);">Longitudinal Trajectory</h2>
                <div style="height: 300px; margin-bottom: 2rem;">
                    <canvas id="trendChart"></canvas>
                </div>

                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text);">Clinical History</h3>
                <div class="table-wrapper">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <th style="padding: 0.75rem 0; text-align: left; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Assessment Date</th>
                                <th style="padding: 0.75rem 0; text-align: center; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Diagnostic Score</th>
                                <th style="padding: 0.75rem 0; text-align: right; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($history_rows) as $r): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text); font-size: 0.88rem;"><?php echo date('M d, Y', strtotime($r['assessment_date'])); ?></td>
                                <td style="padding: 0.75rem 0; text-align: center;">
                                    <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;"><?php echo $r['overall_score']; ?><span style="font-size: 0.75rem; opacity: 0.4;">/100</span></div>
                                </td>
                                <td style="padding: 0.75rem 0; text-align: right;">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-weight: 700; font-size: 0.65rem; background: var(--risk-<?php echo strtolower($r['risk_level']); ?>-glow, #f8fafc); color: var(--risk-<?php echo strtolower($r['risk_level']); ?>, var(--text-muted));">
                                        <?php echo strtoupper($r['risk_level']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php
    endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
else: ?>
                <div style="padding: 4rem 2rem; text-align: center; background: var(--surface-2); border-radius: var(--radius); border: 2px dashed var(--border);">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">📊</div>
                    <h3 style="color: var(--text-muted); font-weight: 600; font-size: 1rem;">No clinical data available.</h3>
                </div>
            <?php
endif; ?>
        </div>

        <!-- Add note form -->
        <div class="card" style="padding: 2rem; border-radius: var(--radius);">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 1.5rem;">Add Clinical Note</h2>
            <form method="POST">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="note_text" style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Clinical Observations *</label>
                    <textarea id="note_text" name="note_text" rows="4" required style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2);"
                               placeholder="Enter clinical observations…"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="recommendation" style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Intervention Plan</label>
                    <textarea id="recommendation" name="recommendation" rows="2" style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2);"
                               placeholder="Recommended next steps…"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="follow_up_date" style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Next Contact Date</label>
                    <input type="date" id="follow_up_date" name="follow_up_date" style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2);">
                </div>
                <button type="submit" name="add_note" class="btn-primary" style="width: 100%; padding: 0.75rem; border-radius: var(--radius-sm); background: var(--primary); color: white; border: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">💾 Archive Session Note</button>
            </form>
        </div>
    </div>

    <!-- Previous notes list (expandable) -->
    <?php if (!empty($notes_rows)): ?>
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
            <h2 style="margin-bottom:0">Previous Notes (<?php echo count($notes_rows); ?>)</h2>
            <button class="btn-sm btn-secondary" onclick="toggleNotes()">Toggle All</button>
        </div>
        <div class="notes-list notes-expandable" id="notesList">
            <?php foreach ($notes_rows as $note): ?>
            <div class="note-item">
                <p class="note-date">📅 <?php echo date('M d, Y H:i A', strtotime($note['created_at'])); ?></p>
                <p><strong>Note:</strong> <?php echo nl2br(htmlspecialchars($note['note_text'])); ?></p>
                <?php if ($note['recommendation']): ?>
                    <p><strong>Recommendation:</strong> <?php echo htmlspecialchars($note['recommendation']); ?></p>
                <?php
        endif; ?>
                <?php if ($note['follow_up_date']): ?>
                    <p><strong>Follow-up:</strong> <?php echo date('M d, Y', strtotime($note['follow_up_date'])); ?></p>
                <?php
        endif; ?>
            </div>
            <?php
    endforeach; ?>
        </div>
    </div>
    <?php
endif; ?>

    <a href="student_list.php" class="btn-secondary">← Back to Student List</a>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

<script>
<?php if (count($history_rows) > 1): ?>
new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Overall Wellness Score',
            data: <?php echo json_encode($chart_scores); ?>,
            borderColor: '#0d9488',
            backgroundColor: 'rgba(13,148,136,0.06)',
            tension: 0.35, fill: true,
            pointBackgroundColor: '#0d9488',
            pointRadius: 4, pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, max: 100, grid: { color: getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});
<?php
endif; ?>

function toggleNotes() {
    const el = document.getElementById('notesList');
    el.style.maxHeight = el.style.maxHeight ? '' : 'none';
}

// AI Summary Logic
document.getElementById('generateAISummary')?.addEventListener('click', async function() {
    const btn = this;
    const box = document.getElementById('aiSummaryBox');
    const content = document.getElementById('aiSummaryContent');
    const studentId = <?php echo $student_id; ?>;

    btn.disabled = true;
    btn.innerHTML = '✨ Processing...';
    box.style.display = 'block';
    content.innerHTML = '<div class="skeleton" style="height: 100px; border-radius: 8px;"></div>';

    try {
        const response = await fetch('counselor_ai_summary_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: studentId })
        });
        const data = await response.json();
        
        if (data.success) {
            content.innerHTML = data.summary;
        } else {
            content.innerHTML = '<span style="color: #dc2626;">Error generating summary: ' + (data.error || 'Unknown error') + '</span>';
        }
    } catch (err) {
        content.innerHTML = '<span style="color: #dc2626;">Connection failed. Please try again.</span>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '✨ Generate AI Summary';
    }
});
</script>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
