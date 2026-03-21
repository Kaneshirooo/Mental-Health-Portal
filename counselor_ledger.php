<?php
require_once 'config.php';
requireCounselor();

$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

// Build query for session logs
$query = "SELECT sl.*, u.full_name, u.user_type, u.email, u.roll_number 
          FROM session_logs sl 
          JOIN users u ON sl.user_id = u.user_id 
          WHERE 1=1";

$params = [];
$types = "";

if ($search) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.roll_number LIKE ?)";
    $st = "%$search%";
    $params[] = $st; $params[] = $st; $params[] = $st;
    $types .= "sss";
}

if ($role_filter) {
    $query .= " AND u.user_type = ?";
    $params[] = $role_filter;
    $types .= "s";
}

$query .= " ORDER BY sl.login_time DESC LIMIT 100";

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function formatDuration($login, $logout) {
    if (!$logout) return '<span style="color:var(--success); font-weight:800;">LIVE SESSION</span>';
    $start = strtotime($login);
    $end = strtotime($logout);
    $diff = $end - $start;
    
    if ($diff < 60) return $diff . "s";
    if ($diff < 3600) return round($diff / 60) . "m";
    return round($diff / 3600, 1) . "h";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Ledger — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Audit & Governance</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Activity Ledger</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Journal of institutional interactions and clinical sessions.</p>
        </div>
        <div style="text-align: right; border-right: 1px solid var(--border); padding-right: 1.5rem;">
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary);"><?php echo count($logs); ?></div>
            <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em;">Interactions</div>
        </div>
    </div>

    <!-- Filters -->
    <div style="background: white; border-radius: var(--radius); padding: 1.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 2rem; display: flex; gap: 1rem; align-items: center;">
        <form method="GET" style="display: flex; gap: 1rem; flex: 1;">
            <div style="flex: 1; position: relative;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search identity or activity..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-size: 0.9rem; font-weight: 500; background: var(--surface-2);">
                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); opacity: 0.4; font-size: 0.9rem;">🔍</span>
            </div>
            
            <select name="role" onchange="this.form.submit()" style="padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); background: white; font-weight: 600; color: var(--text-muted); cursor: pointer; font-size: 0.85rem;">
                <option value="">All Roles</option>
                <option value="student" <?php echo $role_filter === 'student' ? 'selected' : ''; ?>>Students</option>
                <option value="counselor" <?php echo $role_filter === 'counselor' ? 'selected' : ''; ?>>Counselors</option>
                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admins</option>
            </select>
            
            <?php if ($search || $role_filter): ?>
                <a href="counselor_ledger.php" style="padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); background: #fff1f2; color: #e11d48; font-weight: 600; text-decoration: none; display: flex; align-items: center; font-size: 0.85rem;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Identity</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Login</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Logout</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Duration</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Activity</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): 
                    $nameParts = explode(' ', $log['full_name']);
                    $initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                    $is_online = !$log['logout_time'];
                ?>
                <tr style="border-bottom: 1px solid var(--border); transition: var(--transition);">
                    <td style="padding: 1rem 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface-2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; border: 1px solid var(--border);">
                                <?php echo $initials; ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: var(--text); font-size: 0.9rem; margin-bottom: 0.1rem;"><?php echo htmlspecialchars($log['full_name']); ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em;"><?php echo $log['user_type']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text); font-size: 0.85rem;">
                        <?php echo date('M d, H:i', strtotime($log['login_time'])); ?>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-weight: 500; color: var(--text-muted); font-size: 0.85rem;">
                        <?php echo $log['logout_time'] ? date('M d, H:i', strtotime($log['logout_time'])) : '--:--'; ?>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-weight: 700; color: var(--primary); font-size: 0.85rem;">
                        <?php echo formatDuration($log['login_time'], $log['logout_time']); ?>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-weight: 400; color: var(--text-dim); font-size: 0.85rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($log['activity']); ?>">
                        <?php echo htmlspecialchars($log['activity']); ?>
                    </td>
                    <td style="padding: 1rem 1.5rem; text-align: right;">
                        <?php if ($is_online): ?>
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.65rem; border-radius: 4px; background: #f0fdf4; color: #16a34a; font-weight: 700; font-size: 0.62rem; letter-spacing: 0.04em;">
                                <div style="width: 6px; height: 6px; border-radius: 50%; background: #16a34a;"></div>
                                LIVE
                            </div>
                        <?php else: ?>
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.65rem; border-radius: 4px; background: var(--surface-2); color: var(--text-muted); font-weight: 600; font-size: 0.62rem;">
                                ENDED
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($logs)): ?>
    <div style="text-align: center; padding: 10rem 2rem;">
        <div style="font-size: 4rem; opacity: 0.2; margin-bottom: 2rem;">📒</div>
        <h2 style="font-weight: 800; color: var(--text-dim);">No Records Found</h2>
        <p style="color: var(--text-dim);">The activity ledger is currently empty for the selected criteria.</p>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

</main>
</body>
</html>
