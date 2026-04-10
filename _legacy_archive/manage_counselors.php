<?php
require_once 'config.php';
requireAdmin();

$success = '';
$error   = '';

// Delete counselor only (never students or admins)
if (isset($_GET['delete'], $_GET['id'])) {
    $del_id   = intval($_GET['id']);
    $admin_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND user_type = 'counselor' AND user_id != ?");
    $stmt->bind_param("ii", $del_id, $admin_id);
    $stmt->execute();
    $stmt->affected_rows > 0
        ? ($success = 'Counselor removed successfully.') && queueToast($success, 'success', 'Counselor Removed')
        : ($error   = 'Could not remove counselor (may not exist or not a counselor).') && queueToast('Could not remove counselor.', 'error', 'Remove Failed');
}

// Search / filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$q = "SELECT u.user_id, u.full_name, u.email, u.department, u.created_at,
             COUNT(a.appointment_id) AS total_appointments
      FROM users u
      LEFT JOIN appointments a ON a.counselor_id = u.user_id
      WHERE u.user_type = 'counselor'";
$params = [];
$types  = '';

if ($search) {
    $q      .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
    $s       = "%$search%";
    $params  = [$s, $s];
    $types   = 'ss';
}

$q .= " GROUP BY u.user_id ORDER BY u.full_name ASC";

$stmt = $conn->prepare($q);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$counselors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Counselors — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.1">
    <?php include 'theme_init.php'; ?>
    <style>
        .counselor-card {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            margin-bottom: .75rem;
            transition: box-shadow var(--transition);
        }
        .counselor-card:hover { box-shadow: var(--shadow-sm); }
        .counselor-info strong { display:block; font-size:1rem; color:var(--text); }
        .counselor-meta { display:flex; gap:.75rem; margin-top:.35rem; flex-wrap:wrap; }
        .meta-chip { font-size:.78rem; background:var(--surface); border:1px solid var(--border);
                     border-radius:99px; padding:.2rem .65rem; color:var(--text-muted); }
        .delete-confirm { display:none; gap:.5rem; align-items:center; }
        .delete-confirm.show { display:flex; }
        .filter-row { display:flex; gap:.75rem; margin-bottom:1.25rem; flex-wrap:wrap; }
        .filter-row input { flex:1; min-width:180px; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container">
    <div class="dashboard-header">
        <h1>👩‍⚕️ Manage Counselors</h1>
        <p><?php echo count($counselors); ?> counselor(s) registered</p>
    </div>



    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem">
            <h2 style="margin:0">All Counselors
                <span style="font-size:.9rem;font-weight:400;color:var(--text-muted)">(<?php echo count($counselors); ?>)</span>
            </h2>
            <a href="head_counselor_manage.php" class="btn-primary btn-sm">➕ Add New Counselor</a>
        </div>

        <!-- Search bar -->
        <form method="GET" class="filter-row">
            <input type="text" name="search" placeholder="🔍 Search by name or email…"
                   value="<?php echo htmlspecialchars($search); ?>" id="liveSearch" oninput="liveFilter()">
            <button type="submit" class="btn-primary btn-sm">Search</button>
            <?php if ($search): ?>
                <a href="manage_counselors.php" class="btn-secondary btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (empty($counselors)): ?>
            <div class="empty-state" style="padding:2rem">
                <span class="empty-icon">👩‍⚕️</span>
                <h2>No counselors found</h2>
                <p>Add a counselor using the <a href="head_counselor_manage.php">Add Counselor</a> page.</p>
            </div>
        <?php else: ?>
            <?php foreach ($counselors as $c): ?>
            <div class="counselor-card" data-name="<?php echo strtolower($c['full_name']); ?>" data-email="<?php echo strtolower($c['email']); ?>">
                <div class="counselor-info">
                    <strong><?php echo htmlspecialchars($c['full_name']); ?></strong>
                    <div class="counselor-meta">
                        <span class="meta-chip">📧 <?php echo htmlspecialchars($c['email']); ?></span>
                        <?php if ($c['department']): ?>
                            <span class="meta-chip">🏫 <?php echo htmlspecialchars($c['department']); ?></span>
                        <?php endif; ?>
                        <span class="meta-chip">📅 <?php echo $c['total_appointments']; ?> appointment(s)</span>
                        <span class="meta-chip">🗓 Joined <?php echo date('M d, Y', strtotime($c['created_at'])); ?></span>
                    </div>
                </div>
                <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                    <button class="btn-sm btn-danger" onclick="confirmDel(<?php echo $c['user_id']; ?>, this)">🗑 Remove</button>
                    <span class="delete-confirm" id="dc-<?php echo $c['user_id']; ?>">
                        Sure?
                        <a href="?delete=1&id=<?php echo $c['user_id']; ?>" class="btn-sm btn-danger">Yes, Remove</a>
                        <button class="btn-sm btn-secondary" onclick="cancelDel(<?php echo $c['user_id']; ?>)">Cancel</button>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>

<script>
function confirmDel(id, btn) {
    btn.style.display = 'none';
    document.getElementById('dc-' + id).classList.add('show');
}
function cancelDel(id) {
    document.getElementById('dc-' + id).classList.remove('show');
    document.querySelector('[onclick="confirmDel(' + id + ', this)"]').style.display = '';
}
function liveFilter() {
    const q = document.getElementById('liveSearch').value.toLowerCase();
    document.querySelectorAll('.counselor-card').forEach(card => {
        card.style.display =
            card.dataset.name.includes(q) || card.dataset.email.includes(q) ? '' : 'none';
    });
}
</script>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
