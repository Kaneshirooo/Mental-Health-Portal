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
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'CSRF token validation failed.']);
        exit;
    }
    header('Content-Type: application/json');
    $appt_id = (int)$_POST['appointment_id'];
    $action  = $_POST['appt_action'];
    $msg     = trim($_POST['counselor_message'] ?? '');

    $allowed = ['confirm' => 'confirmed', 'decline' => 'declined', 'cancel' => 'cancelled', 'complete' => 'completed', 'reschedule' => null];
    if (!isset($allowed[$action])) {
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        exit;
    }

    // Reschedule: update scheduled_at only
    if ($action === 'reschedule') {
        $new_datetime = trim($_POST['scheduled_at'] ?? '');
        $new_datetime = str_replace('T', ' ', $new_datetime);
        if (strlen($new_datetime) === 16) $new_datetime .= ':00';
        $dt = $new_datetime ? DateTime::createFromFormat('Y-m-d H:i:s', $new_datetime) : null;
        if (!$dt || $dt < new DateTime()) {
            echo json_encode(['success' => false, 'error' => 'Please choose a valid future date and time.']);
            exit;
        }
        $stmt = $conn->prepare(
            "UPDATE appointments SET scheduled_at = ?, counselor_message = ?, updated_at = NOW()
             WHERE appointment_id = ? AND counselor_id = ? AND status IN ('requested','confirmed')"
        );
        $stmt->bind_param("ssii", $new_datetime, $msg, $appt_id, $counselor_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            logActivity($counselor_id, 'Counselor rescheduled appointment #' . $appt_id . ' to ' . $new_datetime);
            // SQLi FIX: Use prepared statement
            $ai_stmt = $conn->prepare("SELECT a.student_id, u.full_name AS cname FROM appointments a JOIN users u ON a.counselor_id=u.user_id WHERE a.appointment_id = ? LIMIT 1");
            $ai_stmt->bind_param("i", $appt_id);
            $ai_stmt->execute();
            $apptInfo = $ai_stmt->get_result()->fetch_assoc();
            if ($apptInfo) {
                $dateStr = date('F d, Y \a\t g:i A', $dt->getTimestamp());
                createNotification($apptInfo['student_id'], 'Appointment Rescheduled', 'Your session with ' . $apptInfo['cname'] . ' has been moved to ' . $dateStr . '. ' . ($msg ? 'Note: ' . $msg : ''), 'appointment');
            }
            echo json_encode(['success' => true, 'newStatus' => 'confirmed', 'scheduled_at' => $new_datetime]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Could not reschedule. Slot may be taken or appointment not in requested/confirmed status.']);
        }
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
        // SQLi FIX: Use prepared statement
        $ai_stmt2 = $conn->prepare("SELECT a.student_id, a.scheduled_at, u.full_name AS cname FROM appointments a JOIN users u ON a.counselor_id=u.user_id WHERE a.appointment_id = ? LIMIT 1");
        $ai_stmt2->bind_param("i", $appt_id);
        $ai_stmt2->execute();
        $apptInfo = $ai_stmt2->get_result()->fetch_assoc();
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

// Normal POST (non-AJAX fallback) — reschedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appt_action'], $_POST['appointment_id']) && $_POST['appt_action'] === 'reschedule') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed.");
    }
    $appt_id = (int)$_POST['appointment_id'];
    $msg = trim($_POST['counselor_message'] ?? '');
    $new_datetime = trim($_POST['scheduled_at'] ?? '');
    $new_datetime = str_replace('T', ' ', $new_datetime);
    if (strlen($new_datetime) === 16) $new_datetime .= ':00';
    $dt = $new_datetime ? DateTime::createFromFormat('Y-m-d H:i:s', $new_datetime) : null;
    if ($dt && $dt >= new DateTime()) {
        $stmt = $conn->prepare(
            "UPDATE appointments SET scheduled_at = ?, counselor_message = ?, updated_at = NOW()
             WHERE appointment_id = ? AND counselor_id = ? AND status IN ('requested','confirmed')"
        );
        $stmt->bind_param("ssii", $new_datetime, $msg, $appt_id, $counselor_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = 'Appointment rescheduled.';
            queueToast($success, 'success', 'Rescheduled');
            logActivity($counselor_id, 'Counselor rescheduled appointment #' . $appt_id);
        } else {
            $error = 'Could not reschedule.';
            queueToast($error, 'error', 'Reschedule Failed');
        }
    } else {
        queueToast('Please choose a valid future date and time.', 'warning', 'Invalid Time');
    }
}
// Normal POST — confirm/decline/cancel/complete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appt_action'], $_POST['appointment_id'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed.");
    }
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
    <link rel="stylesheet" href="styles.css?v=2.1">
    <?php include 'theme_init.php'; ?>
    <style>
        .appt-pill { font-size:.65rem; padding:.35rem .75rem; border-radius:var(--radius-sm); font-weight:600; letter-spacing:0.04em; text-transform:uppercase; }
        .appt-requested { background:#fffbeb; color:#d97706; border: 1px solid rgba(245, 158, 11, 0.1); }
        .appt-confirmed { background:#f0fdfa; color:#0d9488; border: 1px solid rgba(13, 148, 136, 0.1); }
        .appt-declined, .appt-cancelled { background:#fff1f2; color:#e11d48; border: 1px solid rgba(225, 29, 72, 0.1); }
        .appt-completed { background:var(--surface-2); color:var(--text-muted); border: 1px solid var(--border); }
        
        .card:hover { border-color: var(--primary-light); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content reveal">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Intake & Session Manager</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Manage Schedule</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Active session management. <?php echo count($upcoming); ?> slot<?php echo count($upcoming) !== 1 ? 's' : ''; ?> require attention.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <div style="text-align: right; padding: 0 1.5rem; border-right: 1px solid var(--border);">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary);"><?php echo count($upcoming); ?></div>
                <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em;">Upcoming</div>
            </div>
            <button onclick="window.print()" style="padding: 0.6rem 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); background: var(--surface-solid); color: var(--text); font-weight: 600; cursor: pointer; font-size: 0.85rem;">Print Agenda</button>
        </div>
    </div>



    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);">Intake Queue</h2>
    <?php if (empty($upcoming)): ?>
        <div style="padding: 4rem; text-align: center; background: var(--surface-2); border-radius: var(--radius); border: 1px dashed var(--border);">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">☕</div>
            <h3 style="color: var(--text-muted); font-weight: 700; font-size: 1.1rem;">Queue is clear</h3>
            <p style="color: var(--text-dim); font-size: 0.9rem; font-weight: 400;">No upcoming sessions scheduled at the moment.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 1.25rem;">
            <?php foreach ($upcoming as $a): 
                $st = $a['status'];
                $is_pending = ($st === 'requested');
            ?>
            <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); position: relative; transition: var(--transition);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: var(--surface-2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; border: 1px solid var(--border);">
                            <?php echo strtoupper(substr($a['student_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: var(--text); font-size: 1rem; margin-bottom: 0.15rem;"><?php echo htmlspecialchars($a['student_name']); ?></div>
                            <div style="font-size: 0.72rem; color: var(--text-dim); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;"><?php echo $a['roll_number']; ?> • <?php echo $a['department']; ?></div>
                        </div>
                    </div>
                    <span style="padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; 
                        background: <?php echo $is_pending ? '#fffbeb' : '#f0fdfa'; ?>;
                        color: <?php echo $is_pending ? '#d97706' : '#0d9488'; ?>; border: 1px solid transparent;">
                        <?php echo $st; ?>
                    </span>
                </div>

                <div style="background: var(--surface-2); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <span style="font-size: 1.1rem;">🗓️</span>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--primary);"><?php echo date('M d, Y @ g:i A', strtotime($a['scheduled_at'])); ?></div>
                    </div>
                    <?php if ($a['reason']): ?>
                    <p style="font-size: 0.88rem; line-height: 1.6; color: var(--text-muted); font-weight: 400; font-style: italic; border-left: 2px solid var(--border); padding-left: 1rem;">
                        "<?php echo htmlspecialchars($a['reason']); ?>"
                    </p>
                    <?php endif; ?>
                </div>

                <div id="card-alert-<?php echo $a['appointment_id']; ?>" style="display:none; padding:0.5rem 1rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.75rem; margin-bottom:0.75rem;"></div>
                <div style="display: flex; gap: 0.75rem;">
                    <?php if ($is_pending): ?>
                        <button onclick="apptAction('confirm', <?php echo $a['appointment_id']; ?>, this)" style="flex:1; padding: 0.75rem; border-radius: var(--radius-sm); background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; font-size: 0.85rem;">Confirm Session</button>
                    <?php else: ?>
                        <button onclick="apptAction('complete', <?php echo $a['appointment_id']; ?>, this)" style="flex:1; padding: 0.75rem; border-radius: var(--radius-sm); background: #f0fdfa; color: #0d9488; border: 1px solid rgba(13, 148, 136, 0.2); font-weight: 600; cursor: pointer; font-size: 0.85rem;">Mark Completed</button>
                    <?php endif; ?>
                    
                    <button onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='block'?'none':'block'" style="padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); background: var(--surface-solid); border: 1px solid var(--border); color: var(--text-dim); font-weight: 600; cursor: pointer;">•••</button>
                    <div style="display: none; position: absolute; bottom: 1.5rem; right: 1.5rem; background: var(--surface-solid); border-radius: var(--radius-sm); padding: 0.75rem; box-shadow: var(--shadow-lg); border: 1px solid var(--border); z-index: 10;">
                        <button onclick="openRescheduleModal(<?php echo $a['appointment_id']; ?>, '<?php echo date('Y-m-d\TH:i', strtotime($a['scheduled_at'])); ?>')" style="width: 100%; padding: 0.6rem 1rem; border-radius: 6px; background: #f0fdf4; color: #16a34a; border: none; font-weight: 600; cursor: pointer; margin-bottom:0.4rem; font-size: 0.82rem; text-align: left;">Reschedule</button>
                        <button onclick="apptAction('decline', <?php echo $a['appointment_id']; ?>, this)" style="width: 100%; padding: 0.6rem 1rem; border-radius: 6px; background: #fff1f2; color: #e11d48; border: none; font-weight: 600; cursor: pointer; margin-bottom:0.4rem; font-size: 0.82rem; text-align: left;">Decline</button>
                        <button onclick="apptAction('cancel', <?php echo $a['appointment_id']; ?>, this)" style="width: 100%; padding: 0.6rem 1rem; border-radius: 6px; background: var(--surface-2); color: var(--text-muted); border: none; font-weight: 600; cursor: pointer; font-size: 0.82rem; text-align: left;">Cancel</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-top: 3rem; margin-bottom: 1.5rem; color: var(--text);">Session Archive</h2>
    <div style="background: var(--surface-solid); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Student Identity</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Timing</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                    <th style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($past as $a): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 1.25rem 1.5rem;">
                        <div style="font-weight: 600; color: var(--text); font-size: 0.95rem;"><?php echo htmlspecialchars($a['student_name']); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); font-weight: 500;">ID: <?php echo $a['roll_number']; ?></div>
                    </td>
                    <td style="padding: 1.25rem 1.5rem;">
                        <div style="font-weight: 600; color: var(--text); font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($a['scheduled_at'])); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); font-weight: 500;"><?php echo date('g:i A', strtotime($a['scheduled_at'])); ?></div>
                    </td>
                    <td style="padding: 1.25rem 1.5rem;">
                        <span class="appt-pill appt-<?php echo $a['status']; ?>">
                            <?php echo $a['status']; ?>
                        </span>
                    </td>
                    <td style="padding: 1.25rem 1.5rem; text-align: right;">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reschedule modal -->
<div id="rescheduleModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.4); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background:var(--surface-solid); border-radius:var(--radius); padding:2rem; max-width:400px; width:90%; box-shadow:var(--shadow-lg); border: 1px solid var(--border);">
        <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:1.25rem; color:var(--text);">Reschedule Appointment</h3>
        <form id="rescheduleForm" onsubmit="return submitReschedule(event)">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="appointment_id" id="reschedule_appt_id" value="">
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-weight:600; font-size:0.75rem; color:var(--text-muted); margin-bottom:0.4rem;">New Date & Time</label>
                <input type="datetime-local" id="reschedule_datetime" name="scheduled_at" required style="width:100%; padding:0.75rem; border-radius:var(--radius-sm); border:1.5px solid var(--border); font-weight:500; font-family:inherit;">
            </div>
            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:600; font-size:0.75rem; color:var(--text-muted); margin-bottom:0.4rem;">Note (Optional)</label>
                <textarea name="counselor_message" id="reschedule_message" rows="2" style="width:100%; padding:0.75rem; border-radius:var(--radius-sm); border:1.5px solid var(--border); font-weight:500; font-family:inherit; resize:none;" placeholder="Reason for change..."></textarea>
            </div>
            <div style="display:flex; gap:0.75rem;">
                <button type="button" onclick="closeRescheduleModal()" style="flex:1; padding:0.75rem; border-radius:var(--radius-sm); border:1.5px solid var(--border); background:var(--surface-solid); color: var(--text); font-weight:600; cursor:pointer; font-size:0.85rem;">Cancel</button>
                <button type="submit" style="flex:1; padding:0.75rem; border-radius:var(--radius-sm); background:var(--primary); color:white; border:none; font-weight:600; cursor:pointer; font-size:0.85rem;">Reschedule</button>
            </div>
        </form>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>
<script>
function openRescheduleModal(apptId, currentDatetime) {
    document.getElementById('reschedule_appt_id').value = apptId;
    document.getElementById('reschedule_datetime').value = currentDatetime || '';
    document.getElementById('rescheduleModal').style.display = 'flex';
}
function closeRescheduleModal() {
    document.getElementById('rescheduleModal').style.display = 'none';
}
async function submitReschedule(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('rescheduleForm'));
    fd.append('appt_action', 'reschedule');
    fd.append('appointment_id', fd.get('appointment_id'));
    try {
        const res = await fetch('counselor_appointments.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const data = await res.json();
        if (data.success) {
            closeRescheduleModal();
            window.location.reload();
        } else {
            alert(data.error || 'Could not reschedule.');
        }
    } catch (err) {
        alert('Connection error. Please try again.');
    }
    return false;
}
async function apptAction(action, apptId, btn) {
    const card = btn.closest('[id^="appt-card-"]') || btn.closest('div[style*="background: white"]');
    const alertEl = document.getElementById('card-alert-' + apptId);

    const fd = new FormData();
    fd.append('appt_action', action);
    fd.append('appointment_id', apptId);
    fd.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

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
