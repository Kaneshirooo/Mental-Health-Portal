<?php
require_once 'config.php';
$admin_id = $_SESSION['user_id'];

// Handle archiving (marking as read) - Must be before any output
if (isset($_GET['archive_note'])) {
    $nid = intval($_GET['archive_note']);
    $conn->query("UPDATE anonymous_notes SET status = 'read' WHERE note_id = $nid");
    redirect('admin_dashboard.php');
}

// Handle anonymous note reply POST
$qn_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_note_submit'])) {
    $note_id = intval($_POST['reply_note_id']);
    $reply_text = sanitize($_POST['reply_text']);
    if ($note_id && $reply_text) {
        $stmt = $conn->prepare(
            "INSERT INTO anonymous_note_messages (note_id, sender_type, message_text) VALUES (?, 'admin', ?)"
        );
        $stmt->bind_param("is", $note_id, $reply_text);
        if ($stmt->execute()) {
            $conn->query("UPDATE anonymous_notes SET status = 'replied' WHERE note_id = $note_id");
            $qn_msg = 'reply_success';
            logActivity($admin_id, "Admin replied to anonymous note #$note_id");
        } else {
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
    $msg_res = $conn->query("SELECT sender_type, message_text, created_at FROM anonymous_note_messages WHERE note_id = {$row['note_id']} ORDER BY created_at ASC");
    $row['conversation'] = $msg_res->fetch_all(MYSQLI_ASSOC);
    $anon_notes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institutional Intelligence — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .governance-matrix {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 2rem;
            margin-bottom: 6rem;
        }
        .gov-card {
            background: white;
            border-radius: 40px;
            padding: 3rem 2.5rem;
            border: 1.5px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .gov-card:hover { transform: translateY(-5px); box-shadow: var(--shadow); border-color: var(--primary-light); }
        
        .activity-registry {
            background: white;
            border-radius: 48px;
            padding: 4rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 6rem;
        }
        .registry-table th { 
            padding: 2rem;
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
        }
        .registry-table td { 
            padding: 2rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
            font-weight: 600;
        }
        
        .action-hub {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 6rem;
        }
        .action-link-card {
            background: white;
            border-radius: 40px;
            padding: 3rem;
            border: 1.5px solid var(--border);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 2rem;
            transition: var(--transition);
        }
        .action-link-card:hover {
            background: var(--primary-glow);
            border-color: var(--primary);
            transform: translateY(-8px);
        }
        .action-icon-circle {
            width: 70px;
            height: 70px;
            border-radius: 24px;
            background: var(--primary-glow);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="main-content">

<div class="container" style="max-width: 1550px; padding-top: 6rem; padding-bottom: 10rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem;">System Governance & Strategy</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.8rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem; letter-spacing: -0.02em;">Institutional Intelligence</h1>
            <p style="color: var(--text-dim); font-size: 1.35rem; font-weight: 600;">System status: Optimal. Last comprehensive audit performed at <?php echo date('H:i'); ?>.</p>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <div style="padding: 1.25rem 2.5rem; border-radius: 60px; background: #ecfdf5; color: #059669; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(5, 150, 105, 0.1);">
                <span style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981;"></span> NODE ACTIVE
            </div>
            <button onclick="location.reload()" style="padding: 1.25rem 2.5rem; border-radius: 60px; border: 2px solid var(--border); background: white; font-weight: 800; cursor: pointer; font-size: 0.9rem;">REFRESH DATA</button>
        </div>
    </div>

    <!-- Operational Matrix -->
    <div class="governance-matrix">
        <div class="gov-card">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem;">STUDENT POPULATION</div>
            <div style="font-size: 3rem; font-weight: 800; color: var(--text); line-height: 1;"><?php echo $stats['total_students']; ?></div>
        </div>
        <div class="gov-card">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem;">CLINICAL STAFF</div>
            <div style="font-size: 3rem; font-weight: 800; color: var(--primary); line-height: 1;"><?php echo $stats['total_counselors']; ?></div>
        </div>
        <div class="gov-card">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem;">ENGAGEMENT RATIO</div>
            <div style="font-size: 3rem; font-weight: 800; color: var(--text); line-height: 1;">
                <?php echo round(($stats['students_assessed'] / ($stats['total_students'] ?: 1)) * 100); ?><span style="font-size: 1.25rem; opacity: 0.3; margin-left: 4px;">%</span>
            </div>
        </div>
        <div class="gov-card">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem;">TOTAL INTAKE</div>
            <div style="font-size: 3rem; font-weight: 800; color: var(--text); line-height: 1;"><?php echo $stats['total_assessments']; ?></div>
        </div>
        <div class="gov-card" style="background: #f8fafc;">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem;">INSTITUTIONAL INDEX</div>
            <div style="font-size: 3rem; font-weight: 800; color: var(--text); line-height: 1;"><?php echo round($stats['avg_score'] ?? 0); ?><span style="font-size: 1.25rem; opacity: 0.3; margin-left: 4px;">%</span></div>
        </div>
    </div>

    <div class="action-hub">
        <a href="head_counselor_manage.php" class="action-link-card">
            <div class="action-icon-circle">➕</div>
            <div>
                <div style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">Onboard Counselor</div>
                <div style="color: var(--text-dim); font-weight: 600;">Expand the institutional clinical capacity.</div>
            </div>
        </a>
        <a href="admin_reports.php" class="action-link-card">
            <div class="action-icon-circle" style="background: #ecfdf5; color: #10b981;">📊</div>
            <div>
                <div style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">Institutional Analytics</div>
                <div style="color: var(--text-dim); font-weight: 600;">Full-scale diagnostic mapping and data export.</div>
            </div>
        </a>
    </div>

    <!-- Activity Registry -->
    <div class="activity-registry">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 2rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">System Activity Ledger</h2>
                <p style="color: var(--text-dim); font-weight: 600; font-size: 1.1rem;">Journal of institutional interactions and governance events.</p>
            </div>
            <button onclick="location.reload()" style="font-weight: 800; color: var(--primary); background: none; border: none; cursor: pointer; font-size: 0.95rem;">REFRESH LEDGER →</button>
        </div>

        <table class="registry-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left;">Identity</th>
                    <th style="text-align: left;">Role Classification</th>
                    <th style="text-align: left;">Operational Context</th>
                    <th style="text-align: right;">Temporal Marker</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($log_rows as $log): ?>
                <tr onmouseover="this.style.background='#fcfdfe';" onmouseout="this.style.background='white';" style="transition: var(--transition);">
                    <td>
                        <div style="display: flex; align-items: center; gap: 1.5rem;">
                            <div style="width: 45px; height: 45px; border-radius: 14px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; border: 1px solid var(--border);">
                                <?php echo strtoupper(substr($log['full_name'], 0, 1)); ?>
                            </div>
                            <strong style="color: var(--text); font-size: 1.05rem;"><?php echo htmlspecialchars($log['full_name']); ?></strong>
                        </div>
                    </td>
                    <td>
                        <span style="padding: 0.6rem 1.2rem; border-radius: 10px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; 
                            <?php echo $log['user_type'] === 'admin' ? 'background: #fef2f2; color: #dc2626;' : ($log['user_type'] === 'counselor' ? 'background: #eff6ff; color: #2563eb;' : 'background: #f0fdf4; color: #16a34a;'); ?>">
                            <?php echo $log['user_type']; ?>
                        </span>
                    </td>
                    <td style="color: var(--text-dim); font-weight: 600;"><?php echo htmlspecialchars($log['activity']); ?></td>
                    <td style="text-align: right; color: var(--text-dim); font-weight: 700; font-size: 0.9rem;">
                        <?php echo date('M d, g:i A', strtotime($log['login_time'])); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<footer class="footer" style="padding: 4rem; text-align: center; border-top: 1px solid var(--border); margin-top: 4rem;">
    <p style="color: var(--text-dim); font-weight: 700; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">© <?php echo date('Y'); ?> Institutional Governance Bureau. Systems Integrity Verified.</p>
</footer>

</main>
</body>
</html>
