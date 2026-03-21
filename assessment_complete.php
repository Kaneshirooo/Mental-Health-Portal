<?php
require_once 'config.php';
requireStudent();

if (!isset($_GET['score_id'])) redirect('student_dashboard.php');

$score_id = intval($_GET['score_id']);
$user_id  = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT overall_score, depression_score, anxiety_score, stress_score, risk_level, assessment_date
     FROM assessment_scores WHERE score_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $score_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) redirect('student_dashboard.php');
$score = $result->fetch_assoc();

$recommendations = [
    'Low'      => 'Your assessment indicates low risk. Continue with regular self-care and healthy habits.',
    'Moderate' => 'Your assessment indicates moderate risk. Consider speaking with a counselor for guidance.',
    'High'     => 'Your assessment indicates high risk. We recommend scheduling a session with a counselor.',
    'Critical' => 'Your assessment indicates critical risk. Please contact a counselor immediately.',
];

$risk_colors = [
    'Low'      => '#10b981',
    'Moderate' => '#f59e0b',
    'High'     => '#f97316',
    'Critical' => '#ef4444',
];

function getSeverityLabel($val) {
    // $val is 0–20 (5 questions × max 4 pts)
    if ($val < 5)  return ['label' => 'Minimal',  'cls' => 'sev-minimal'];
    if ($val < 10) return ['label' => 'Mild',     'cls' => 'sev-mild'];
    if ($val < 15) return ['label' => 'Moderate', 'cls' => 'sev-moderate'];
    return             ['label' => 'Severe',   'cls' => 'sev-severe'];
}

$dep_info = getSeverityLabel($score['depression_score']);
$anx_info = getSeverityLabel($score['anxiety_score']);
$str_info = getSeverityLabel($score['stress_score']);

// Calculate display score from the individual dimension totals (each 0-20, max total = 60).
// Using the dimension scores is more reliable than overall_score which depends on
// category-name string matching during submission.
$raw_total     = $score['depression_score'] + $score['anxiety_score'] + $score['stress_score'];
$display_score = ($raw_total > 0 || $score['overall_score'] > 0)
    ? min(100, round(($raw_total / 60) * 100))
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Complete — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        @media print {
            .navbar, .action-buttons, .no-print, footer { display:none!important; }
            body { background:#fff; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 800px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">✨</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Insight Generated</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Your clinical reflection has been analyzed. Here are the personalized findings.</p>
    </div>

    <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 2rem; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: <?php echo $risk_colors[$score['risk_level']]; ?>;"></div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center; margin-bottom: 3rem;">
            <div style="text-align: center;">
                <div style="width: 140px; height: 140px; border-radius: 50%; border: 8px solid #f1f5f9; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto; position: relative;">
                    <div style="font-size: 2.25rem; font-weight: 700; color: <?php echo $risk_colors[$score['risk_level']]; ?>; line-height: 1;"><?php echo $display_score; ?></div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-top: 0.15rem;">/ 100</div>
                    <div style="position: absolute; inset: -8px; border-radius: 50%; border: 8px solid <?php echo $risk_colors[$score['risk_level']]; ?>; clip-path: inset(0 0 <?php echo 100 - $display_score; ?>% 0);"></div>
                </div>
                <div style="font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 1.5rem; font-size: 0.7rem;">Wellness Index</div>
            </div>

            <div>
                <div style="display: inline-block; padding: 0.35rem 0.85rem; border-radius: 6px; font-weight: 700; font-size: 0.72rem; text-transform: uppercase; margin-bottom: 1rem; 
                    background: <?php echo $risk_colors[$score['risk_level']]; ?>15; color: <?php echo $risk_colors[$score['risk_level']]; ?>;">
                    <?php echo $score['risk_level']; ?> Risk Profile
                </div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 0.75rem;">Analysis Complete</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; font-weight: 400;"><?php echo $recommendations[$score['risk_level']]; ?></p>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;" class="no-print">
                    <button onclick="window.print()" class="btn-sm btn-secondary" style="padding: 0.5rem 1rem;">Export PDF</button>
                    <a href="my_reports.php" class="btn-sm btn-secondary" style="padding: 0.5rem 1rem; text-decoration: none;">View History</a>
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 2rem;">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 1.5rem;">Dimension Breakdown</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                <?php
                $dims = [
                    ['label' => 'Depression', 'score' => $score['depression_score'], 'info' => $dep_info],
                    ['label' => 'Anxiety', 'score' => $score['anxiety_score'], 'info' => $anx_info],
                    ['label' => 'Stress', 'score' => $score['stress_score'], 'info' => $str_info],
                ];
                foreach ($dims as $dim):
                    $pct = ($dim['score'] / 20) * 100;
                ?>
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                        <div>
                            <div style="font-weight: 800; font-size: 0.85rem; color: var(--text); margin-bottom: 0.25rem;"><?php echo $dim['label']; ?></div>
                            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;"><?php echo $dim['info']['label']; ?></div>
                        </div>
                        <div style="font-weight: 800; color: var(--text);"><?php echo $dim['score']; ?><span style="font-size: 0.7rem; color: var(--text-dim); margin-left: 0.2rem;">/20</span></div>
                    </div>
                    <div style="height: 8px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                        <div class="score-fill" data-width="<?php echo $pct; ?>" style="width: 0%; height: 100%; background: var(--primary); transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="margin-top: 3rem; padding: 1.5rem; background: var(--surface-2); border-radius: var(--radius); display: flex; align-items: center; justify-content: space-between;" class="no-print">
            <div style="max-width: 400px;">
                <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--primary); margin-bottom: 0.25rem;">Seek Professional Support</h4>
                <p style="color: var(--text-muted); font-weight: 400; font-size: 0.88rem;">Speak 1-on-1 with clinical experts for a deeper analysis.</p>
            </div>
            <a href="student_appointments.php" class="btn-primary" style="padding: 0.65rem 1.5rem; border-radius: var(--radius-sm); font-size: 0.85rem; text-decoration: none; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">Book Consultation →</a>
        </div>
    </div>

    <div style="margin-top: 2rem; text-align: center;" class="no-print">
        <a href="student_dashboard.php" style="font-weight: 600; color: var(--text-muted); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.85rem;">
            <span>🏠</span> Return to Dashboard
        </a>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System. All rights reserved.</p>
</footer>

<script>
// Animate score bars on load
window.addEventListener('load', function() {
    const fills = document.querySelectorAll('.score-fill[data-width]');
    setTimeout(() => {
        fills.forEach(fill => {
            const w = fill.getAttribute('data-width');
            fill.style.width = w + '%';
        });
    }, 300);
});
</script>
</main>
</body>
</html>
