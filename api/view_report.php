<?php
require_once 'config.php';
requireStudent();

if (!isset($_GET['score_id'])) redirect('my_reports.php');

$score_id = intval($_GET['score_id']);
$user_id  = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT overall_score, depression_score, anxiety_score, stress_score, risk_level, assessment_date
     FROM assessment_scores WHERE score_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $score_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) redirect('my_reports.php');
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

function getSeverityInfo($val) {
    if ($val < 5)  return ['label' => 'Minimal',  'cls' => 'sev-minimal'];
    if ($val < 10) return ['label' => 'Mild',     'cls' => 'sev-mild'];
    if ($val < 15) return ['label' => 'Moderate', 'cls' => 'sev-moderate'];
    return             ['label' => 'Severe',   'cls' => 'sev-severe'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Report — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 900px; padding-top: 3rem; padding-bottom: 5rem;">
    <div class="report-view" style="animation: fadeInUp 0.6s ease-out;">
        
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 2px solid var(--border);" class="no-print">
            <div>
                <nav style="margin-bottom: 1rem;">
                    <a href="student_dashboard.php" class="btn-link" style="font-size: 0.9rem;">← Back to Dashboard</a>
                </nav>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.2rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.5rem;">Wellness Analysis Report</h1>
                <div style="display: flex; gap: 1.5rem; color: var(--text-muted); font-weight: 600; font-size: 0.95rem;">
                    <span>📅 Issued: <?php echo date('M d, Y', strtotime($score['assessment_date'])); ?></span>
                    <span>📑 Ref: #WA-<?php echo str_pad($score_id, 5, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
            <button onclick="window.print()" class="btn-primary" style="padding: 0.8rem 1.5rem; background: var(--text); border: none; display: flex; align-items: center; gap: 0.6rem; font-weight: 700;">
                <span>🖨</span> Print Report (PDF)
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Overall Score Card -->
            <div class="card" style="padding: 2.5rem; background: linear-gradient(135deg, white 0%, #f8fafc 100%); position: relative; overflow: hidden;">
                <div style="position: absolute; top: -20px; right: -20px; font-size: 8rem; opacity: 0.03; font-weight: 900; color: var(--primary);">01</div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2rem;">Overall Wellness Index</h2>
                <div style="display: flex; align-items: center; gap: 2rem;">
                    <div style="width: 120px; height: 120px; border-radius: 50%; border: 8px solid var(--primary-glow); display: flex; align-items: center; justify-content: center; position: relative;">
                        <span style="font-size: 2.5rem; font-weight: 800; color: var(--primary);"><?php echo $score['overall_score']; ?></span>
                        <div style="position: absolute; bottom: 20px; font-size: 0.7rem; font-weight: 700; color: var(--text-dim);">PTS</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem;">Clinical Status:</div>
                        <span class="risk-badge risk-<?php echo strtolower($score['risk_level']); ?>" style="padding: 0.6rem 1.25rem; font-size: 1rem;">
                            <i>●</i> <?php echo $score['risk_level']; ?> Risk
                        </span>
                    </div>
                </div>
            </div>

            <!-- Summary Text -->
            <div class="card" style="padding: 2.5rem; border-left: 5px solid var(--primary-light);">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1.5rem;">Analysis Summary</h2>
                <p style="font-size: 1rem; color: var(--text); line-height: 1.7;">
                    Your wellness index of <strong><?php echo $score['overall_score']; ?>/100</strong> indicates a <strong><?php echo strtolower($score['risk_level']); ?></strong> level of psychological distress. 
                    This score is based on your combined responses regarding depression, anxiety, and perceived stress levels over the past 2 weeks.
                </p>
            </div>
        </div>

        <!-- Detailed Metrics -->
        <div class="card" style="padding: 3rem; margin-bottom: 2rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">2</span>
                Dimensional Breakdown
            </h2>

            <div style="display: flex; flex-direction: column; gap: 2.5rem;">
                <?php
                $cats = [
                    ['name' => 'Depression Indicators', 'score' => $score['depression_score'], 'icon' => '☁️', 'desc' => 'Mood, energy levels, and general interest.'],
                    ['name' => 'Anxiety Markers',    'score' => $score['anxiety_score'], 'icon' => '⚡', 'desc' => 'Nervousness, physical tension, and worry.'],
                    ['name' => 'Stress Perception',   'score' => $score['stress_score'], 'icon' => '🌪️', 'desc' => 'Feeling overwhelmed and lack of control.'],
                ];
                foreach ($cats as $cat):
                    $info = getSeverityInfo($cat['score']);
                    $pct  = round($cat['score'] / 20 * 100);
                ?>
                <div style="display: grid; grid-template-columns: 240px 1fr; gap: 2rem; align-items: start;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                            <span style="font-size: 1.25rem;"><?php echo $cat['icon']; ?></span>
                            <span style="font-weight: 700; color: var(--text); font-size: 1.05rem;"><?php echo $cat['name']; ?></span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;"><?php echo $cat['desc']; ?></p>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; align-items: flex-end;">
                            <span style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.2rem; color: var(--primary);">
                                <?php echo $cat['score']; ?> <small style="font-size: 0.75rem; opacity: 0.5; font-weight: 600;">/ 20</small>
                            </span>
                            <span class="risk-badge <?php echo str_replace('sev-', 'risk-', $info['cls']); ?>" style="font-size: 0.7rem; padding: 0.35rem 0.85rem;">
                                <?php echo strtoupper($info['label']); ?>
                            </span>
                        </div>
                        <div style="height: 10px; background: #f1f5f9; border-radius: 20px; overflow: hidden; border: 1px solid var(--border);">
                            <div class="score-fill" data-width="<?php echo $pct; ?>" style="width: 0%; height: 100%; background: var(--primary); border-radius: 20px; transition: width 1.2s cubic-bezier(0.16, 1, 0.3, 1);"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Counselor Feedback -->
        <?php if ($counselor_note): ?>
        <div class="card" style="padding: 3rem; background: #fdf2f8; border: 1px solid #fbcfe8; margin-bottom: 2rem; position: relative;">
            <div style="position: absolute; top: -15px; left: 30px; background: #f43f5e; color: white; padding: 0.4rem 1.25rem; border-radius: 50px; font-weight: 800; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">
                Clinical Feedback
            </div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: #9d174d; margin-bottom: 1.5rem;">Review Results & Recommendations</h2>
            <div style="background: white; padding: 2rem; border-radius: 20px; border: 1px solid #fce7f3; box-shadow: 0 4px 12px rgba(157, 23, 77, 0.05);">
                <p style="font-size: 1.05rem; line-height: 1.8; color: #431407; margin-bottom: 1.5rem;">
                    <?php echo nl2br(htmlspecialchars($counselor_note['note_text'])); ?>
                </p>
                <?php if ($counselor_note['recommendation']): ?>
                    <div style="background: #fff5f7; padding: 1.25rem; border-radius: 12px; border-left: 4px solid #f43f5e; margin-bottom: 1.5rem;">
                        <strong style="color: #9d174d; font-size: 0.9rem; text-transform: uppercase;">Prescribed Action Plan:</strong>
                        <p style="margin-top: 0.5rem; color: #431407; font-weight: 600;"><?php echo htmlspecialchars($counselor_note['recommendation']); ?></p>
                    </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #fce7f3; color: var(--text-dim); font-size: 0.85rem; font-weight: 600;">
                    <span>Dr. Counselor Name (Auto)</span>
                    <span><?php echo date('M d, Y', strtotime($counselor_note['created_at'])); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Educational Footer -->
        <div style="background: var(--primary-glow); padding: 2.5rem; border-radius: 24px; text-align: center; border: 1px solid var(--primary-glow);">
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 0.75rem; color: var(--primary-dark);">Need to talk about these results?</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem;">Our support team is available for confidential sessions to discuss your report in detail.</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="student_appointments.php" class="btn-primary" style="padding: 0.75rem 1.75rem; background: var(--primary);">Schedule Consultation</a>
                <a href="anonymous_notes.php" class="btn-secondary" style="padding: 0.75rem 1.75rem;">Message Counselor</a>
            </div>
        </div>
    </div>
</div>

<footer class="footer no-print">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
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
