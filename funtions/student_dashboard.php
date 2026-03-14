<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];
$user    = getUserData($user_id);

// Assessment history (last 10) for chart
$history_query = "SELECT score_id, overall_score, risk_level, assessment_date
                  FROM assessment_scores
                  WHERE user_id = ? ORDER BY assessment_date ASC LIMIT 10";
$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$history_rows   = $history_result->fetch_all(MYSQLI_ASSOC);

// Latest score
$latest_score = !empty($history_rows) ? end($history_rows) : null;

// Notification count
$notif_count = getNotificationCount($user_id);

// Daily tips pool
$tips = [
    "Take a 5-minute breathing break today — inhale for 4, hold for 4, exhale for 4.",
    "Small steps count. Even a 10-minute walk can improve your mood significantly.",
    "Reach out to a friend today. Social connection is a powerful mental health booster.",
    "Stay hydrated! Dehydration can worsen anxiety and fatigue.",
    "Write down 3 things you are grateful for before you sleep tonight.",
];
$tip = $tips[date('N') % count($tips)];

// Chart data
$chart_labels = [];
$chart_scores = [];
foreach ($history_rows as $r) {
    $chart_labels[] = date('M d', strtotime($r['assessment_date']));
    $chart_scores[] = (int)$r['overall_score'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Sanctuary — Mental Health Portal</title>
    <meta name="description" content="Your student wellness sanctuary. Access resources, track patterns, and connect with support.">
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <style>
        .sanctuary-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 48px;
            padding: 5rem;
            color: white;
            margin-bottom: 6rem;
            box-shadow: 0 30px 60px rgba(67, 56, 202, 0.2);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sanctuary-banner::after {
            content: '🧘';
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 18rem;
            opacity: 0.08;
            transform: rotate(-15deg);
        }
        .vitality-card {
            background: white;
            border-radius: 36px;
            padding: 2.5rem;
            border: 1px solid var(--border);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .vitality-card:hover { transform: translateY(-10px); box-shadow: var(--shadow); border-color: var(--primary-light); }
        
        .action-sanctuary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-bottom: 6rem;
        }
        .action-card {
            background: white;
            border-radius: 40px;
            padding: 3.5rem 2.5rem;
            border: 1.5px solid var(--border);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .action-card:hover { 
            background: var(--primary-glow);
            border-color: var(--primary);
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(67, 56, 202, 0.1);
        }
        .action-icon {
            width: 70px;
            height: 70px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 2rem;
            transition: var(--transition);
        }
        .action-card:hover .action-icon { transform: scale(1.1) rotate(5deg); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1450px; padding-top: 6rem; padding-bottom: 10rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem;">Clinical Wellness Sanctuary</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.8rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem; letter-spacing: -0.02em;">Digital Peace, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>.</h1>
            <p style="color: var(--text-dim); font-size: 1.35rem; font-weight: 600;">Systems online. Resilience mapping in progress.</p>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <?php if ($notif_count > 0): ?>
            <a href="anonymous_notes.php" style="padding: 1.25rem 2.5rem; border-radius: 60px; background: var(--primary); color: white; font-weight: 800; font-size: 0.9rem; text-decoration: none; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 15px 30px rgba(67, 56, 202, 0.25);">
                🔔 <?php echo $notif_count; ?> REFLECTION UPDATE
            </a>
            <?php endif; ?>
            <div style="padding: 1.25rem 2.5rem; border-radius: 60px; background: #ecfdf5; color: #059669; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(5, 150, 105, 0.1);">
                <span style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981;"></span> SECURE CONNECTION
            </div>
        </div>
    </div>

    <div class="sanctuary-banner">
        <div style="max-width: 900px; position: relative; z-index: 1;">
            <div style="font-size: 0.85rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.25em; margin-bottom: 2rem; opacity: 0.7;">INSTITUTIONAL WELLNESS DIRECTIVE</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 3rem; font-weight: 800; line-height: 1.25; margin-bottom: 3rem;"><?php echo $tip; ?></h2>
            <div style="display: flex; gap: 1.5rem;">
                <button onclick="location.href='mindfulness.php'" style="padding: 1.25rem 3rem; border-radius: 60px; background: white; color: var(--primary); border: none; font-weight: 800; cursor: pointer; transition: var(--transition);">PRACTICE NOW</button>
                <button onclick="location.href='mental_health_resources.php'" style="padding: 1.25rem 3rem; border-radius: 60px; background: rgba(255,255,255,0.15); color: white; border: 1.5px solid rgba(255,255,255,0.3); font-weight: 800; cursor: pointer; backdrop-filter: blur(10px);">VIEW LIBRARY</button>
            </div>
        </div>
    </div>

    <!-- Vitality Matrix -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; margin-bottom: 6rem;">
        <div class="vitality-card">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem; letter-spacing: 0.05em;">Clinical Identity</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user['roll_number']); ?></div>
            <div style="font-size: 0.85rem; color: var(--primary); font-weight: 800;"><?php echo htmlspecialchars($user['department']); ?> Sector</div>
        </div>
        <div class="vitality-card">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem; letter-spacing: 0.05em;">Diagnostic Depth</div>
            <div style="font-size: 3rem; font-weight: 800; color: var(--primary); line-height: 1;"><?php echo count($history_rows); ?></div>
            <div style="font-size: 0.85rem; color: var(--text-dim); font-weight: 700; margin-top: 0.5rem;">Assessments Completed</div>
        </div>
        <?php if ($latest_score): ?>
        <div class="vitality-card">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem; letter-spacing: 0.05em;">Resilience Index</div>
            <div style="font-size: 3rem; font-weight: 800; color: var(--text); line-height: 1;"><?php echo $latest_score['overall_score']; ?><span style="font-size: 1.25rem; opacity: 0.3; margin-left: 4px;">%</span></div>
            <div style="font-size: 0.85rem; color: var(--text-dim); font-weight: 700; margin-top: 0.5rem;">Current Aggregate</div>
        </div>
        <div class="vitality-card" style="background: <?php echo ($latest_score['risk_level']==='Critical' || $latest_score['risk_level']==='High') ? '#fef2f2' : '#f0fdf4'; ?>; border-color: <?php echo ($latest_score['risk_level']==='Critical' || $latest_score['risk_level']==='High') ? '#fee2e2' : '#dcfce7'; ?>;">
            <div style="font-size: 0.8rem; font-weight: 800; color: <?php echo ($latest_score['risk_level']==='Critical' || $latest_score['risk_level']==='High') ? '#dc2626' : '#16a34a'; ?>; text-transform: uppercase; margin-bottom: 1.5rem; letter-spacing: 0.05em;">Priority Status</div>
            <div style="font-size: 2rem; font-weight: 800; color: <?php echo ($latest_score['risk_level']==='Critical' || $latest_score['risk_level']==='High') ? '#dc2626' : '#16a34a'; ?>; letter-spacing: -0.02em;"><?php echo strtoupper($latest_score['risk_level']); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sanctuary Grid -->
    <div class="action-sanctuary">
        <a href="take_assessment.php" class="action-card">
            <div class="action-icon" style="background: #f5f3ff; color: var(--primary);">📋</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 1rem; color: var(--text);">Clinical Analysis</h3>
            <p style="color: var(--text-dim); font-size: 1rem; line-height: 1.7; font-weight: 600;">Full diagnostic mapping of your current resilience state.</p>
        </a>
        <a href="student_ai_chat.php" class="action-card">
            <div class="action-icon" style="background: #ecfdf5; color: #10b981;">✨</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 1rem; color: var(--text);">Aria Catalyst</h3>
            <p style="color: var(--text-dim); font-size: 1rem; line-height: 1.7; font-weight: 600;">High-fidelity AI companion for therapeutic reflections.</p>
        </a>
        <a href="mood_journal.php" class="action-card">
            <div class="action-icon" style="background: #fffbeb; color: #f59e0b;">📔</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 1rem; color: var(--text);">Interior Ledger</h3>
            <p style="color: var(--text-dim); font-size: 1rem; line-height: 1.7; font-weight: 600;">Secure, private archival of your emotional trajectory.</p>
        </a>
        <a href="student_appointments.php" class="action-card">
            <div class="action-icon" style="background: #eff6ff; color: #3b82f6;">📅</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 1rem; color: var(--text);">Support Studio</h3>
            <p style="color: var(--text-dim); font-size: 1rem; line-height: 1.7; font-weight: 600;">Human-to-human clinical sessions on your own terms.</p>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 4rem; align-items: start;">
        <?php if (count($history_rows) > 1): ?>
        <div style="background: white; border-radius: 48px; border: 1px solid var(--border); padding: 4rem; box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 2rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2rem; margin: 0;">Longitudinal Journey</h2>
                <a href="my_reports.php" style="font-weight: 800; color: var(--primary); text-decoration: none; font-size: 0.9rem;">EXPLORE FULL DATA →</a>
            </div>
            <div style="height: 400px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <div style="background: #f8fafc; border-radius: 48px; border: 1.5px solid var(--border); padding: 4rem;">
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.5rem; margin-bottom: 2.5rem;">Diagnostic Legends</h3>
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 14px; height: 14px; border-radius: 50%; background: #10b981; box-shadow: 0 0 10px #10b981;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 0.9rem; color: var(--text);">Stable Resilience (0-30%)</div>
                        <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600;">Maintain current wellness practices.</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 14px; height: 14px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 10px #f59e0b;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 0.9rem; color: var(--text);">Moderate Concern (31-60%)</div>
                        <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600;">Aria chat or journaling recommended.</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 14px; height: 14px; border-radius: 50%; background: #ef4444; box-shadow: 0 0 10px #ef4444;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 0.9rem; color: var(--text);">Critical Intervention (60%+)</div>
                        <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600;">Immediate counselor session advised.</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 4rem; padding-top: 3rem; border-top: 1.5px solid #eef2f6;">
                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); line-height: 1.8;">
                    All data is E2E encrypted and only accessible to assigned clinical personnel.
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer" style="padding: 4rem; text-align: center; border-top: 1px solid var(--border); margin-top: 4rem;">
    <p style="color: var(--text-dim); font-weight: 700; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">© <?php echo date('Y'); ?> Mental Health Clinical Ecosystem. High-Fidelity Wellness Stewardship.</p>
</footer>

</main>

<script>
<?php if (count($history_rows) > 1): ?>
const ctx = document.getElementById('trendChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Resilience Index',
            data: <?php echo json_encode($chart_scores); ?>,
            borderColor: '#4338ca',
            borderWidth: 5,
            backgroundColor: gradient,
            tension: 0.45,
            fill: true,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#4338ca',
            pointBorderWidth: 4,
            pointRadius: 8,
            pointHoverRadius: 12,
            pointHoverBorderWidth: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                padding: 15,
                titleFont: { size: 14, weight: 'bold', family: 'Outfit' },
                bodyFont: { size: 14, family: 'Inter' },
                displayColors: false,
                cornerRadius: 12
            }
        },
        scales: {
            y: {
                min: 0, max: 100,
                grid: { color: 'rgba(0,0,0,.03)', drawBorder: false },
                ticks: { 
                    font: { size: 12, weight: '600', family: 'Inter' },
                    color: '#64748b',
                    callback: (val) => val + '%'
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 12, weight: '600', family: 'Inter' }, color: '#64748b' }
            }
        }
    }
});
<?php endif; ?>
</script>

</body>
</html>
