<?php
require_once 'config.php';
requireStudent();

if (!isset($_GET['score_id']))
    redirect('my_reports.php');

$score_id = intval($_GET['score_id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT overall_score, depression_score, anxiety_score, stress_score, risk_level, assessment_date
     FROM assessment_scores WHERE score_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $score_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0)
    redirect('my_reports.php');
$score = $result->fetch_assoc();

// Latest counselor note
$notes_stmt = $conn->prepare(
    "SELECT note_text, recommendation, follow_up_date, created_at
     FROM counselor_notes WHERE student_id = ?
     ORDER BY created_at DESC LIMIT 1"
);
$notes_stmt->bind_param("i", $user_id);
$notes_stmt->execute();
$counselor_note = $notes_stmt->get_result()->fetch_assoc();

function getSeverityInfo($val)
{
    if ($val < 5)
        return ['label' => 'Minimal', 'cls' => 'sev-minimal'];
    if ($val < 10)
        return ['label' => 'Mild', 'cls' => 'sev-mild'];
    if ($val < 15)
        return ['label' => 'Moderate', 'cls' => 'sev-moderate'];
    return ['label' => 'Severe', 'cls' => 'sev-severe'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Report — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.3">
    <?php include 'theme_init.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 900px; padding-top: 1.5rem; padding-bottom: 3rem;">
    <div class="report-view" style="animation: fadeInUp 0.6s ease-out;">
        
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border);" class="no-print">
            <div>
                <nav style="margin-bottom: 0.75rem;">
                    <a href="my_reports.php" class="btn-link" style="font-size: 0.85rem; color: var(--primary); text-decoration: none; font-weight: 600;">← Back to Reports</a>
                </nav>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Wellness Analysis Report</h1>
                <div style="display: flex; gap: 1rem; color: var(--text-muted); font-weight: 500; font-size: 0.88rem;">
                    <span>📅 Issued: <?php echo date('M d, Y', strtotime($score['assessment_date'])); ?></span>
                    <span>📑 Ref: #WA-<?php echo str_pad($score_id, 5, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
            <button onclick="window.print()" class="btn-primary" style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); border: none; background: var(--primary); color: white; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">
                <span>🖨️</span> PDF Export
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <!-- Overall Score Card -->
            <div class="card" style="padding: 2rem; background: var(--surface-solid); position: relative; overflow: hidden; border-radius: var(--radius);">
                <div style="position: absolute; top: -10px; right: -10px; font-size: 5rem; opacity: 0.05; font-weight: 900; color: var(--primary);">01</div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 1.5rem;">Wellbeing Index</h2>
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 100px; height: 100px; border-radius: 50%; border: 6px solid var(--surface-2); display: flex; align-items: center; justify-content: center; position: relative; background: var(--surface-solid);">
                        <span style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo $score['overall_score']; ?></span>
                        <div style="position: absolute; bottom: 15px; font-size: 0.6rem; font-weight: 600; color: var(--text-dim);">PTS</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem;">Status:</div>
                        <div style="padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 700; background: var(--surface-2); color: var(--text); border: 1px solid var(--border);">
                           <?php echo $score['risk_level']; ?> Risk
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Text -->
            <div class="card" style="padding: 2rem; border-left: 4px solid var(--primary); border-radius: var(--radius);">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 1rem;">Summary</h2>
                <p style="font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; font-weight: 400;">
                    Your baseline of <strong><?php echo $score['overall_score']; ?>/100</strong> indicates a <strong><?php echo strtolower($score['risk_level']); ?></strong> level of distress. 
                    This metric is derived from clinical markers tracked over the assessment period.
                </p>
            </div>
        </div>

        <!-- Detailed Metrics -->
        <div class="card" style="padding: 2.5rem; margin-bottom: 1.5rem; border-radius: var(--radius); background: var(--surface-solid);">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.6rem; color: var(--text);">
                Dimensional Breakdown
            </h2>

            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <?php
$cats = [
    ['name' => 'Depression Indicators', 'score' => $score['depression_score'], 'icon' => '☁️', 'desc' => 'Mood, energy levels, and general interest.'],
    ['name' => 'Anxiety Markers', 'score' => $score['anxiety_score'], 'icon' => '⚡', 'desc' => 'Nervousness, physical tension, and worry.'],
    ['name' => 'Stress Perception', 'score' => $score['stress_score'], 'icon' => '🌪️', 'desc' => 'Feeling overwhelmed and lack of control.'],
];
foreach ($cats as $cat):
    $info = getSeverityInfo($cat['score']);
    $pct = round($cat['score'] / 20 * 100);
?>
                <div style="display: grid; grid-template-columns: 220px 1fr; gap: 2rem; align-items: start;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem;">
                            <span style="font-size: 1.1rem;"><?php echo $cat['icon']; ?></span>
                            <span style="font-weight: 700; color: var(--text); font-size: 0.95rem;"><?php echo $cat['name']; ?></span>
                        </div>
                        <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; font-weight: 400;"><?php echo $cat['desc']; ?></p>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.6rem; align-items: flex-end;">
                            <span style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 1.1rem; color: var(--primary);">
                                <?php echo $cat['score']; ?> <small style="font-size: 0.72rem; opacity: 0.5; font-weight: 500;">/ 20</small>
                            </span>
                            <span style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">
                                <?php echo $info['label']; ?>
                            </span>
                        </div>
                        <div style="height: 8px; background: var(--surface-2); border-radius: 10px; overflow: hidden; border: 1px solid var(--border);">
                            <div class="score-fill" data-width="<?php echo $pct; ?>" style="width: 0%; height: 100%; background: var(--primary); border-radius: 10px; transition: width 1s ease-out;"></div>
                        </div>
                    </div>
                </div>
                <?php
endforeach; ?>
            </div>
        </div>

        <!-- Counselor Feedback -->
        <?php if ($counselor_note): ?>
        <div class="card" style="padding: 2.5rem; background: var(--surface-2); border: 1px solid var(--border); margin-bottom: 1.5rem; position: relative; border-radius: var(--radius);">
            <div style="position: absolute; top: -12px; left: 24px; background: var(--primary); color: white; padding: 0.35rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.7rem; letter-spacing: 0.04em; text-transform: uppercase; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">
                Clinical Feedback
            </div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">Recommendations</h2>
            <div style="background: var(--surface-solid); padding: 1.75rem; border-radius: var(--radius-sm); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                <p style="font-size: 0.95rem; line-height: 1.7; color: var(--text-muted); margin-bottom: 1.5rem; font-weight: 400;">
                    <?php echo nl2br(htmlspecialchars($counselor_note['note_text'])); ?>
                </p>
                <?php if ($counselor_note['recommendation']): ?>
                    <div style="background: var(--surface-2); padding: 1.25rem; border-radius: var(--radius-sm); border-left: 3px solid var(--primary); margin-bottom: 1.5rem;">
                        <strong style="color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em;">Action Plan:</strong>
                        <p style="margin-top: 0.4rem; color: var(--text); font-weight: 600; font-size: 0.92rem;"><?php echo htmlspecialchars($counselor_note['recommendation']); ?></p>
                    </div>
                <?php
    endif; ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border); color: var(--text-dim); font-size: 0.8rem; font-weight: 600;">
                    <span>Guidance Office</span>
                    <span><?php echo date('M d, Y', strtotime($counselor_note['created_at'])); ?></span>
                </div>
            </div>
        </div>
        <?php
endif; ?>

        <!-- Educational Footer -->
        <div style="background: var(--surface-2); padding: 2rem; border-radius: var(--radius); text-align: center; border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin-bottom: 0.5rem; color: var(--text);">Need to talk?</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.88rem; font-weight: 400;">Schedule a confidential session to discuss your results.</p>
            <div style="display: flex; gap: 0.75rem; justify-content: center;">
                <a href="student_appointments.php" class="btn-primary" style="padding: 0.65rem 1.5rem; border-radius: var(--radius-sm); background: var(--primary); text-decoration: none; color: white; font-weight: 600; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">Book Consultation</a>
                <a href="anonymous_notes.php" class="btn-secondary" style="padding: 0.65rem 1.5rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; background: var(--surface-solid);">Message Counselor</a>
            </div>
        </div>
    </div>
</div>

<footer class="footer no-print">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

<script>
window.addEventListener('load', function() {
    const fills = document.querySelectorAll('.score-fill[data-width]');
    setTimeout(() => fills.forEach(f => f.style.width = f.dataset.width + '%'), 300);
});
</script>
</main>
</body>
</html>
