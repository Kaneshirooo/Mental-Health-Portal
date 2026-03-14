<?php
require_once 'config.php';
requireLogin();
// Allow both counselors AND the head counselor (admin) to manage their own appointments
if (!isCounselor() && !isAdmin()) {
    redirect('unauthorized.php');
}

$counselor_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// AJAX action handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_POST['appt_action'], $_POST['appointment_id'])) {
    header('Content-Type: application/json');
    $appt_id = (int)$_POST['appointment_id'];
    $action  = $_POST['appt_action'];
    $msg     = trim($_POST['counselor_message'] ?? '');

    $allowed = ['confirm' => 'confirmed', 'decline' => 'declined', 'cancel' => 'cancelled', 'complete' => 'completed'];
    if (!isset($allowed[$action])) {
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        exit;
    }

    $newStatus = $allowed[$action];
    $stmt = $conn->prepare(
        "UPDATE appointments
         SET status = ?, counselor_message = ?, updated_at = NOW()
         WHERE appointment_id = ? AND counselor_id = ?"
    );
    $stmt->bind_param("ssii", $newStatus, $msg, $appt_id, $counselor_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        logActivity($counselor_id, 'Counselor updated appointment #' . $appt_id . ' to ' . $newStatus);

        // Notify the student
        $apptInfo = $conn->query("SELECT a.student_id, a.scheduled_at, u.full_name AS cname FROM appointments a JOIN users u ON a.counselor_id=u.user_id WHERE a.appointment_id=$appt_id LIMIT 1")->fetch_assoc();
        if ($apptInfo) {
            $dateStr = date('F d, Y \a\t g:i A', strtotime($apptInfo['scheduled_at']));
            $notifMessages = [
                'confirmed'  => ['Appointment Confirmed ✅', 'Your session with ' . $apptInfo['cname'] . ' on ' . $dateStr . ' has been confirmed.'],
                'declined'   => ['Appointment Declined', 'Your session request for ' . $dateStr . ' was declined. Please book another slot.'],
                'cancelled'  => ['Appointment Cancelled', 'Your session on ' . $dateStr . ' has been cancelled.'],
                'completed'  => ['Session Completed 🎓', 'Your counseling session on ' . $dateStr . ' has been marked complete.'],
            ];
            if (isset($notifMessages[$newStatus])) {
                createNotification($apptInfo['student_id'], $notifMessages[$newStatus][0], $notifMessages[$newStatus][1], 'appointment');
            }
        }
        echo json_encode(['success' => true, 'newStatus' => $newStatus]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Could not update appointment.']);
    }
    exit;
}

// Normal POST (non-AJAX fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appt_action'], $_POST['appointment_id'])) {
    $appt_id = (int)$_POST['appointment_id'];
    $action  = $_POST['appt_action'];
    $msg     = trim($_POST['counselor_message'] ?? '');

    $allowed = ['confirm' => 'confirmed', 'decline' => 'declined', 'cancel' => 'cancelled', 'complete' => 'completed'];
    if (isset($allowed[$action])) {
        $newStatus = $allowed[$action];
        $stmt = $conn->prepare(
            "UPDATE appointments
             SET status = ?, counselor_message = ?, updated_at = NOW()
             WHERE appointment_id = ? AND counselor_id = ?"
        );
        $stmt->bind_param("ssii", $newStatus, $msg, $appt_id, $counselor_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = 'Appointment updated.';
            queueToast($success, 'success', 'Appointment Updated');
            logActivity($counselor_id, 'Counselor updated appointment #' . $appt_id . ' to ' . $newStatus);
        } else {
            $error = 'Could not update appointment.';
            queueToast($error, 'error', 'Update Failed');
        }
    }
}

