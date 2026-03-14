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
    <?php require_once 'pwa_head.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1400px; padding-top: 5rem; padding-bottom: 8rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5rem;">
        <div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.75rem;">Activity Ledger</h1>
            <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">Encrypted journal of institutional authentication and session trajectories.</p>
        </div>
        <div style="text-align: right; padding: 0 2rem;">
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary);"><?php echo count($logs); ?></div>
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Recent Interactions</div>
        </div>
    </div>

    <!-- Filters -->
    <div style="background: white; border-radius: 32px; padding: 2.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 4rem; display: flex; gap: 1.5rem; align-items: center;">
        <form method="GET" style="display: flex; gap: 1.5rem; flex: 1;">
            <div style="flex: 1; position: relative;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, ID, or activity..." style="width: 100%; padding: 1.25rem 1.5rem 1.25rem 3.5rem; border-radius: 50px; border: 1.5px solid var(--border); font-size: 1rem; font-weight: 600; background: #f8fafc;">
                <span style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); opacity: 0.4;">🔍</span>
            </div>
            
            <select name="role" onchange="this.form.submit()" style="padding: 1.25rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); background: white; font-weight: 800; color: var(--text); cursor: pointer;">
                <option value="">ALL ROLES</option>
                <option value="student" <?php echo $role_filter === 'student' ? 'selected' : ''; ?>>STUDENTS</option>
                <option value="counselor" <?php echo $role_filter === 'counselor' ? 'selected' : ''; ?>>COUNSELORS</option>
                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>ADMINS</option>
            </select>
            
            <?php if ($search || $role_filter): ?>
                <a href="counselor_ledger.php" style="padding: 1.25rem 2rem; border-radius: 50px; background: #fee2e2; color: #b91c1c; font-weight: 800; text-decoration: none; display: flex; align-items: center;">RESET</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div style="background: white; border-radius: 40px; border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Identity</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Login Marker</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Logout Marker</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Session Duration</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Activity Context</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): 
                    $initials = strtoupper(substr($log['full_name'], 0, 1) . substr(explode(' ', $log['full_name'])[1] ?? '', 0, 1));
                    $is_online = !$log['logout_time'];
                ?>
                <tr style="border-bottom: 1px solid #f8fafc; transition: var(--transition);">
                    <td style="padding: 1.5rem 2rem;">
                        <div style="display: flex; align-items: center; gap: 1.25rem;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; border: 1px solid var(--border);">
                                <?php echo $initials; ?>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--text); font-size: 1rem; margin-bottom: 0.1rem;"><?php echo htmlspecialchars($log['full_name']); ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-dim); font-weight: 700; text-transform: uppercase;"><?php echo $log['user_type']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1.5rem 2rem; font-weight: 700; color: var(--text); font-size: 0.9rem;">
                        <?php echo date('M d, H:i:s', strtotime($log['login_time'])); ?>
                    </td>
                    <td style="padding: 1.5rem 2rem; font-weight: 600; color: var(--text-dim); font-size: 0.9rem;">
                        <?php echo $log['logout_time'] ? date('M d, H:i:s', strtotime($log['logout_time'])) : '-- : -- : --'; ?>
                    </td>
                    <td style="padding: 1.5rem 2rem; font-weight: 800; color: var(--primary); font-size: 0.9rem;">
                        <?php echo formatDuration($log['login_time'], $log['logout_time']); ?>
                    </td>
                    <td style="padding: 1.5rem 2rem; font-weight: 600; color: var(--text-dim); font-size: 0.85rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?php echo htmlspecialchars($log['activity']); ?>
                    </td>
                    <td style="padding: 1.5rem 2rem; text-align: right;">
                        <?php if ($is_online): ?>
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 10px; background: #ecfdf5; color: #10b981; font-weight: 800; font-size: 0.7rem; letter-spacing: 0.05em;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; animation: pulse 1.5s infinite;"></div>
                                ONLINE
                            </div>
                        <?php else: ?>
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 10px; background: #f8fafc; color: var(--text-dim); font-weight: 700; font-size: 0.7rem;">
                                OFFLINE
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
    <p>© <?php echo date('Y'); ?> Mental Health Ledger System. Confidential Records.</p>
</footer>

</main>
</body>
</html>
