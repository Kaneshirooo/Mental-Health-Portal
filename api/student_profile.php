<?php
require_once 'config.php';
requireCounselor();

if (!isset($_GET['user_id'])) redirect('student_list.php');

$student_id   = intval($_GET['user_id']);
$counselor_id = $_SESSION['user_id'];

$student = getUserData($student_id);
if (!$student || $student['user_type'] !== 'student') redirect('student_list.php');

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
    $note_text    = sanitize($_POST['note_text']);
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
foreach (explode(' ', $student['full_name']) as $part) $initials .= strtoupper($part[0] ?? '');
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($student['full_name']); ?> — Profile</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1400px; padding-top: 5rem; padding-bottom: 8rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div style="display: flex; align-items: center; gap: 3.5rem;">
            <div style="width: 120px; height: 120px; border-radius: 35px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 800; font-family: 'Outfit', sans-serif; box-shadow: 0 20px 40px rgba(79, 70, 229, 0.2);">
                <?php echo $initials; ?>
            </div>
            <div>
                <div style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Clinical Case Archive</div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem;"><?php echo htmlspecialchars($student['full_name']); ?></h1>
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="padding: 0.75rem 1.75rem; border-radius: 50px; background: #f8fafc; border: 1px solid var(--border); font-weight: 800; font-size: 0.85rem; color: var(--text-dim);">
                        ID: <?php echo htmlspecialchars($student['roll_number'] ?: 'N/A'); ?>
                    </div>
                    <div style="padding: 0.75rem 1.75rem; border-radius: 50px; background: var(--primary-glow); color: var(--primary); font-weight: 800; font-size: 0.85rem;">
                        <?php echo htmlspecialchars($student['department'] ?: 'General Studies'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.5rem;">Last Interaction</div>
            <div style="font-weight: 800; color: var(--text); font-size: 1.1rem;"><?php echo !empty($history_rows) ? date('M d, Y', strtotime($history_rows[count($history_rows)-1]['assessment_date'])) : 'No record'; ?></div>
        </div>
    </div>



    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 4rem; align-items: start; margin-bottom: 6rem;">
        
        <div style="display: flex; flex-direction: column; gap: 4rem;">
            <!-- Diagnostic Mirror -->
            <?php if (count($history_rows) > 0): ?>
            <div style="background: white; border-radius: 40px; padding: 4rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; margin-bottom: 3rem; color: var(--text);">Longitudinal Trajectory</h2>
                <div style="height: 400px; margin-bottom: 4rem;">
                    <canvas id="trendChart"></canvas>
                </div>

                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; margin-bottom: 2rem; color: var(--text);">Clinical Ledger</h3>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Assessment Date</th>
                                <th style="text-align: center;">Diagnostic Score</th>
                                <th style="text-align: right;">Risk Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($history_rows) as $r): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--text);"><?php echo date('M d, Y', strtotime($r['assessment_date'])); ?></td>
                                <td style="text-align: center;">
                                    <div style="font-weight: 800; color: var(--primary); font-size: 1.1rem;"><?php echo $r['overall_score']; ?><span style="font-size: 0.8rem; opacity: 0.4;">/100</span></div>
                                </td>
                                <td style="text-align: right;">
                                    <span style="padding: 0.5rem 1.25rem; border-radius: 50px; font-weight: 800; font-size: 0.75rem; background: var(--risk-<?php echo strtolower($r['risk_level']); ?>-glow); color: var(--risk-<?php echo strtolower($r['risk_level']); ?>);">
                                        <?php echo strtoupper($r['risk_level']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <div style="padding: 6rem; text-align: center; background: #f8fafc; border-radius: 40px; border: 2.5px dashed var(--border);">
                    <div style="font-size: 4rem; margin-bottom: 2rem;">📊</div>
                    <h3 style="color: var(--text-dim); font-weight: 700; font-size: 1.25rem;">No clinical data available for this identity.</h3>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add note form -->
        <div class="card">
            <h2>Add Counselor Note</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="note_text">Clinical Notes *</label>
                    <textarea id="note_text" name="note_text" rows="5" required
                              placeholder="Enter clinical observations…"></textarea>
                </div>
                <div class="form-group">
                    <label for="recommendation">Recommendation</label>
                    <textarea id="recommendation" name="recommendation" rows="3"
                              placeholder="Optional recommendation…"></textarea>
                </div>
                <div class="form-group">
                    <label for="follow_up_date">Follow-up Date</label>
                    <input type="date" id="follow_up_date" name="follow_up_date">
                </div>
                <button type="submit" name="add_note" class="btn-primary btn-full">💾 Save Note</button>
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
                <?php endif; ?>
                <?php if ($note['follow_up_date']): ?>
                    <p><strong>Follow-up:</strong> <?php echo date('M d, Y', strtotime($note['follow_up_date'])); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <a href="student_list.php" class="btn-secondary">← Back to Student List</a>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>

<script>
<?php if (count($history_rows) > 1): ?>
new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Overall Score',
            data: <?php echo json_encode($chart_scores); ?>,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79,70,229,0.08)',
            tension: 0.4, fill: true,
            pointBackgroundColor: '#4f46e5',
            pointRadius: 5, pointHoverRadius: 7,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, max: 100, grid: { color:'rgba(0,0,0,.05)' } },
            x: { grid: { display: false } }
        }
    }
});
<?php endif; ?>

function toggleNotes() {
    const el = document.getElementById('notesList');
    el.style.maxHeight = el.style.maxHeight ? '' : 'none';
}
</script>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