// Fetch appointments
$stmt = $conn->prepare(
    "SELECT a.*, u.full_name AS student_name, u.roll_number, u.department, u.email
     FROM appointments a
     JOIN users u ON a.student_id = u.user_id
     WHERE a.counselor_id = ?
     ORDER BY a.scheduled_at ASC"
);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$now = new DateTime();
$upcoming = [];
$past = [];
foreach ($rows as $r) {
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $r['scheduled_at']);
    if ($dt && $dt >= $now) {
        $upcoming[] = $r;
    } else {
        $past[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments — Counselor</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .appt-pill { font-size:.7rem; padding:.4rem .8rem; border-radius:99px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase; }
        .appt-requested { background:rgba(245, 158, 11, 0.1); color:#d97706; border: 1px solid rgba(245, 158, 11, 0.2); }
        .appt-confirmed { background:rgba(16, 185, 129, 0.1); color:#059669; border: 1px solid rgba(16, 185, 129, 0.2); }
        .appt-declined, .appt-cancelled { background:rgba(239, 68, 68, 0.1); color:#dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }
        .appt-completed { background:rgba(79, 70, 229, 0.1); color:#4f46e5; border: 1px solid rgba(79, 70, 229, 0.2); }
        
        .card:hover { border-color: var(--primary-light); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1400px; padding-top: 5rem; padding-bottom: 8rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Clinical Intake & Session Manager</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.75rem;">Manage Your Schedule</h1>
            <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">System is active. <?php echo count($upcoming); ?> upcoming sessions require attention.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <div style="text-align: right; padding: 0 2rem; border-right: 2px solid var(--border);">
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary);"><?php echo count($upcoming); ?></div>
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Upcoming Slots</div>
            </div>
            <button onclick="window.print()" style="padding: 1rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); background: white; font-weight: 800; cursor: pointer;">PRINT AGENDA</button>
        </div>
    </div>



    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; margin-bottom: 3rem; color: var(--text);">Active Intake Queue</h2>
    <?php if (empty($upcoming)): ?>
        <div style="padding: 6rem; text-align: center; background: #f8fafc; border-radius: 40px; border: 2.5px dashed var(--border);">
            <div style="font-size: 4rem; margin-bottom: 2rem;">☕</div>
            <h3 style="color: var(--text-dim); font-weight: 700; font-size: 1.25rem;">Your queue is currently clear.</h3>
            <p style="color: var(--text-dim); opacity: 0.7; font-weight: 600;">Take this time for clinical documentation or research.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
            <?php foreach ($upcoming as $a): 
                $st = $a['status'];
                $is_pending = ($st === 'requested');
            ?>
            <div style="background: white; border-radius: 40px; padding: 3.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); position: relative; transition: var(--transition);" onmouseover="this.style.borderColor='var(--primary-light)'; this.style.boxShadow='var(--shadow)';" onmouseout="this.style.borderColor='var(--border)'; this.style.boxShadow='var(--shadow-sm)';">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 3rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <div style="width: 60px; height: 60px; border-radius: 18px; background: #f8fafc; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.25rem; border: 1px solid var(--border);">
                            <?php echo strtoupper(substr($a['student_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-weight: 800; color: var(--text); font-size: 1.25rem; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($a['student_name']); ?></div>
                            <div style="font-size: 0.85rem; color: var(--text-dim); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">ID: <?php echo $a['roll_number']; ?> • <?php echo $a['department']; ?></div>
                        </div>
                    </div>
                    <span style="padding: 0.6rem 1.25rem; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; 
                        background: <?php echo $is_pending ? '#fffbeb' : '#ecfdf5'; ?>;
                        color: <?php echo $is_pending ? '#d97706' : '#059669'; ?>; border: 1.5px solid currentColor; opacity: 0.8;">
                        <?php echo $st; ?>
                    </span>
                </div>

                <div style="background: #f8fafc; border-radius: 20px; padding: 2.5rem; margin-bottom: 3rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                        <span style="font-size: 1.5rem;">🗓️</span>
                        <div style="font-weight: 800; font-size: 1.1rem; color: var(--primary);"><?php echo date('F d, Y @ g:i A', strtotime($a['scheduled_at'])); ?></div>
                    </div>
                    <?php if ($a['reason']): ?>
                    <p style="font-size: 0.95rem; line-height: 1.6; color: var(--text-dim); font-weight: 600; font-style: italic; border-left: 3px solid var(--border); padding-left: 1.5rem;">
                        "<?php echo htmlspecialchars($a['reason']); ?>"
                    </p>
                    <?php endif; ?>
                </div>

                <div id="card-alert-<?php echo $a['appointment_id']; ?>" style="display:none; padding:0.5rem 1rem; border-radius:10px; font-weight:800; font-size:0.8rem; margin-bottom:0.75rem;"></div>
                <div style="display: flex; gap: 1rem;">
                    <?php if ($is_pending): ?>
                        <button onclick="apptAction('confirm', <?php echo $a['appointment_id']; ?>, this)" style="flex:1; padding: 1rem; border-radius: 50px; background: var(--primary); color: white; border: none; font-weight: 800; cursor: pointer;">CONFIRM SESSION</button>
                    <?php else: ?>
                        <button onclick="apptAction('complete', <?php echo $a['appointment_id']; ?>, this)" style="flex:1; padding: 1rem; border-radius: 50px; background: #ecfdf5; color: #059669; border: 1px solid #059669; font-weight: 800; cursor: pointer;">MARK COMPLETED</button>
                    <?php endif; ?>
                    
                    <button onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='block'?'none':'block'" style="padding: 1rem 2rem; border-radius: 50px; background: white; border: 1.5px solid #fee2e2; color: #dc2626; font-weight: 800; cursor: pointer;">...</button>
                    <div style="display: none; position: absolute; bottom: 2rem; right: 2rem; background: white; border-radius: 20px; padding: 1.5rem; box-shadow: var(--shadow); border: 1px solid var(--border); z-index: 10;">
                        <button onclick="apptAction('decline', <?php echo $a['appointment_id']; ?>, this)" style="width: 100%; padding: 0.75rem 1.5rem; border-radius: 10px; background: #fff1f2; color: #dc2626; border: none; font-weight: 800; cursor: pointer; margin-bottom:0.5rem;">Decline Request</button>
                        <button onclick="apptAction('cancel', <?php echo $a['appointment_id']; ?>, this)" style="width: 100%; padding: 0.75rem 1.5rem; border-radius: 10px; background: #f8fafc; color: var(--text-dim); border: none; font-weight: 800; cursor: pointer;">Cancel Session</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; margin-top: 6rem; margin-bottom: 3rem; color: var(--text);">Session Archive</h2>
    <div style="background: white; border-radius: 40px; border: 1px solid var(--border); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Student Identity</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Session Timing</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Outcome Status</th>
                    <th style="padding: 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; text-align: right;">Case File</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($past as $a): ?>
                <tr style="border-bottom: 1px solid #f8fafc;">
                    <td style="padding: 2rem;">
                        <div style="font-weight: 800; color: var(--text);"><?php echo htmlspecialchars($a['student_name']); ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600;">ID: <?php echo $a['roll_number']; ?></div>
                    </td>
                    <td style="padding: 2rem;">
                        <div style="font-weight: 700; color: var(--text);"><?php echo date('M d, Y', strtotime($a['scheduled_at'])); ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600;"><?php echo date('g:i A', strtotime($a['scheduled_at'])); ?></div>
                    </td>
                    <td style="padding: 2rem;">
                        <span style="padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; 
                            background: <?php echo $a['status']==='completed' ? '#f5f3ff' : '#f1f5f9'; ?>;
                            color: <?php echo $a['status']==='completed' ? 'var(--primary)' : 'var(--text-dim)'; ?>;">
                            <?php echo $a['status']; ?>
                        </span>
                    </td>
                    <td style="padding: 2rem; text-align: right;">
                        <a href="view_assessment.php?user_id=<?php echo $a['student_id']; ?>" style="text-decoration: none; font-weight: 800; color: var(--primary); font-size: 0.85rem;">REVIEW CASE →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>
<script>
async function apptAction(action, apptId, btn) {
    const card = btn.closest('[id^="appt-card-"]') || btn.closest('div[style*="background: white"]');
    const alertEl = document.getElementById('card-alert-' + apptId);

    const fd = new FormData();
    fd.append('appt_action', action);
    fd.append('appointment_id', apptId);

    // Disable all buttons in this card
    card.querySelectorAll('button').forEach(b => b.disabled = true);

    try {
        const res = await fetch('counselor_appointments.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const data = await res.json();

        if (data.success) {
            const labels = { confirm: 'Confirmed ✅', decline: 'Declined', cancel: 'Cancelled', complete: 'Completed 🎓' };
            const colors = { confirm: '#059669', decline: '#dc2626', cancel: '#94a3b8', complete: '#4f46e5' };
            const bgs    = { confirm: '#ecfdf5', decline: '#fff1f2', cancel: '#f8fafc', complete: '#f5f3ff' };

            // Animate card fade out for terminal statuses
            if (['decline', 'cancel', 'complete'].includes(action)) {
                card.style.transition = 'opacity 0.5s, transform 0.5s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => { card.remove(); }, 500);
            } else {
                // Update status badge inline
                const badge = card.querySelector('[style*="border: 1.5px solid"]');
                if (badge) {
                    badge.textContent = data.newStatus;
                    badge.style.background = bgs[action];
                    badge.style.color = colors[action];
                }
                if (alertEl) {
                    alertEl.style.display = 'block';
                    alertEl.style.background = bgs[action];
                    alertEl.style.color = colors[action];
                    alertEl.textContent = '✨ ' + labels[action];
                }
                card.querySelectorAll('button').forEach(b => b.disabled = false);
            }
        } else {
            showToast(data.error || 'Could not update appointment.', 'error', 'Update Failed');
            card.querySelectorAll('button').forEach(b => b.disabled = false);
        }
    } catch(e) {
        showToast('Connection error. Please refresh the page.', 'error', 'Network Error');
        card.querySelectorAll('button').forEach(b => b.disabled = false);
    }
}
</script>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
