<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];

// All assessments for chart + cards
$stmt = $conn->prepare(
    "SELECT score_id, overall_score, depression_score, anxiety_score, stress_score, risk_level, assessment_date
     FROM assessment_scores WHERE user_id = ? ORDER BY assessment_date DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result   = $stmt->get_result();
$all_rows = $result->fetch_all(MYSQLI_ASSOC);

// Chart data (chronological order)
$chart_data   = array_reverse($all_rows);
$chart_labels = array_map(fn($r) => date('M d', strtotime($r['assessment_date'])), $chart_data);
$chart_scores = array_map(fn($r) => (int)$r['overall_score'], $chart_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1000px; padding-top: 1.5rem; padding-bottom: 3rem;">
    <div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Clinical Records</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Assessment Reports</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Track your wellness progression over time.</p>
        </div>
        <a href="take_assessment.php" class="btn-primary" style="padding: 0.65rem 1.5rem; border-radius: var(--radius-sm); font-weight: 600; background: var(--primary); color: white; text-decoration: none; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); font-size: 0.85rem;">New Assessment →</a>
    </div>

    <?php if (count($chart_data) > 1): ?>
    <div class="card" style="padding: 2rem; border-radius: var(--radius); margin-bottom: 2rem;">
        <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; font-size: 1.15rem; color: var(--text);">
            📈 Progress Overview
        </h2>
        <div style="height: 300px;">
            <canvas id="historyChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($all_rows)): ?>

    <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; background: var(--surface-2); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
        <input type="text" id="searchInput" placeholder="Search by date or risk..." style="flex: 1; padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border); font-weight: 500; font-size: 0.9rem;" oninput="filterCards()">
        <select id="riskFilter" style="padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border); font-weight: 600; color: var(--text-muted); font-size: 0.85rem;" onchange="filterCards()">
            <option value="">All Risk Levels</option>
            <option value="Low">Low</option>
            <option value="Moderate">Moderate</option>
            <option value="High">High</option>
            <option value="Critical">Critical</option>
        </select>
    </div>

    <div id="reportsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.25rem;">
        <?php foreach ($all_rows as $record): ?>
        <div class="report-card" 
             data-risk="<?php echo $record['risk_level']; ?>" 
             data-date="<?php echo $record['assessment_date']; ?>"
             style="background: white; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.75rem; transition: var(--transition); position: relative; overflow: hidden;">
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
                <div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: 0.2rem;"><?php echo date('M d, Y', strtotime($record['assessment_date'])); ?></h3>
                    <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em;">Assessment Record</div>
                </div>
                <div style="padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; 
                    border: 1px solid transparent;
                    <?php echo $record['risk_level']; ?>
                </div>
            </div>

            <div style="margin-bottom: 1.75rem;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary); line-height: 1;"><?php echo $record['overall_score']; ?><span style="font-size: 0.85rem; color: var(--text-dim); font-weight: 400; margin-left: 0.35rem;">/ 100</span></div>
                <p style="font-weight: 600; color: var(--text-muted); margin-top: 0.25rem; font-size: 0.85rem;">Wellness Score</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; padding: 1rem; background: var(--surface-2); border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
                <div style="text-align: center;">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--text);"><?php echo $record['depression_score']; ?></div>
                    <div style="font-size: 0.6rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase;">Depr.</div>
                </div>
                <div style="text-align: center; border-left: 1px solid var(--border); border-right: 1px solid var(--border);">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--text);"><?php echo $record['anxiety_score']; ?></div>
                    <div style="font-size: 0.6rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase;">Anx.</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--text);"><?php echo $record['stress_score']; ?></div>
                    <div style="font-size: 0.6rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase;">Stress</div>
                </div>
            </div>

            <a href="view_report.php?score_id=<?php echo $record['score_id']; ?>" style="display: block; text-align: center; padding: 0.75rem; border-radius: var(--radius-sm); background: white; border: 1.5px solid var(--border); color: var(--primary); font-weight: 600; text-decoration: none; transition: var(--transition); font-size: 0.85rem;">View Details →</a>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div style="padding: 4rem 2rem; background: white; border-radius: var(--radius); text-align: center; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        <div style="font-size: 3rem; margin-bottom: 1.5rem;">🗂️</div>
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">No Reports Yet</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem; max-width: 400px; margin-left: auto; margin-right: auto;">You haven't completed any assessments. Take your first step toward wellness.</p>
        <a href="take_assessment.php" class="btn-primary" style="padding: 0.85rem 2rem; border-radius: var(--radius-sm); font-weight: 600; background: var(--primary); color: white; text-decoration: none; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">Start Assessment →</a>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

<script>
// Filter cards
function filterCards() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const risk   = document.getElementById('riskFilter').value;
    document.querySelectorAll('.report-card').forEach(card => {
        const matchRisk   = !risk || card.dataset.risk === risk;
        const matchSearch = !search ||
            card.dataset.date.toLowerCase().includes(search) ||
            card.dataset.risk.toLowerCase().includes(search) ||
            card.innerText.toLowerCase().includes(search);
        card.style.display = matchRisk && matchSearch ? '' : 'none';
    });
}

<?php if (count($chart_data) > 1): ?>
new Chart(document.getElementById('historyChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Wellness Score',
            data: <?php echo json_encode($chart_scores); ?>,
            borderColor: '#0d9488',
            borderWidth: 3,
            backgroundColor: (context) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(13, 148, 136, 0.15)');
                gradient.addColorStop(1, 'rgba(13, 148, 136, 0)');
                return gradient;
            },
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#0d9488',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 9,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { 
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                padding: 12,
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 },
                displayColors: false
            }
        },
        scales: {
            y: { 
                min: 0, max: 100, 
                grid: { color: 'rgba(0,0,0,0.03)', drawBorder: false },
                ticks: { font: { weight: '600' }, color: '#64748b' }
            },
            x: { 
                grid: { display: false },
                ticks: { font: { weight: '600' }, color: '#64748b' }
            }
        }
    }
});
<?php endif; ?>
</script>
</main>
</body>
</html>
