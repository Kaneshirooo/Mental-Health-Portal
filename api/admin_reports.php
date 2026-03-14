<?php
require_once 'config.php';
requireAdmin();

// Handle CSV export
if (isset($_GET['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="assessments_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Student Name','Email','Roll Number','Department','Overall Score','Depression','Anxiety','Stress','Risk Level','Date']);

    $rows = $conn->query(
        "SELECT u.full_name, u.email, u.roll_number, u.department,
                a.overall_score, a.depression_score, a.anxiety_score, a.stress_score, a.risk_level, a.assessment_date
         FROM assessment_scores a JOIN users u ON a.user_id = u.user_id
         ORDER BY a.assessment_date DESC"
    );
    while ($r = $rows->fetch_assoc()) fputcsv($out, $r);
    fclose($out);
    exit;
}

// Stats
$total_assessments = $conn->query("SELECT COUNT(*) AS cnt FROM assessment_scores")->fetch_assoc()['cnt'];
$total_students    = $conn->query("SELECT COUNT(DISTINCT user_id) AS cnt FROM assessment_scores")->fetch_assoc()['cnt'];

$risk_counts_result = $conn->query(
    "SELECT risk_level, COUNT(*) AS cnt FROM assessment_scores GROUP BY risk_level"
);
$risk_counts = [];
while ($r = $risk_counts_result->fetch_assoc()) $risk_counts[$r['risk_level']] = $r['cnt'];

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
    <?php require_once 'pwa_head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1400px; padding-top: 5rem; padding-bottom: 8rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Diagnostic Intelligence Hub</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.75rem;">Institutional Analytics</h1>
            <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">Mapping system-wide health based on <?php echo number_format($total_assessments); ?> clinical interactions.</p>
        </div>
        <a href="?export_csv=1" style="padding: 1.25rem 2.5rem; border-radius: 50px; background: var(--primary); color: white; text-decoration: none; font-weight: 800; font-size: 0.85rem; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2); transition: var(--transition);">DOWNLOAD SYSTEM ARCHIVE ↓</a>
    </div>

    <!-- Analytics Matrix -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 5rem;">
        <div style="background: white; border-radius: 32px; padding: 2.5rem; border: 1px solid var(--border);">
            <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem;">Intake Volume</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);"><?php echo number_format($total_assessments); ?></div>
            <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600; margin-top: 0.5rem;"><?php echo number_format($total_students); ?> Unique Identifiers</div>
        </div>
        <div style="background: #f0fdf4; border-radius: 32px; padding: 2.5rem; border: 1px solid #dcfce7;">
            <div style="font-size: 0.75rem; font-weight: 800; color: #16a34a; text-transform: uppercase; margin-bottom: 1.5rem;">Standard Resilience</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #16a34a;"><?php echo $risk_counts['Low'] ?? 0; ?></div>
            <div style="font-size: 0.8rem; color: #16a34a; opacity: 0.7; font-weight: 600; margin-top: 0.5rem;">Low Risk Profile</div>
        </div>
        <div style="background: #fffbeb; border-radius: 32px; padding: 2.5rem; border: 1px solid #fef3c7;">
            <div style="font-size: 0.75rem; font-weight: 800; color: #d97706; text-transform: uppercase; margin-bottom: 1.5rem;">Intermediate Concern</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #d97706;"><?php echo ($risk_counts['Moderate'] ?? 0) + ($risk_counts['High'] ?? 0); ?></div>
            <div style="font-size: 0.8rem; color: #d97706; opacity: 0.7; font-weight: 600; margin-top: 0.5rem;">Moderate / High Priority</div>
        </div>
        <div style="background: #fef2f2; border-radius: 32px; padding: 2.5rem; border: 1px solid #fee2e2;">
            <div style="font-size: 0.75rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-bottom: 1.5rem;">Clinical Alarm</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #dc2626;"><?php echo $risk_counts['Critical'] ?? 0; ?></div>
            <div style="font-size: 0.8rem; color: #dc2626; opacity: 0.7; font-weight: 600; margin-top: 0.5rem;">Critical Intervention Required</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2.5rem; margin-bottom: 3.5rem;">
        <!-- Volume chart -->
        <div class="card" style="padding: 2.5rem; border-radius: 32px; border: none;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 2rem;">Assessment Trajectory</h2>
            <div style="height: 350px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Risk Distribution -->
        <div class="card" style="padding: 2.5rem; border-radius: 32px; border: none;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 2rem;">Risk Propensity Scale</h2>
            <div class="risk-bar-chart">
                <?php
                $colors = ['Low'=>'#10b981','Moderate'=>'#f59e0b','High'=>'#f97316','Critical'=>'#ef4444'];
                $total  = max(1, $total_assessments);
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
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 20px;">
                <h4 style="font-weight: 800; color: var(--text); margin-bottom: 0.5rem; font-size: 0.9rem;">Diagnostic Summary</h4>
                <p style="font-size: 0.85rem; color: var(--text-dim); line-height: 1.6;">Highest volume observed in the <strong style="color: var(--primary);"><?php echo array_search(max($risk_counts ?: [0]), $risk_counts) ?: 'N/A'; ?></strong> category. Recommending focused intervention for high-priority sectors.</p>
            </div>
        </div>
    </div>

    <!-- Latest assessments table -->
    <div class="card" style="padding: 0; border-radius: 32px; overflow: hidden; border: 1px solid var(--border);">
        <div style="padding: 2.5rem; border-bottom: 1px solid var(--border); background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin: 0;">Clinical Feed</h2>
            <span style="font-weight: 700; color: var(--text-dim); font-size: 0.85rem;">Last 20 records</span>
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
            <table class="table" style="margin: 0;">
                <thead style="background: rgba(0,0,0,0.02);">
                    <tr><th>Patient Record</th><th>Clinical ID</th><th>Response Score</th><th>Risk Priority</th><th>Timestamp</th></tr>
                </thead>
                <tbody>
                <?php while ($r = $recent->fetch_assoc()): 
                    $initials = strtoupper(substr($r['full_name'], 0, 1) . substr(explode(' ', $r['full_name'])[1] ?? '', 0, 1));
                ?>
                <tr style="transition: var(--transition);">
                    <td style="padding: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem;">
                                <?php echo $initials; ?>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 800; color: var(--text);"><?php echo htmlspecialchars($r['full_name']); ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-dim);"><?php echo htmlspecialchars($r['email']); ?></span>
                            </div>
                        </div>
                    </td>
                    <td><span style="font-weight: 700; background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.8rem;"><?php echo htmlspecialchars($r['roll_number']); ?></span></td>
                    <td style="font-weight: 800; color: var(--primary);"><?php echo $r['overall_score']; ?>%</td>
                    <td>
                        <span class="risk-badge risk-<?php echo strtolower($r['risk_level']); ?>" style="border-radius: 10px; font-size: 0.7rem; padding: 0.5rem 1rem;">
                            <?php echo strtoupper($r['risk_level']); ?>
                        </span>
                    </td>
                    <td style="font-weight: 600; color: var(--text-dim); font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($r['assessment_date'])); ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
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
            backgroundColor: 'rgba(79,70,229,.7)',
            borderRadius: 6
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
