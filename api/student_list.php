<?php
require_once 'config.php';
requireCounselor();

$search = '';
$filter = '';

if (isset($_GET['search'])) $search = sanitize($_GET['search']);
if (isset($_GET['filter']))  $filter = sanitize($_GET['filter']);

// Build query
$query = "SELECT u.user_id, u.full_name, u.email, u.roll_number, u.department, u.semester,
          a.overall_score, a.risk_level, a.assessment_date
          FROM users u
          LEFT JOIN assessment_scores a ON u.user_id = a.user_id
          WHERE u.user_type = 'student'";

$params = [];
$types  = '';

if (!empty($search)) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.roll_number LIKE ?)";
    $term   = "%$search%";
    $params = [$term, $term, $term];
    $types  = 'sss';
}
if (!empty($filter)) {
    $query  .= " AND a.risk_level = ?";
    $params[] = $filter;
    $types  .= 's';
}
$query .= " GROUP BY u.user_id ORDER BY u.full_name";

$stmt = $conn->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$rows   = $result->fetch_all(MYSQLI_ASSOC);

// Build CSV data
$csv_ready = $rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1400px; padding-top: 5rem; padding-bottom: 8rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5rem;">
        <div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.75rem;">Clinical Registry</h1>
            <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">Centralized repository of student wellness trajectories and assessment data.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <div style="text-align: right; padding: 0 2rem; border-right: 2px solid var(--border);">
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary);"><?php echo count($rows); ?></div>
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Total Captive Records</div>
            </div>
            <button onclick="exportCSV()" style="padding: 1rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); background: white; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; transition: var(--transition);">
                <span>📊</span> EXPORT DATA
            </button>
        </div>
    </div>

    <!-- Interface Controls -->
    <div style="background: white; border-radius: 32px; padding: 2.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 4rem; display: flex; gap: 1.5rem; align-items: center;">
        <div style="flex: 1; position: relative;">
            <input type="text" id="liveSearch" oninput="liveFilter()" placeholder="Identify student by name, ID, or clinical profile..." style="width: 100%; padding: 1.25rem 1.5rem 1.25rem 3.5rem; border-radius: 50px; border: 1.5px solid var(--border); font-size: 1rem; font-weight: 600; background: #f8fafc;">
            <span style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); opacity: 0.4;">🔍</span>
        </div>
        
        <form method="GET" id="filterForm" style="display: flex; gap: 1rem;">
            <select name="filter" onchange="this.form.submit()" style="padding: 1.25rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); background: white; font-weight: 800; color: var(--text); cursor: pointer;">
                <option value="">PRIORITY: ALL LEVELS</option>
                <?php foreach (['Low','Moderate','High','Critical'] as $lvl): ?>
                    <option value="<?php echo $lvl; ?>" <?php echo ($filter === $lvl) ? 'selected' : ''; ?>>PRIORITY: <?php echo strtoupper($lvl); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($search || $filter): ?>
                <a href="student_list.php" style="padding: 1.25rem 2rem; border-radius: 50px; background: #fee2e2; color: #b91c1c; font-weight: 800; text-decoration: none; display: flex; align-items: center;">RESET SYSTEM</a>
            <?php endif; ?>
        </form>
    </div>

    <div style="background: white; border-radius: 40px; border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Student Identity</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">ID / Credential</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Academic Unit</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Clinical Index</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Priority Status</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php foreach ($rows as $student): 
                    $initials = strtoupper(substr($student['full_name'], 0, 1) . substr(explode(' ', $student['full_name'])[1] ?? '', 0, 1));
                    $row_status = strtolower($student['risk_level'] ?? 'unassessed');
                ?>
                <tr class="student-row" style="border-bottom: 1px solid #f8fafc; transition: var(--transition);"
                    data-name="<?php echo strtolower($student['full_name']); ?>"
                    data-email="<?php echo strtolower($student['email']); ?>"
                    data-roll="<?php echo strtolower($student['roll_number']); ?>">
                    <td style="padding: 1.5rem 2rem;">
                        <div style="display: flex; align-items: center; gap: 1.25rem;">
                            <div style="width: 48px; height: 48px; border-radius: 14px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem; border: 1px solid var(--border);">
                                <?php echo $initials; ?>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--text); font-size: 1.05rem; margin-bottom: 0.15rem;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600;"><?php echo htmlspecialchars($student['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1.5rem 2rem;">
                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--text-dim); background: #f8fafc; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border);">
                            # <?php echo htmlspecialchars($student['roll_number']); ?>
                        </span>
                    </td>
                    <td style="padding: 1.5rem 2rem; font-weight: 700; color: var(--text-dim); font-size: 0.95rem;">
                        <?php echo htmlspecialchars($student['department']); ?>
                    </td>
                    <td style="padding: 1.5rem 2rem;">
                        <?php if ($student['overall_score']): ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="flex: 1; min-width: 60px; height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                                    <div style="width: <?php echo $student['overall_score']; ?>%; height: 100%; background: var(--primary);"></div>
                                </div>
                                <span style="font-weight: 800; color: var(--primary); font-size: 0.9rem;"><?php echo $student['overall_score']; ?>%</span>
                            </div>
                        <?php else: ?>
                            <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem;">PENDING</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1.5rem 2rem;">
                        <?php if ($student['risk_level']): ?>
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 10px; font-weight: 800; font-size: 0.75rem; letter-spacing: 0.05em;
                                <?php 
                                    $colors = [
                                        'Low' => ['bg' => '#f0fdf4', 'text' => '#16a34a'],
                                        'Moderate' => ['bg' => '#fefce8', 'text' => '#ca8a04'],
                                        'High' => ['bg' => '#fff7ed', 'text' => '#ea580c'],
                                        'Critical' => ['bg' => '#fef2f2', 'text' => '#dc2626']
                                    ];
                                    $c = $colors[$student['risk_level']];
                                    echo "background: {$c['bg']}; color: {$c['text']};";
                                ?>">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: currentColor; <?php if($student['risk_level']==='Critical') echo 'animation: pulse 1s infinite;'; ?>"></div>
                                <?php echo strtoupper($student['risk_level']); ?>
                            </div>
                        <?php else: ?>
                            <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim); opacity: 0.5;">NO DATA</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1.5rem 2rem; text-align: right;">
                        <a href="view_assessment.php?user_id=<?php echo $student['user_id']; ?>" style="text-decoration: none; font-weight: 800; font-size: 0.85rem; color: var(--primary); padding: 0.75rem 1.25rem; border-radius: 10px; background: #f5f3ff; transition: var(--transition);">CASE FILE →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php if (empty($rows)): ?>
    <div class="empty-state">
        <span class="empty-icon">🔍</span>
        <h2>No Students Found</h2>
        <p>Try adjusting your search or filter criteria.</p>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>

