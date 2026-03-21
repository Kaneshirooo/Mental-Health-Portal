<?php
require_once 'config.php';
requireAdmin();

$admin_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// ── Override / Reschedule / Force status (any appointment) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appt_id = (int)($_POST['appointment_id'] ?? 0);
    if ($appt_id < 1) {
        $error = 'Invalid appointment.';
    } else {
        $override_action = $_POST['override_action'] ?? '';
        if ($override_action === 'reassign') {
            $new_counselor_id = (int)($_POST['counselor_id'] ?? 0);
            if ($new_counselor_id < 1) {
                $error = 'Select a counselor.';
            } else {
                $chk = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND user_type IN ('counselor','admin')");
                $chk->bind_param("i", $new_counselor_id);
                $chk->execute();
                if ($chk->get_result()->num_rows > 0) {
                    $stmt = $conn->prepare("UPDATE appointments SET counselor_id = ?, updated_at = NOW() WHERE appointment_id = ?");
                    $stmt->bind_param("ii", $new_counselor_id, $appt_id);
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $success = 'Appointment reassigned.';
                        queueToast($success, 'success', 'Override');
                        logActivity($admin_id, 'Head counselor reassigned appointment #' . $appt_id . ' to counselor ' . $new_counselor_id);
                    } else {
                        $error = 'Could not reassign.';
                    }
                } else {
                    $error = 'Invalid counselor.';
                }
            }
        } elseif ($override_action === 'reschedule') {
            $new_datetime = trim($_POST['scheduled_at'] ?? '');
            $new_datetime = str_replace('T', ' ', $new_datetime);
            if (strlen($new_datetime) === 16) $new_datetime .= ':00';
            $dt = $new_datetime ? DateTime::createFromFormat('Y-m-d H:i:s', $new_datetime) : null;
            if (!$dt || $dt < new DateTime()) {
                $error = 'Choose a valid future date and time.';
            } else {
                $stmt = $conn->prepare("UPDATE appointments SET scheduled_at = ?, updated_at = NOW() WHERE appointment_id = ?");
                $stmt->bind_param("si", $new_datetime, $appt_id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
        // SQLi FIX: Use prepared statements
        $appt_stmt = $conn->prepare("SELECT student_id, counselor_id FROM appointments WHERE appointment_id = ? LIMIT 1");
        $appt_stmt->bind_param("i", $appt_id);
        $appt_stmt->execute();
        $row = $appt_stmt->get_result()->fetch_assoc();

        if ($row) {
            $c_stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $c_stmt->bind_param("i", $row['counselor_id']);
            $c_stmt->execute();
            $cname = $c_stmt->get_result()->fetch_row()[0] ?? 'Counselor';
                        $dateStr = $dt->format('F d, Y \a\t g:i A');
                        createNotification((int)$row['student_id'], 'Appointment Rescheduled', 'Your session has been moved to ' . $dateStr . ' with ' . $cname . '.', 'appointment');
                    }
                    $success = 'Appointment rescheduled.';
                    queueToast($success, 'success', 'Override');
                    logActivity($admin_id, 'Head counselor rescheduled appointment #' . $appt_id);
                } else {
                    $error = 'Could not reschedule.';
                }
            }
        } elseif ($override_action === 'force_status') {
            $new_status = $_POST['status'] ?? '';
            $allowed = ['requested', 'confirmed', 'declined', 'cancelled', 'completed'];
            if (!in_array($new_status, $allowed, true)) {
                $error = 'Invalid status.';
            } else {
                $msg = trim($_POST['counselor_message'] ?? '');
                $stmt = $conn->prepare("UPDATE appointments SET status = ?, counselor_message = ?, updated_at = NOW() WHERE appointment_id = ?");
                $stmt->bind_param("ssi", $new_status, $msg, $appt_id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $success = 'Status updated to ' . $new_status . '.';
                    queueToast($success, 'success', 'Override');
                    logActivity($admin_id, 'Head counselor set appointment #' . $appt_id . ' to ' . $new_status);
                } else {
                    $error = 'Could not update status.';
                }
            }
        } elseif ($override_action === 'set_priority') {
            $is_priority = isset($_POST['is_priority']) && $_POST['is_priority'] ? 1 : 0;
            $stmt = $conn->prepare("UPDATE appointments SET is_priority = ?, updated_at = NOW() WHERE appointment_id = ?");
            $stmt->bind_param("ii", $is_priority, $appt_id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $success = $is_priority ? 'Marked as priority case.' : 'Priority flag removed.';
                queueToast($success, 'success', 'Priority');
                logActivity($admin_id, 'Head counselor ' . ($is_priority ? 'set' : 'cleared') . ' priority for appointment #' . $appt_id);
            } else {
                $error = 'Could not update priority.';
            }
        }
    }
    if ($error) queueToast($error, 'error', 'Error');
}

