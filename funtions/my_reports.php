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
    <?php require_once 'pwa_head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 3rem; padding-bottom: 6rem;">
    <div style="margin-bottom: 4rem; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.5rem;">Wellness Analytics</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 600;">Visualize your clinical progression and emotional patterns.</p>
        </div>
        <a href="take_assessment.php" class="btn-primary" style="padding: 1rem 2rem; border-radius: 50px; font-weight: 800; background: var(--primary); color: white; text-decoration: none; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.15);">NEW ASSESSMENT →</a>
    </div>

    <?php if (count($chart_data) > 1): ?>
    <div class="card" style="padding: 3rem; border-radius: 32px; margin-bottom: 3rem;">
        <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.5rem;">📈</span> Progression Metric
        </h2>
        <div style="height: 350px;">
            <canvas id="historyChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($all_rows)): ?>

    <div style="display: flex; gap: 1rem; margin-bottom: 3rem; background: #f8fafc; padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
        <input type="text" id="searchInput" placeholder="Search assessments..." style="flex: 1; padding: 1rem 1.5rem; border-radius: 14px; border: 1.5px solid var(--border); font-weight: 600;" oninput="filterCards()">
        <select id="riskFilter" style="padding: 1rem 1.5rem; border-radius: 14px; border: 1.5px solid var(--border); font-weight: 800; color: var(--text-dim);" onchange="filterCards()">
            <option value="">ALL JOURNEYS</option>
            <option value="Low">STABLE</option>
            <option value="Moderate">MODERATE</option>
            <option value="High">CONCERNING</option>
            <option value="Critical">CRITICAL</option>
        </select>
    </div>

    <div id="reportsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem;">
        <?php foreach ($all_rows as $record): ?>
        <div class="report-card" 
             data-risk="<?php echo $record['risk_level']; ?>" 
             data-date="<?php echo $record['assessment_date']; ?>"
             style="background: white; border: 1px solid var(--border); border-radius: 28px; padding: 2.5rem; transition: var(--transition); position: relative; overflow: hidden;">
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
                <div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--text); margin-bottom: 0.25rem;"><?php echo date('M d, Y', strtotime($record['assessment_date'])); ?></h3>
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Clinical Snapshot</div>
                </div>
                <div style="padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; 
                    <?php 
                        $rl = strtolower($record['risk_level']);
                        if($rl == 'low') echo 'background: #ecfdf5; color: #059669;';
                        elseif($rl == 'moderate') echo 'background: #fffbeb; color: #d97706;';
                        elseif($rl == 'high') echo 'background: #fff1f2; color: #e11d48;';
                        else echo 'background: #450a0a; color: white;';
                    ?>">
                    <?php echo $record['risk_level']; ?>
                </div>
            </div>

            <div style="margin-bottom: 2.5rem;">
                <div style="font-size: 3rem; font-weight: 800; color: var(--primary-dark); line-height: 1;"><?php echo $record['overall_score']; ?><span style="font-size: 1rem; color: var(--text-dim); margin-left: 0.5rem;">/ 100</span></div>
                <p style="font-weight: 700; color: var(--text-dim); margin-top: 0.5rem;">Overall Wellbeing Index</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; padding: 1.5rem; background: #f8fafc; border-radius: 20px; margin-bottom: 2rem;">
                <div style="text-align: center;">
                    <div style="font-weight: 800; font-size: 1.1rem; color: var(--text);"><?php echo $record['depression_score']; ?></div>
                    <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Depression</div>
                </div>
                <div style="text-align: center; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                    <div style="font-weight: 800; font-size: 1.1rem; color: var(--text);"><?php echo $record['anxiety_score']; ?></div>
                    <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Anxiety</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-weight: 800; font-size: 1.1rem; color: var(--text);"><?php echo $record['stress_score']; ?></div>
                    <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Stress</div>
                </div>
            </div>

            <a href="view_report.php?score_id=<?php echo $record['score_id']; ?>" style="display: block; text-align: center; padding: 1rem; border-radius: 16px; background: white; border: 1.5px solid var(--border); color: var(--primary); font-weight: 800; text-decoration: none; transition: var(--transition);">EXPLORE DATA →</a>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div style="padding: 6rem 2rem; background: white; border-radius: 40px; text-align: center; border: 1px solid var(--border);">
        <div style="font-size: 5rem; margin-bottom: 2rem;">🗂️</div>
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; margin-bottom: 1rem;">Begin Your Journey</h2>
        <p style="color: var(--text-dim); font-size: 1.1rem; margin-bottom: 3rem; max-width: 500px; margin-left: auto; margin-right: auto;">You haven't completed any clinical assessments yet. Your journey to clarity starts here.</p>
        <a href="take_assessment.php" class="btn-primary" style="padding: 1.25rem 3rem; border-radius: 50px; font-weight: 800; background: var(--primary); color: white; text-decoration: none;">START FIRST ASSESSMENT</a>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
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
            label: 'Wellbeing Index',
            data: <?php echo json_encode($chart_scores); ?>,
            borderColor: '#4f46e5',
            borderWidth: 4,
            backgroundColor: (context) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
                gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
                return gradient;
            },
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#4f46e5',
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
