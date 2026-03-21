<?php
require_once 'config.php';
$admin_id = $_SESSION['user_id'];

// Handle archiving (marking as read) - Must be before any output
if (isset($_GET['archive_note'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die("CSRF validation failed for archive action. Use the dashboard buttons.");
    }
    $nid = intval($_GET['archive_note']);
    // SQLi FIX: Use prepared statement
    $up_stmt = $conn->prepare("UPDATE anonymous_notes SET status = 'read' WHERE note_id = ?");
    $up_stmt->bind_param("i", $nid);
    $up_stmt->execute();
    redirect('admin_dashboard.php');
}

// Handle anonymous note reply POST
$qn_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_note_submit'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $note_id = intval($_POST['reply_note_id']);
    $reply_text = sanitize($_POST['reply_text']);
    if ($note_id && $reply_text) {
        $stmt = $conn->prepare(
            "INSERT INTO anonymous_note_messages (note_id, sender_type, message_text) VALUES (?, 'admin', ?)"
        );
        $stmt->bind_param("is", $note_id, $reply_text);
        if ($stmt->execute()) {
            // SQLi FIX: Use prepared statement
            $up_stmt2 = $conn->prepare("UPDATE anonymous_notes SET status = 'replied' WHERE note_id = ?");
            $up_stmt2->bind_param("i", $note_id);
            $up_stmt2->execute();
            $qn_msg = 'reply_success';
            logActivity($admin_id, "Admin replied to anonymous note #$note_id");
        }
        else {
            $qn_msg = 'reply_error';
        }
    }
}

// Stats
$stats = $conn->query(
    "SELECT
       (SELECT COUNT(*) FROM users WHERE user_type='student')   as total_students,
       (SELECT COUNT(*) FROM users WHERE user_type='counselor') as total_counselors,
       (SELECT COUNT(DISTINCT user_id) FROM assessment_scores)  as students_assessed,
       (SELECT COUNT(*) FROM assessment_scores)                 as total_assessments,
       (SELECT AVG(overall_score) FROM assessment_scores)       as avg_score"
)->fetch_assoc();

// Activity log
$log_result = $conn->query(
    "SELECT sl.login_time, sl.activity, u.full_name, u.user_type
     FROM session_logs sl JOIN users u ON sl.user_id = u.user_id
     ORDER BY sl.login_time DESC LIMIT 15"
);
$log_rows = $log_result->fetch_all(MYSQLI_ASSOC);

// Fetch anonymous notes and their conversation history
$anon_notes_res = $conn->query(
    "SELECT n.note_id, n.status, n.created_at, 
            (SELECT message_text FROM anonymous_note_messages WHERE note_id = n.note_id ORDER BY created_at DESC LIMIT 1) as last_text,
            (SELECT sender_type FROM anonymous_note_messages WHERE note_id = n.note_id ORDER BY created_at DESC LIMIT 1) as last_sender
     FROM anonymous_notes n 
     WHERE n.status IN ('new', 'read', 'replied') 
     ORDER BY n.created_at DESC"
);

