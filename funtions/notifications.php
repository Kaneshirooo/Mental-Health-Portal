<?php
require_once 'config.php';
requireLogin();
$user_id = $_SESSION['user_id'];

// AJAX: Return unread notification count for badge polling
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_GET['ajax_count'])) {
    header('Content-Type: application/json');
    $cnt = getNotificationCount($user_id);
    echo json_encode(['count' => $cnt]);
    exit;
}

// AJAX: Clear all notifications
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_POST['clear_all'])) {
    header('Content-Type: application/json');
    $conn->query("DELETE FROM notifications WHERE user_id = $user_id");
    echo json_encode(['success' => true]);
    exit;
}

// Mark all as read on page visit
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");

// Fetch all notifications, newest first
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .notif-card {
            background: white;
            border-radius: 18px;
            padding: 1.5rem 2rem;
            border: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            transition: var(--transition);
        }
        .notif-card:hover { border-color: var(--primary-light); box-shadow: var(--shadow-sm); }
        .notif-card.unread { background: #f5f3ff; border-color: rgba(99,102,241,0.2); }
        .notif-icon {
            width: 44px; height: 44px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex-shrink: 0;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width:760px; padding-top:3.5rem; padding-bottom:6rem;">

    <div style="margin-bottom:3rem;">
        <div style="font-weight:800; color:var(--primary); font-size:0.8rem; text-transform:uppercase; letter-spacing:0.12em; margin-bottom:0.5rem;">Inbox</div>
        <h1 style="font-family:'Outfit',sans-serif; font-size:2.5rem; font-weight:800; color:var(--primary-dark); margin-bottom:0.4rem;">Notifications</h1>
        <p style="color:var(--text-dim); font-weight:600;"><?php echo count($notifications); ?> notification<?php echo count($notifications) !== 1 ? 's' : ''; ?></p>
    </div>

    <?php if (empty($notifications)): ?>
    <div style="text-align:center; padding:5rem 2rem; background:white; border-radius:28px; border:2px dashed var(--border);">
        <div style="font-size:3rem; margin-bottom:1.5rem; opacity:0.3;">🔔</div>
        <h3 style="color:var(--text-dim); font-weight:700; margin-bottom:0.5rem;">All clear!</h3>
        <p style="color:var(--text-dim); font-size:0.95rem; font-weight:500;">You have no notifications yet.</p>
    </div>
    <?php else: ?>
    <div style="display:flex; flex-direction:column; gap:1rem;">
        <?php foreach ($notifications as $n):
            $typeIcon = ['appointment' => '📅', 'system' => '🔔'][$n['type']] ?? '🔔';
            $typeBg   = ['appointment' => '#eff6ff', 'system'  => '#f8fafc'][$n['type']] ?? '#f8fafc';
            $typeClr  = ['appointment' => '#3b82f6', 'system'  => '#94a3b8'][$n['type']] ?? '#94a3b8';
            $dtObj    = new DateTime($n['created_at']);
            $timeAgo  = $dtObj->format('M d, Y \a\t g:i A');
        ?>
        <div class="notif-card <?php echo !$n['is_read'] ? 'unread' : ''; ?>">
            <div class="notif-icon" style="background:<?php echo $typeBg; ?>; color:<?php echo $typeClr; ?>;">
                <?php echo $typeIcon; ?>
            </div>
            <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.4rem;">
                    <div style="font-weight:800; color:var(--text); font-size:0.97rem;"><?php echo htmlspecialchars($n['title']); ?></div>
                    <?php if (!$n['is_read']): ?>
                    <span style="width:8px; height:8px; background:var(--primary); border-radius:50%; flex-shrink:0; margin-top:5px;"></span>
                    <?php endif; ?>
                </div>
                <p style="color:var(--text-dim); font-size:0.9rem; font-weight:500; line-height:1.6; margin-bottom:0.5rem;"><?php echo htmlspecialchars($n['message']); ?></p>
                <div style="font-size:0.75rem; color:var(--text-dim); font-weight:700;"><?php echo $timeAgo; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top:2rem; text-align:center;">
        <button id="clearAllBtn" onclick="clearAllAjax()" style="background:white; border:1.5px solid var(--border); padding:0.85rem 2rem; border-radius:50px; font-weight:700; color:var(--text-dim); cursor:pointer; font-size:0.9rem;">Clear All</button>
    </div>
    <?php endif; ?>
</div>



<script>
async function clearAllAjax() {
    if (!confirm('Clear all notifications?')) return;
    const btn = document.getElementById('clearAllBtn');
    btn.disabled = true;
    btn.textContent = 'Clearing…';
    try {
        const fd = new FormData();
        fd.append('clear_all', '1');
        const res = await fetch('notifications.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            // Animate removal of notification cards
            document.querySelectorAll('.notif-card').forEach(card => {
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px)';
            });
            setTimeout(() => { window.location.reload(); }, 400);
        }
    } catch(e) {
        btn.disabled = false;
        btn.textContent = 'Clear All';
    }
}
</script>
<footer class="footer" style="padding:2.5rem; text-align:center; border-top:1px solid var(--border);">
    <p style="color:var(--text-dim); font-weight:700; font-size:0.85rem;">© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>
</main>
</body>
</html>