// Filters
$filter_status = $_GET['status'] ?? '';
$filter_priority = isset($_GET['priority']) && $_GET['priority'] === '1';
$filter_counselor = (int)($_GET['counselor_id'] ?? 0);
$tab = $_GET['tab'] ?? 'appointments'; // appointments | schedules | priority

// All appointments (for override list)
$sql = "SELECT a.*, u.full_name AS student_name, u.roll_number, u.department, u.email,
        c.full_name AS counselor_name
        FROM appointments a
        JOIN users u ON a.student_id = u.user_id
        JOIN users c ON a.counselor_id = c.user_id
        WHERE 1=1";
$params = [];
$types = '';
if ($filter_status !== '') {
    $sql .= " AND a.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if ($filter_priority) {
    $sql .= " AND a.is_priority = 1";
}
if ($filter_counselor > 0) {
    $sql .= " AND a.counselor_id = ?";
    $params[] = $filter_counselor;
    $types .= 'i';
}
$sql .= " ORDER BY a.scheduled_at DESC LIMIT 200";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$all_appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Priority-only list (for Priority Cases tab)
$priority_stmt = $conn->prepare(
    "SELECT a.*, u.full_name AS student_name, u.roll_number, u.department, c.full_name AS counselor_name
     FROM appointments a
     JOIN users u ON a.student_id = u.user_id
     JOIN users c ON a.counselor_id = c.user_id
     WHERE a.is_priority = 1 AND a.status IN ('requested','confirmed')
     ORDER BY a.scheduled_at ASC"
);
$priority_stmt->execute();
$priority_cases = $priority_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// All counselors (for reassign dropdown + schedules)
$counselors_list = $conn->query(
    "SELECT user_id, full_name, user_type FROM users WHERE user_type IN ('counselor','admin') ORDER BY user_type DESC, full_name ASC"
)->fetch_all(MYSQLI_ASSOC);

// All schedules: per-counselor availability + upcoming appointments
$schedules = [];
foreach ($counselors_list as $c) {
    $cid = (int)$c['user_id'];
    $avail = $conn->query(
        "SELECT day_of_week, start_time, end_time FROM counselor_availability WHERE counselor_id = $cid AND is_active = 1 ORDER BY day_of_week, start_time"
    )->fetch_all(MYSQLI_ASSOC);
    $upcoming = $conn->query(
        "SELECT a.appointment_id, a.scheduled_at, a.status, a.is_priority, u.full_name AS student_name
         FROM appointments a JOIN users u ON a.student_id = u.user_id
         WHERE a.counselor_id = $cid AND a.status IN ('requested','confirmed') AND a.scheduled_at >= NOW()
         ORDER BY a.scheduled_at ASC LIMIT 20"
    )->fetch_all(MYSQLI_ASSOC);
    $schedules[$cid] = [
        'name' => $c['full_name'],
        'type' => $c['user_type'],
        'availability' => $avail,
        'upcoming' => $upcoming,
    ];
}