$anon_notes = [];
while ($row = $anon_notes_res->fetch_assoc()) {
    // SQLi FIX: Use prepared statement
    $note_id_val = (int)$row['note_id'];
    $msg_stmt = $conn->prepare("SELECT sender_type, message_text, created_at FROM anonymous_note_messages WHERE note_id = ? ORDER BY created_at ASC");
    $msg_stmt->bind_param("i", $note_id_val);
    $msg_stmt->execute();
    $msg_res = $msg_stmt->get_result();
    $row['conversation'] = $msg_res->fetch_all(MYSQLI_ASSOC);
    $anon_notes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .governance-matrix {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .gov-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .gov-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); border-color: var(--border-hover); }
        
        .activity-registry {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        .registry-table th { 
            padding: 0.85rem 1.25rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }
        .registry-table td { 
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .action-hub {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .action-link-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: var(--transition);
        }
        .action-link-card:hover {
            background: var(--primary-glow);
            border-color: var(--primary);
            transform: translateY(-3px);
        }
        .action-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            background: var(--primary-glow);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">System Administration</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Admin Panel</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">System status: Active. Last updated at <?php echo date('H:i'); ?>.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <div style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); background: #ecfdf5; color: #059669; font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(5, 150, 105, 0.08);">
                <span style="width: 7px; height: 7px; background: #10b981; border-radius: 50%;"></span> Online
            </div>
            <button onclick="location.reload()" style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: white; font-weight: 600; cursor: pointer; font-size: 0.82rem; color: var(--text-muted);">Refresh</button>
        </div>
    </div>

    <!-- Operational Matrix -->
    <div class="governance-matrix">
        <div class="gov-card">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Students</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1;"><?php echo $stats['total_students']; ?></div>
        </div>
        <div class="gov-card">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Counselors</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary); line-height: 1;"><?php echo $stats['total_counselors']; ?></div>
        </div>
        <div class="gov-card">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Completion %</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1;">
                <?php echo round(($stats['students_assessed'] / ($stats['total_students'] ?: 1)) * 100); ?><span style="font-size: 1rem; opacity: 0.3; margin-left: 2px;">%</span>
            </div>
        </div>
        <div class="gov-card">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Total Assessments</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1;"><?php echo $stats['total_assessments']; ?></div>
        </div>
        <div class="gov-card" style="background: var(--surface-2);">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Avg Score</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1;"><?php echo round($stats['avg_score'] ?? 0); ?><span style="font-size: 1rem; opacity: 0.3; margin-left: 2px;">%</span></div>
        </div>
    </div>

    <div class="action-hub" style="margin-bottom: 3rem;">
        <a href="head_counselor_manage.php" class="action-link-card" style="padding: 1.25rem;">
            <div class="action-icon-circle" style="width: 40px; height: 40px; font-size: 1rem;">➕</div>
            <div>
                <div style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 0.25rem;">Onboard Counselor</div>
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 400;">Expand the institutional clinical capacity.</div>
            </div>
        </a>
        <a href="admin_reports.php" class="action-link-card" style="padding: 1.25rem;">
            <div class="action-icon-circle" style="width: 40px; height: 40px; font-size: 1rem; background: #f0fdfa; color: #0d9488;">📊</div>
            <div>
                <div style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 0.25rem;">Institutional Analytics</div>
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 400;">Full-scale diagnostic mapping and search.</div>
            </div>
        </a>
    </div>

    <!-- Activity Registry -->
    <div class="activity-registry">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 0.25rem;">System Activity Ledger</h2>
                <p style="color: var(--text-muted); font-weight: 400; font-size: 0.88rem;">Journal of institutional interactions and governance events.</p>
            </div>
            <button onclick="location.reload()" style="font-weight: 600; color: var(--primary); background: none; border: none; cursor: pointer; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em;">Refresh Ledger →</button>
        </div>

        <table class="registry-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 1rem; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Identity</th>
                    <th style="text-align: left; padding: 1rem; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Role</th>
                    <th style="text-align: left; padding: 1rem; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Context</th>
                    <th style="text-align: right; padding: 1rem; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($log_rows as $log): ?>
                <tr style="border-bottom: 1px solid var(--border); transition: var(--transition);">
                    <td style="padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface-2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; border: 1px solid var(--border); font-size: 0.75rem;">
                                <?php echo strtoupper(substr($log['full_name'], 0, 1)); ?>
                            </div>
                            <span style="color: var(--text); font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($log['full_name']); ?></span>
                        </div>
                    </td>
                    <td style="padding: 1rem;">
                        <span style="padding: 0.35rem 0.75rem; border-radius: 4px; font-weight: 700; font-size: 0.62rem; text-transform: uppercase; 
                            <?php echo $log['user_type'] === 'admin' ? 'background: #fff1f2; color: #e11d48;' : ($log['user_type'] === 'counselor' ? 'background: #eff6ff; color: #2563eb;' : 'background: #f0fdf4; color: #16a34a;'); ?>">
                            <?php echo $log['user_type']; ?>
                        </span>
                    </td>
                    <td style="padding: 1rem; color: var(--text-muted); font-weight: 400; font-size: 0.88rem;"><?php echo htmlspecialchars($log['activity']); ?></td>
                    <td style="padding: 1rem; text-align: right; color: var(--text-muted); font-weight: 500; font-size: 0.82rem;">
                        <?php echo date('M d, H:i', strtotime($log['login_time'])); ?>
                    </td>
                </tr>
                <?php
endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

</main>
</body>
</html>
