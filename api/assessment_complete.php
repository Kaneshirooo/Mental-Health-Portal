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
    <?php require_once 'pwa_head.php'; ?>
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

<div class="container" style="max-width: 900px; padding-top: 6rem; padding-bottom: 8rem;">
    
    <div style="text-align: center; margin-bottom: 5rem; animation: fadeInUp 0.8s ease-out;">
        <div style="font-size: 5rem; margin-bottom: 1.5rem;">✨</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem;">Insight Generated</h1>
        <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">Your clinical reflection has been analyzed. Here are your personalized findings.</p>
    </div>

    <div style="background: white; border-radius: 40px; border: 1px solid var(--border); box-shadow: var(--shadow); padding: 4rem; position: relative; overflow: hidden; animation: fadeInUp 1s ease-out;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 10px; background: <?php echo $risk_colors[$score['risk_level']]; ?>;"></div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; margin-bottom: 5rem;">
            <div style="text-align: center;">
                <div style="width: 200px; height: 200px; border-radius: 50%; border: 12px solid #f1f5f9; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
                    <div style="font-size: 4rem; font-weight: 800; color: <?php echo $risk_colors[$score['risk_level']]; ?>; line-height: 1;"><?php echo $display_score; ?></div>
                    <div style="font-size: 1rem; font-weight: 800; color: var(--text-dim); margin-top: 0.25rem;">/ 100</div>
                    <div style="position: absolute; inset: -12px; border-radius: 50%; border: 12px solid <?php echo $risk_colors[$score['risk_level']]; ?>; clip-path: inset(0 0 <?php echo 100 - $display_score; ?>% 0);"></div>
                </div>
                <div style="font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 2rem; font-size: 0.9rem;">WELLBEING INDEX</div>
            </div>

            <div>
                <div style="display: inline-block; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 1.5rem; 
                    background: <?php echo $risk_colors[$score['risk_level']]; ?>20; color: <?php echo $risk_colors[$score['risk_level']]; ?>;">
                    <?php echo $score['risk_level']; ?> RISK PROFILE
                </div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--text); margin-bottom: 1.5rem; line-height: 1.2;">Clinical Trajectory Identified.</h2>
                <p style="color: var(--text-dim); font-size: 1.1rem; line-height: 1.6; font-weight: 600;"><?php echo $recommendations[$score['risk_level']]; ?></p>
                
                <div style="margin-top: 2.5rem; display: flex; gap: 1rem;" class="no-print">
                    <button onclick="window.print()" style="padding: 1rem 1.5rem; border-radius: 14px; border: 1.5px solid var(--border); background: white; font-weight: 800; cursor: pointer;">DOWNLOAD PDF</button>
                    <a href="my_reports.php" style="padding: 1rem 1.5rem; border-radius: 14px; border: 1.5px solid var(--border); background: white; font-weight: 800; color: var(--text); text-decoration: none;">FULL HISTORY</a>
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 4rem;">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; margin-bottom: 3rem;">Dimension Breakdown</h3>
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

        <div style="margin-top: 5rem; padding: 3rem; background: #f8fafc; border-radius: 32px; display: flex; align-items: center; justify-content: space-between;" class="no-print">
            <div style="max-width: 450px;">
                <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.5rem;">Need more clarity?</h4>
                <p style="color: var(--text-dim); font-weight: 600; font-size: 1rem;">Schedule a 1-on-1 session with our clinical experts to discuss your results in depth.</p>
            </div>
            <a href="student_appointments.php" style="padding: 1.25rem 2.5rem; background: var(--primary); color: white; border-radius: 50px; font-weight: 800; text-decoration: none; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);">BOOK CONSULTATION →</a>
        </div>
    </div>

    <div style="margin-top: 4rem; text-align: center;" class="no-print">
        <a href="student_dashboard.php" style="font-weight: 800; color: var(--text-dim); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <span>🏠</span> RETURN TO DASHBOARD
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
