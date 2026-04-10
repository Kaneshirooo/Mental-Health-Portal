<?php
require_once 'config.php';
requireCounselor();

$search = '';
$filter = '';

if (isset($_GET['search']))
    $search = sanitize($_GET['search']);
if (isset($_GET['filter']))
    $filter = sanitize($_GET['filter']);

// Build query
$query = "SELECT u.user_id, u.full_name, u.email, u.roll_number, u.department, u.semester,
          a.overall_score, a.risk_level, a.assessment_date
          FROM users u
          LEFT JOIN assessment_scores a ON u.user_id = a.user_id
          WHERE u.user_type = 'student'";

$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.roll_number LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term];
    $types = 'sss';
}
if (!empty($filter)) {
    $query .= " AND a.risk_level = ?";
    $params[] = $filter;
    $types .= 's';
}
$query .= " GROUP BY u.user_id ORDER BY u.full_name";

$stmt = $conn->prepare($query);
if (!empty($params))
    $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Build CSV data
$csv_ready = $rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.1">
    <?php include 'theme_init.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Administrative Console</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Student Directory</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Registry of student wellness data and clinical assessments.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <div style="text-align: right; padding: 0 1.5rem; border-right: 1px solid var(--border);">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary);"><?php echo count($rows); ?></div>
                <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em;">Total Records</div>
            </div>
            <button onclick="exportCSV()" style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); background: var(--surface-solid); color: var(--text); font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                <span>📊</span> Export CSV
            </button>
        </div>
    </div>

    <!-- Interface Controls -->
    <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 1.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 2rem; display: flex; gap: 1rem; align-items: center;">
        <div style="flex: 1; position: relative;">
            <input type="text" id="liveSearch" oninput="liveFilter()" placeholder="Identify student by name, ID..." style="width: 100%; padding: 0.75rem 1.25rem 0.75rem 2.75rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-size: 0.9rem; font-weight: 500; background: var(--surface-2);">
            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); opacity: 0.4; font-size: 0.9rem;">🔍</span>
        </div>
        
        <form method="GET" id="filterForm" style="display: flex; gap: 0.75rem;">
            <select name="filter" onchange="this.form.submit()" style="padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                <option value="">All Risk Levels</option>
                <?php foreach (['Low', 'Moderate', 'High', 'Critical'] as $lvl): ?>
                    <option value="<?php echo $lvl; ?>" <?php echo($filter === $lvl) ? 'selected' : ''; ?>><?php echo $lvl; ?> Priority</option>
                <?php
endforeach; ?>
            </select>
            <?php if ($search || $filter): ?>
                <a href="student_list.php" style="padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); background: #fff1f2; color: #e11d48; font-weight: 600; text-decoration: none; display: flex; align-items: center; font-size: 0.85rem;">Reset</a>
            <?php
endif; ?>
        </form>
    </div>

    <div style="background: var(--surface-solid); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Student Identity</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">ID</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Unit</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Wellness</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Priority</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php foreach ($rows as $student):
    $initials = strtoupper(substr($student['full_name'], 0, 1) . substr(explode(' ', $student['full_name'])[1] ?? '', 0, 1));
    $row_status = strtolower($student['risk_level'] ?? 'unassessed');
?>
                <tr class="student-row" style="border-bottom: 1px solid var(--border); transition: var(--transition);"
                    data-name="<?php echo strtolower($student['full_name']); ?>"
                    data-email="<?php echo strtolower($student['email']); ?>"
                    data-roll="<?php echo strtolower($student['roll_number']); ?>">
                    <td style="padding: 1rem 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--surface-2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; border: 1px solid var(--border);">
                                <?php echo $initials; ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: var(--text); font-size: 0.92rem;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 400;"><?php echo htmlspecialchars($student['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1rem 1.5rem;">
                        <span style="font-size: 0.72rem; font-weight: 600; color: var(--text-muted); background: var(--surface-2); padding: 0.25rem 0.5rem; border-radius: 4px; border: 1px solid var(--border);">
                            <?php echo htmlspecialchars($student['roll_number']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-weight: 500; color: var(--text-muted); font-size: 0.82rem;">
                        <?php echo htmlspecialchars($student['department']); ?>
                    </td>
                    <td style="padding: 1rem 1.5rem;">
                        <?php if ($student['overall_score']): ?>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="flex: 1; min-width: 50px; height: 5px; background: var(--surface-2); border-radius: 10px; overflow: hidden;">
                                    <div style="width: <?php echo $student['overall_score']; ?>%; height: 100%; background: var(--primary);"></div>
                                </div>
                                <span style="font-weight: 600; color: var(--primary); font-size: 0.82rem;"><?php echo $student['overall_score']; ?></span>
                            </div>
                        <?php
    else: ?>
                            <span style="color: var(--text-dim); font-weight: 500; font-size: 0.75rem;">UNCATEGORIZED</span>
                        <?php
    endif; ?>
                    </td>
                    <td style="padding: 1rem 1.5rem;">
                        <?php if ($student['risk_level']): ?>
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 700; font-size: 0.65rem; letter-spacing: 0.04em;
                                <?php
        $colors = [
            'Low' => ['bg' => '#f0fdf4', 'text' => '#16a34a'],
            'Moderate' => ['bg' => '#fffbeb', 'text' => '#d97706'],
            'High' => ['bg' => '#fff1f2', 'text' => '#e11d48'],
            'Critical' => ['bg' => '#450a0a', 'text' => '#ffffff']
        ];
        $c = $colors[$student['risk_level']];
        echo "background: {$c['bg']}; color: {$c['text']};";
?> border: 1px solid transparent;">
                                <div style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></div>
                                <?php echo strtoupper($student['risk_level']); ?>
                            </div>
                        <?php
    else: ?>
                            <span style="font-size: 0.65rem; font-weight: 600; color: var(--text-dim); opacity: 0.5;">NONE</span>
                        <?php
    endif; ?>
                    </td>
                    <td style="padding: 1rem 1.5rem; text-align: right;">
                        <a href="view_assessment.php?user_id=<?php echo $student['user_id']; ?>" style="text-decoration: none; font-weight: 600; font-size: 0.8rem; color: var(--primary); padding: 0.5rem 1rem; border-radius: 6px; background: #f0fdfa; border: 1px solid rgba(13, 148, 136, 0.1); transition: var(--transition);">Records →</a>
                    </td>
                </tr>
                <?php
endforeach; ?>
            </tbody>
        </table>
    </div>
<?php if (empty($rows)): ?>
    <div class="empty-state">
        <span class="empty-icon">🔍</span>
        <h2>No Students Found</h2>
        <p>Try adjusting your search or filter criteria.</p>
    </div>
    <?php
endif; ?>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
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
