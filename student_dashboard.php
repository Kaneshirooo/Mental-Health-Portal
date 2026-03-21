<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);

// Assessment history (last 10) for chart
$history_query = "SELECT score_id, overall_score, risk_level, assessment_date
                  FROM assessment_scores
                  WHERE user_id = ? ORDER BY assessment_date ASC LIMIT 10";
$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$history_rows = $history_result->fetch_all(MYSQLI_ASSOC);

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

// Mood logs for the secondary chart (last 14)
$mood_query = "SELECT mood_score, logged_at FROM mood_logs WHERE student_id = ? ORDER BY logged_at ASC LIMIT 14";
$mood_stmt = $conn->prepare($mood_query);
$mood_stmt->bind_param("i", $user_id);
$mood_stmt->execute();
$mood_result = $mood_stmt->get_result();
$mood_rows = $mood_result->fetch_all(MYSQLI_ASSOC);

$mood_labels = [];
$mood_data = [];
foreach ($mood_rows as $m) {
    $mood_labels[] = date('M d', strtotime($m['logged_at']));
    $mood_data[] = (int)$m['mood_score'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Mental Health Portal</title>
    <meta name="description" content="Your student wellness dashboard. Access resources, track patterns, and connect with support.">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <style>
        .wellness-banner {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, #0ea5e9 100%);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 8px 24px rgba(13, 148, 136, 0.15);
            position: relative;
            overflow: hidden;
        }
        .wellness-banner::after {
            content: '🧘';
            position: absolute;
            right: -10px;
            bottom: -15px;
            font-size: 10rem;
            opacity: 0.06;
            transform: rotate(-15deg);
        }
        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .quick-action {
            background: white;
            border-radius: var(--radius);
            padding: 1.75rem 1.25rem;
            border: 1px solid var(--border);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }
        .quick-action:hover { 
            border-color: var(--primary-light);
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }
        .quick-action-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            transition: var(--transition);
        }
        .quick-action:hover .quick-action-icon { transform: scale(1.08); }

        @media (max-width: 1024px) {
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.25rem;">Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?></h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Here's an overview of your wellness journey.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <?php if ($notif_count > 0): ?>
            <a href="anonymous_notes.php" style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); background: var(--primary); color: white; font-weight: 600; font-size: 0.82rem; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">
                🔔 <?php echo $notif_count; ?> New
            </a>
            <?php
endif; ?>
            <div style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); background: #ecfdf5; color: #059669; font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(5, 150, 105, 0.08);">
                <span style="width: 7px; height: 7px; background: #10b981; border-radius: 50%;"></span> Connected
            </div>
        </div>
    </div>

    <!-- Daily Tip Banner -->
    <div class="wellness-banner">
        <div style="max-width: 700px; position: relative; z-index: 1;">
            <div style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 1rem; opacity: 0.7;">Daily Wellness Tip</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 600; line-height: 1.4; margin-bottom: 1.5rem;"><?php echo $tip; ?></h2>
            <div style="display: flex; gap: 0.75rem;">
                <button onclick="location.href='mindfulness.php'" style="padding: 0.65rem 1.5rem; border-radius: var(--radius-sm); background: white; color: var(--primary-dark); border: none; font-weight: 600; cursor: pointer; font-size: 0.85rem; transition: var(--transition);">Practice Now</button>
                <button onclick="location.href='mental_health_resources.php'" style="padding: 0.65rem 1.5rem; border-radius: var(--radius-sm); background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25); font-weight: 600; cursor: pointer; font-size: 0.85rem; backdrop-filter: blur(8px);">Resources</button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div class="stat-card">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Student ID</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: 0.25rem;"><?php echo htmlspecialchars($user['roll_number']); ?></div>
            <div style="font-size: 0.8rem; color: var(--primary); font-weight: 600;"><?php echo htmlspecialchars($user['department']); ?></div>
        </div>
        <div class="stat-card">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Assessments Taken</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary); line-height: 1;"><?php echo count($history_rows); ?></div>
            <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 500; margin-top: 0.25rem;">Total completed</div>
        </div>
        <?php if ($latest_score): ?>
        <div class="stat-card">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Latest Score</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1;"><?php echo $latest_score['overall_score']; ?><span style="font-size: 1rem; opacity: 0.3; margin-left: 2px;">%</span></div>
            <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 500; margin-top: 0.25rem;">Current score</div>
        </div>
        <div class="stat-card" style="background: <?php echo($latest_score['risk_level'] === 'Critical' || $latest_score['risk_level'] === 'High') ? '#fef2f2' : '#f0fdf4'; ?>; border-color: <?php echo($latest_score['risk_level'] === 'Critical' || $latest_score['risk_level'] === 'High') ? '#fee2e2' : '#dcfce7'; ?>;">
            <div style="font-size: 0.72rem; font-weight: 600; color: <?php echo($latest_score['risk_level'] === 'Critical' || $latest_score['risk_level'] === 'High') ? '#dc2626' : '#16a34a'; ?>; text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Risk Level</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: <?php echo($latest_score['risk_level'] === 'Critical' || $latest_score['risk_level'] === 'High') ? '#dc2626' : '#16a34a'; ?>;"><?php echo $latest_score['risk_level']; ?></div>
        </div>
        <?php
endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="take_assessment.php" class="quick-action">
            <div class="quick-action-icon" style="background: #f0fdfa; color: var(--primary);">📋</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">Pre-Assessment</h3>
            <p style="color: var(--text-dim); font-size: 0.82rem; line-height: 1.5; font-weight: 400;">Take your wellness assessment to track your progress.</p>
        </a>
        <a href="student_ai_chat.php" class="quick-action">
            <div class="quick-action-icon" style="background: #ecfdf5; color: #10b981;">✨</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">Talk to Aria</h3>
            <p style="color: var(--text-dim); font-size: 0.82rem; line-height: 1.5; font-weight: 400;">Chat with your AI companion for support and reflection.</p>
        </a>
        <a href="mood_journal.php" class="quick-action">
            <div class="quick-action-icon" style="background: #fffbeb; color: #f59e0b;">📔</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">Mood Journal</h3>
            <p style="color: var(--text-dim); font-size: 0.82rem; line-height: 1.5; font-weight: 400;">Log how you're feeling and track your emotional patterns.</p>
        </a>
        <a href="student_appointments.php" class="quick-action">
            <div class="quick-action-icon" style="background: #eff6ff; color: #3b82f6;">📅</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">Appointments</h3>
            <p style="color: var(--text-dim); font-size: 0.82rem; line-height: 1.5; font-weight: 400;">Book a session with a counselor on your own terms.</p>
        </a>
    </div>

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Assessment Chart -->
        <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); height: 400px; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin: 0;">Wellness Score Trend</h2>
                <a href="my_reports.php" style="font-weight: 600; color: var(--primary); text-decoration: none; font-size: 0.82rem;">Details →</a>
            </div>
            <div style="flex: 1; position: relative;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Mood Chart -->
        <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm); height: 400px; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin: 0;">Emotional Mood Trend</h2>
                <a href="mood_journal.php" style="font-weight: 600; color: #f59e0b; text-decoration: none; font-size: 0.82rem;">Journal →</a>
            </div>
            <div style="flex: 1; position: relative;">
                <canvas id="moodChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div style="margin-bottom: 2rem;">

        <div style="background: var(--surface-2); border-radius: var(--radius); border: 1px solid var(--border); padding: 2rem;">
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem; margin-bottom: 1.5rem;">Score Legend</h3>
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #10b981;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.82rem; color: var(--text);">Low Risk (0-30%)</div>
                        <div style="font-size: 0.75rem; color: var(--text-dim);">Maintain current wellness habits.</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.82rem; color: var(--text);">Moderate (31-60%)</div>
                        <div style="font-size: 0.75rem; color: var(--text-dim);">Consider journaling or chatting with Aria.</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.82rem; color: var(--text);">High / Critical (60%+)</div>
                        <div style="font-size: 0.75rem; color: var(--text-dim);">We recommend booking a counselor session.</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--border);">
                <div style="font-size: 0.78rem; color: var(--text-dim); line-height: 1.6;">
                    All data is encrypted and only accessible to assigned counselors.
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

</main>

<script>
// Shared configuration for charts
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#8b95a5';

<?php if (count($history_rows) > 1): ?>
(function() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(13, 148, 136, 0.2)');
    gradient.addColorStop(1, 'rgba(13, 148, 136, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Score %',
                data: <?php echo json_encode($chart_scores); ?>,
                borderColor: '#0d9488',
                borderWidth: 3,
                backgroundColor: gradient,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d9488',
                pointBorderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
                x: { grid: { display: false } }
            }
        }
    });
})();
<?php endif; ?>

<?php if (count($mood_rows) > 1): ?>
(function() {
    const ctx = document.getElementById('moodChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(245, 158, 11, 0.2)');
    gradient.addColorStop(1, 'rgba(245, 158, 11, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($mood_labels); ?>,
            datasets: [{
                label: 'Mood Level',
                data: <?php echo json_encode($mood_data); ?>,
                borderColor: '#f59e0b',
                borderWidth: 3,
                backgroundColor: gradient,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#f59e0b',
                pointBorderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const labels = ['', 'Very Low', 'Low', 'Neutral', 'Good', 'Very Good'];
                            return ' Mood: ' + (labels[ctx.raw] || ctx.raw);
                        }
                    }
                }
            },
            scales: {
                y: { 
                    min: 1, max: 5, 
                    ticks: { 
                        stepSize: 1,
                        callback: v => ['', '😢', '🙁', '😐', '🙂', '😊'][v] || v 
                    } 
                },
                x: { grid: { display: false } }
            }
        }
    });
})();
<?php endif; ?>
</script>

</body>
</html>
