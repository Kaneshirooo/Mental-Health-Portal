<?php
require_once 'config.php';
requireAdmin();

// Handle CSV export
if (isset($_GET['export_csv'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die("CSRF validation failed for export action.");
    }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="assessments_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Student Name', 'Email', 'Roll Number', 'Department', 'Overall Score', 'Depression', 'Anxiety', 'Stress', 'Risk Level', 'Date']);

    $rows = $conn->query(
        "SELECT u.full_name, u.email, u.roll_number, u.department,
                a.overall_score, a.depression_score, a.anxiety_score, a.stress_score, a.risk_level, a.assessment_date
         FROM assessment_scores a JOIN users u ON a.user_id = u.user_id
         ORDER BY a.assessment_date DESC"
    );
    while ($r = $rows->fetch_assoc())
        fputcsv($out, $r);
    fclose($out);
    exit;
}

// Stats
$total_assessments = $conn->query("SELECT COUNT(*) AS cnt FROM assessment_scores")->fetch_assoc()['cnt'];
$total_students = $conn->query("SELECT COUNT(DISTINCT user_id) AS cnt FROM assessment_scores")->fetch_assoc()['cnt'];

$risk_counts_result = $conn->query(
    "SELECT risk_level, COUNT(*) AS cnt FROM assessment_scores GROUP BY risk_level"
);
$risk_counts = [];
while ($r = $risk_counts_result->fetch_assoc())
    $risk_counts[$r['risk_level']] = $r['cnt'];

// Monthly data (last 6 months)
$monthly_result = $conn->query(
    "SELECT DATE_FORMAT(assessment_date,'%Y-%m') AS month, COUNT(*) AS cnt
     FROM assessment_scores
     WHERE assessment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY month ORDER BY month"
);
$monthly_labels = [];
$monthly_counts = [];
while ($r = $monthly_result->fetch_assoc()) {
    $monthly_labels[] = date('M Y', strtotime($r['month'] . '-01'));
    $monthly_counts[] = (int)$r['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Diagnostic Intelligence Hub</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Institutional Analytics</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">System-wide wellness metrics and clinical trends.</p>
        </div>
        <a href="?export_csv=1&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn-primary" style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); background: var(--primary); color: white; text-decoration: none; font-weight: 600; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition);">Systems Export ↓</a>
    </div>

    <!-- Analytics Matrix -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: var(--radius); padding: 1.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.04em;">Intake Volume</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary);"><?php echo number_format($total_assessments); ?></div>
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 400; margin-top: 0.25rem;"><?php echo number_format($total_students); ?> students</div>
        </div>
        <div style="background: #f0fdf4; border-radius: var(--radius); padding: 1.5rem; border: 1px solid rgba(22, 163, 74, 0.1);">
            <div style="font-size: 0.65rem; font-weight: 700; color: #16a34a; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.04em;">Low Risk</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #16a34a;"><?php echo $risk_counts['Low'] ?? 0; ?></div>
            <div style="font-size: 0.75rem; color: #16a34a; opacity: 0.8; font-weight: 400; margin-top: 0.25rem;">Standard Baseline</div>
        </div>
        <div style="background: #fffbeb; border-radius: var(--radius); padding: 1.5rem; border: 1px solid rgba(217, 119, 6, 0.1);">
            <div style="font-size: 0.65rem; font-weight: 700; color: #d97706; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.04em;">Mid Priority</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #d97706;"><?php echo($risk_counts['Moderate'] ?? 0) + ($risk_counts['High'] ?? 0); ?></div>
            <div style="font-size: 0.75rem; color: #d97706; opacity: 0.8; font-weight: 400; margin-top: 0.25rem;">Focused Observation</div>
        </div>
        <div style="background: #fff1f2; border-radius: var(--radius); padding: 1.5rem; border: 1px solid rgba(225, 29, 72, 0.1);">
            <div style="font-size: 0.65rem; font-weight: 700; color: #e11d48; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.04em;">Critical Intervention</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #e11d48;"><?php echo $risk_counts['Critical'] ?? 0; ?></div>
            <div style="font-size: 0.75rem; color: #e11d48; opacity: 0.8; font-weight: 400; margin-top: 0.25rem;">Urgent Clinical Action</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Volume chart -->
        <div class="card" style="padding: 1.5rem; border-radius: var(--radius);">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin-bottom: 1.5rem;">Assessment Patterns</h2>
            <div style="height: 300px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Risk Distribution -->
        <div class="card" style="padding: 2.5rem; border-radius: 32px; border: none;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 2rem;">Risk Propensity Scale</h2>
            <div class="risk-bar-chart">
                <?php
$colors = ['Low' => '#10b981', 'Moderate' => '#f59e0b', 'High' => '#f97316', 'Critical' => '#ef4444'];
$total = max(1, $total_assessments);
foreach ($colors as $lvl => $color):
    $cnt = $risk_counts[$lvl] ?? 0;
    $pct = round($cnt / $total * 100);
?>
                <div class="risk-bar-row" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; color: var(--text-dim);">
                        <span><?php echo $lvl; ?></span>
                        <span style="color: <?php echo $color; ?>;"><?php echo $pct; ?>% (<?php echo $cnt; ?>)</span>
                    </div>
                    <div style="height: 12px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                        <div class="risk-bar-inner" data-width="<?php echo $pct; ?>"
                             style="background:<?php echo $color; ?>;width:0%;height:100%;transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);">
                        </div>
                    </div>
                </div>
                <?php
endforeach; ?>
            </div>
            
            <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 20px;">
                <h4 style="font-weight: 800; color: var(--text); margin-bottom: 0.5rem; font-size: 0.9rem;">Diagnostic Summary</h4>
                <p style="font-size: 0.85rem; color: var(--text-dim); line-height: 1.6;">Highest volume observed in the <strong style="color: var(--primary);"><?php echo array_search(max($risk_counts ?: [0]), $risk_counts) ?: 'N/A'; ?></strong> category. Recommending focused intervention for high-priority sectors.</p>
            </div>
        </div>
    </div>

    <!-- Latest assessments table -->
    <div class="card" style="padding: 0; border-radius: var(--radius); overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); background: var(--surface-2); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin: 0;">Clinical Feed</h2>
            <span style="font-weight: 600; color: var(--text-muted); font-size: 0.75rem;">Last 20 records</span>
        </div>
        <?php
$recent = $conn->query(
    "SELECT u.full_name, u.email, u.roll_number,
                    a.overall_score, a.risk_level, a.assessment_date
             FROM assessment_scores a JOIN users u ON a.user_id = u.user_id
             ORDER BY a.assessment_date DESC LIMIT 20"
);
?>
        <div class="table-wrapper">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                    <tr>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; text-align: left;">Identity</th>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; text-align: left;">ID</th>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; text-align: left;">Score</th>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; text-align: left;">Priority</th>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($r = $recent->fetch_assoc()):
    $nameParts = explode(' ', $r['full_name']);
    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
?>
                <tr style="border-bottom: 1px solid var(--border); transition: var(--transition);">
                    <td style="padding: 1rem 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface-2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; border: 1px solid var(--border);">
                                <?php echo $initials; ?>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 600; color: var(--text); font-size: 0.88rem;"><?php echo htmlspecialchars($r['full_name']); ?></span>
                                <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400;"><?php echo htmlspecialchars($r['email']); ?></span>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1rem 1.5rem;"><span style="font-weight: 600; background: var(--surface-2); padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.72rem; color: var(--text-muted); border: 1px solid var(--border);"><?php echo htmlspecialchars($r['roll_number']); ?></span></td>
                    <td style="padding: 1rem 1.5rem; font-weight: 700; color: var(--primary); font-size: 0.9rem;"><?php echo $r['overall_score']; ?>%</td>
                    <td style="padding: 1rem 1.5rem;">
                         <span style="font-size: 0.62rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 4px; border: 1px solid transparent; background: #f8fafc; color: var(--text-muted);">
                            <?php echo strtoupper($r['risk_level']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-weight: 500; color: var(--text-muted); font-size: 0.8rem; text-align: right;"><?php echo date('M d, Y', strtotime($r['assessment_date'])); ?></td>
                </tr>
                <?php
endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

<script>
window.addEventListener('load', () => {
    document.querySelectorAll('.risk-bar-inner[data-width]')
        .forEach(b => setTimeout(() => b.style.width = b.dataset.width + '%', 400));
});

new Chart(document.getElementById('monthlyChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($monthly_labels); ?>,
        datasets: [{
            label: 'Assessments',
            data: <?php echo json_encode($monthly_counts); ?>,
            backgroundColor: '#0d9488',
            borderRadius: 4,
            barThickness: 20
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color:'rgba(0,0,0,.05)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
</main>
</body>
</html>