$days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Schedules & Override — Head Counselor</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .tab-link { 
            padding: 0.65rem 1.25rem; 
            border-radius: var(--radius-sm); 
            border: 1.5px solid var(--border); 
            background: white; 
            font-weight: 600; 
            cursor: pointer; 
            text-decoration: none; 
            color: var(--text-muted); 
            transition: var(--transition); 
            font-size: 0.85rem;
        }
        .tab-link:hover { border-color: var(--primary); color: var(--primary); background: #f0fdfa; }
        .tab-link.active { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15); }
        
        .pill { font-size: 0.65rem; padding: 0.3rem 0.75rem; border-radius: 4px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
        .pill-requested { background: #fffbeb; color: #d97706; }
        .pill-confirmed { background: #f0fdfa; color: #0d9488; }
        .pill-declined, .pill-cancelled { background: #fff1f2; color: #e11d48; }
        .pill-completed { background: var(--surface-2); color: var(--text-muted); border: 1px solid var(--border); }
        .pill-priority { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Governance & Override</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Clinical Master Schedule</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Global visibility of institutional appointments and clinical capacity.</p>
        </div>
        <div style="padding: 0.5rem 1rem; border-radius: 6px; background: #fffbeb; color: #b45309; font-weight: 700; font-size: 0.7rem; border: 1px solid #fde68a; text-transform: uppercase; letter-spacing: 0.04em;">Override Mode Active</div>
    </div>

    <!-- Tabs -->
    <div style="display: flex; gap: 0.75rem; margin-bottom: 2.5rem;">
        <a href="?tab=appointments<?php echo $filter_status ? '&status=' . urlencode($filter_status) : ''; ?><?php echo $filter_priority ? '&priority=1' : ''; ?><?php echo $filter_counselor ? '&counselor_id=' . $filter_counselor : ''; ?>" class="tab-link <?php echo $tab === 'appointments' ? 'active' : ''; ?>">All Appointments</a>
        <a href="?tab=priority" class="tab-link <?php echo $tab === 'priority' ? 'active' : ''; ?>">Priority Cases (<?php echo count($priority_cases); ?>)</a>
        <a href="?tab=schedules" class="tab-link <?php echo $tab === 'schedules' ? 'active' : ''; ?>">View All Schedules</a>
    </div>

    <?php if ($tab === 'appointments' || $tab === 'priority'): ?>
        <?php
        $list = $tab === 'priority' ? $priority_cases : $all_appointments;
        ?>
        <!-- Filters (appointments tab only) -->
        <?php if ($tab === 'appointments'): ?>
        <form method="GET" style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1.5rem;">
            <input type="hidden" name="tab" value="appointments">
            <select name="status" onchange="this.form.submit()" style="padding: 0.65rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 600; font-size: 0.85rem; color: var(--text-muted);">
                <option value="">All Statuses</option>
                <option value="requested" <?php echo $filter_status === 'requested' ? 'selected' : ''; ?>>Requested</option>
                <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="declined" <?php echo $filter_status === 'declined' ? 'selected' : ''; ?>>Declined</option>
                <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
            <select name="counselor_id" onchange="this.form.submit()" style="padding: 0.65rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 600; font-size: 0.85rem; color: var(--text-muted);">
                <option value="">All Counselors</option>
                <?php foreach ($counselors_list as $c): ?>
                <option value="<?php echo $c['user_id']; ?>" <?php echo $filter_counselor === (int)$c['user_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.85rem; color: var(--text-muted); margin-left: 0.5rem;">
                <input type="checkbox" name="priority" value="1" <?php echo $filter_priority ? 'checked' : ''; ?> onchange="this.form.submit()"> Priority Only
            </label>
        </form>
        <?php endif; ?>

        <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                        <th style="padding: 1rem 1.25rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">Student / Counselor</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">Scheduled</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">Status</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $a): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 1rem 1.25rem;">
                        <div style="font-weight: 700; color: var(--text); font-size: 0.95rem; margin-bottom: 0.15rem;"><?php echo htmlspecialchars($a['student_name']); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em;"><?php echo htmlspecialchars($a['counselor_name']); ?></div>
                        <?php if (!empty($a['is_priority'])): ?><span class="pill pill-priority" style="margin-top:0.4rem; display:inline-block;">Priority Case</span><?php endif; ?>
                    </td>
                    <td style="padding: 1rem 1.25rem; font-weight: 600; color: var(--text); font-size: 0.88rem;">
                        <div><?php echo date('M d, Y', strtotime($a['scheduled_at'])); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;"><?php echo date('g:i A', strtotime($a['scheduled_at'])); ?></div>
                    </td>
                    <td style="padding: 1rem 1.25rem;"><span class="pill pill-<?php echo $a['status']; ?>"><?php echo $a['status']; ?></span></td>
                    <td style="padding: 1rem 1.25rem; text-align: right;">
                        <button type="button" onclick="document.getElementById('override_<?php echo $a['appointment_id']; ?>').style.display='flex'" style="padding: 0.45rem 1rem; border-radius: 6px; border: 1.5px solid var(--border); background: white; color: var(--text); font-weight: 600; font-size: 0.78rem; cursor: pointer; transition: var(--transition);">Override</button>
                        <div id="override_<?php echo $a['appointment_id']; ?>" style="display:none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); align-items: center; justify-content: center;" onclick="if(event.target===this) this.style.display='none'">
                            <div style="background: white; border-radius: var(--radius); padding: 2rem; max-width: 400px; width: 90%; box-shadow: var(--shadow-lg); text-align: left; border: 1px solid var(--border);" onclick="event.stopPropagation()">
                                <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 1.5rem;">Clinical Override #<?php echo $a['appointment_id']; ?></h4>
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <form method="POST" style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $a['appointment_id']; ?>">
                                        <input type="hidden" name="override_action" value="reassign">
                                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim);">Reassign counselor</label>
                                        <select name="counselor_id" style="width: 100%; padding: 0.6rem; border-radius: 10px; border: 1px solid var(--border);">
                                            <?php foreach ($counselors_list as $c): ?>
                                            <option value="<?php echo $c['user_id']; ?>" <?php echo (int)$c['user_id'] === (int)$a['counselor_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['full_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" style="margin-top: 0.25rem; padding: 0.5rem 1rem; border-radius: 8px; background: #eff6ff; color: #2563eb; border: none; font-weight: 800; font-size: 0.8rem; cursor: pointer;">Reassign</button>
                                    </form>
                                    <form method="POST" style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $a['appointment_id']; ?>">
                                        <input type="hidden" name="override_action" value="reschedule">
                                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim);">Reschedule</label>
                                        <input type="datetime-local" name="scheduled_at" value="<?php echo date('Y-m-d\TH:i', strtotime($a['scheduled_at'])); ?>" style="width: 100%; padding: 0.6rem; border-radius: 10px; border: 1px solid var(--border);">
                                        <button type="submit" style="margin-top: 0.25rem; padding: 0.5rem 1rem; border-radius: 8px; background: #ecfdf5; color: #059669; border: none; font-weight: 800; font-size: 0.8rem; cursor: pointer;">Reschedule</button>
                                    </form>
                                    <form method="POST" style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $a['appointment_id']; ?>">
                                        <input type="hidden" name="override_action" value="force_status">
                                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim);">Force status</label>
                                        <select name="status" style="width: 100%; padding: 0.6rem; border-radius: 10px; border: 1px solid var(--border);">
                                            <option value="requested" <?php echo $a['status'] === 'requested' ? 'selected' : ''; ?>>Requested</option>
                                            <option value="confirmed" <?php echo $a['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="declined" <?php echo $a['status'] === 'declined' ? 'selected' : ''; ?>>Declined</option>
                                            <option value="cancelled" <?php echo $a['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="completed" <?php echo $a['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                        <input type="text" name="counselor_message" placeholder="Optional message" style="width: 100%; padding: 0.6rem; border-radius: 10px; border: 1px solid var(--border);">
                                        <button type="submit" style="margin-top: 0.25rem; padding: 0.5rem 1rem; border-radius: 8px; background: #f5f3ff; color: var(--primary); border: none; font-weight: 800; font-size: 0.8rem; cursor: pointer;">Set status</button>
                                    </form>
                                    <form method="POST" style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $a['appointment_id']; ?>">
                                        <input type="hidden" name="override_action" value="set_priority">
                                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim);">Priority case</label>
                                        <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="is_priority" value="1" <?php echo !empty($a['is_priority']) ? 'checked' : ''; ?>> Mark as priority</label>
                                        <button type="submit" style="margin-top: 0.25rem; padding: 0.5rem 1rem; border-radius: 8px; background: #fef3c7; color: #b45309; border: none; font-weight: 800; font-size: 0.8rem; cursor: pointer;">Update priority</button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('override_<?php echo $a['appointment_id']; ?>').style.display='none'" style="padding: 0.75rem; border-radius: 10px; border: 1px solid var(--border); background: #f8fafc; font-weight: 800; cursor: pointer;">Close</button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($list)): ?>
            <div style="padding: 4rem; text-align: center; color: var(--text-dim); font-weight: 700;">No appointments match the filters.</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($tab === 'schedules'): ?>
        <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text);">All counselors — availability & upcoming</h2>
        <div style="display: grid; gap: 2rem;">
            <?php foreach ($schedules as $cid => $data): ?>
            <div style="background: white; border-radius: 24px; padding: 2rem; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                        <?php echo strtoupper(substr($data['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 800; color: var(--text); font-size: 1.1rem;"><?php echo htmlspecialchars($data['name']); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); font-weight: 700; text-transform: uppercase;"><?php echo $data['type'] === 'admin' ? 'Head Counselor' : 'Counselor'; ?></div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem;">Availability</div>
                        <?php if (empty($data['availability'])): ?>
                        <p style="color: var(--text-dim); font-size: 0.9rem;">No slots set.</p>
                        <?php else: ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <?php foreach ($data['availability'] as $av): ?>
                            <span style="background: #f8fafc; border: 1px solid var(--border); padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700;"><?php echo $days[$av['day_of_week']]; ?> <?php echo date('g:i A', strtotime($av['start_time'])); ?>–<?php echo date('g:i A', strtotime($av['end_time'])); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem;">Upcoming (<?php echo count($data['upcoming']); ?>)</div>
                        <?php if (empty($data['upcoming'])): ?>
                        <p style="color: var(--text-dim); font-size: 0.9rem;">No upcoming sessions.</p>
                        <?php else: ?>
                        <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem;">
                            <?php foreach (array_slice($data['upcoming'], 0, 8) as $u): ?>
                            <li style="margin-bottom: 0.35rem;"><?php echo date('M d g:i A', strtotime($u['scheduled_at'])); ?> — <?php echo htmlspecialchars($u['student_name']); ?> <?php echo !empty($u['is_priority']) ? ' <span class="pill pill-priority">P</span>' : ''; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