<script>
// Live JS search (without page reload)
function liveFilter() {
    const q = document.getElementById('liveSearch').value.toLowerCase();
    document.querySelectorAll('.student-row').forEach(row => {
        const match = row.dataset.name.includes(q) ||
                      row.dataset.email.includes(q) ||
                      row.dataset.roll.includes(q);
        row.style.display = match ? '' : 'none';
    });
}

// Sortable columns
let sortDir = {};
function sortTable(col) {
    const tbody = document.getElementById('tableBody');
    const rows  = [...tbody.querySelectorAll('tr')];
    const dir   = sortDir[col] = !(sortDir[col]);
    rows.sort((a, b) => {
        const va = a.cells[col].textContent.trim();
        const vb = b.cells[col].textContent.trim();
        return dir
            ? va.localeCompare(vb, undefined, { numeric: true })
            : vb.localeCompare(va, undefined, { numeric: true });
    });
    rows.forEach(r => tbody.appendChild(r));
}

// Export CSV
function exportCSV() {
    const table = document.getElementById('studentsTable');
    const rows  = [...table.querySelectorAll('tr')];
    const csv   = rows.map(r =>
        [...r.cells].slice(0, 7).map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(',')
    ).join('\n');
    const blob  = new Blob([csv], { type: 'text/csv' });
    const a     = document.createElement('a');
    a.href      = URL.createObjectURL(blob);
    a.download  = 'students_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}
</script>
</main>
</body>
</html>
