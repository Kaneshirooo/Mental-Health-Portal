<?php
require_once 'config.php';
requireStudent();

$student_id = $_SESSION['user_id'];
$user       = getUserData($student_id);
$name       = htmlspecialchars(explode(' ', $user['full_name'])[0]);

$success = '';
$error   = '';
$booking_conflict = false; // true when selected slot conflicts with existing appointments

function findAvailableCounselor(mysqli $conn, string $scheduledAt, int $durationMin = 30): ?int {
    $counselors = [];
    $res = $conn->query("SELECT user_id FROM users WHERE user_type IN ('counselor','admin') ORDER BY created_at ASC");
    while ($r = $res->fetch_assoc()) $counselors[] = (int)$r['user_id'];
    
    if (empty($counselors)) return null;
    foreach ($counselors as $cid) {
        $sql = "SELECT appointment_id FROM appointments
                WHERE counselor_id = ? AND status IN ('requested','confirmed')
                  AND scheduled_at < DATE_ADD(?, INTERVAL ? MINUTE)
                  AND DATE_ADD(scheduled_at, INTERVAL duration_min MINUTE) > ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $cid, $scheduledAt, $durationMin, $scheduledAt);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) return $cid;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appt'])) {
    $scheduled_at_raw = trim($_POST['scheduled_at'] ?? '');
    $reason           = trim($_POST['reason'] ?? '');
    $duration_min     = 30;

    if ($scheduled_at_raw === '') {
        $error = 'Please choose a date and time.';
        queueToast($error, 'warning', 'Missing Field');
    } else {
        $scheduled_at = str_replace('T', ' ', $scheduled_at_raw);
        if (strlen($scheduled_at) === 16) $scheduled_at .= ':00';
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $scheduled_at);
        if (!$dt) {
            $error = 'Invalid date/time format.';
        } else {
            $now = new DateTime();
            $cutoff = (clone $now)->modify('+10 minutes'); // clone to avoid mutating $now
            if ($dt < $cutoff) {
                $error = 'Please select a time at least 10 minutes from now.';
            } else {
                $assigned = findAvailableCounselor($conn, $scheduled_at, $duration_min);
                if (!$assigned) {
                    $error = 'No counselor is available at that time. Please choose another slot.';
                    $booking_conflict = true;
                    queueToast($error, 'warning', 'Schedule conflict');
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO appointments (student_id, counselor_id, scheduled_at, duration_min, status, reason)
                         VALUES (?, ?, ?, ?, 'requested', ?)"
                    );
                    $stmt->bind_param("iisis", $student_id, $assigned, $scheduled_at, $duration_min, $reason);
                    if ($stmt->execute()) {
                        // Notify the assigned counselor
                        $counselorData = getUserData($assigned);
                        createNotification(
                            $assigned,
                            'New Appointment Request',
                            $user['full_name'] . ' has requested an appointment on ' . date('F d, Y \a\t g:i A', strtotime($scheduled_at)) . '.',
                            'appointment'
                        );
                        $success = 'Appointment requested! Your counselor will confirm it soon.';
                        queueToast($success, 'success', 'Appointment Requested');
                        logActivity($student_id, 'Student requested counselor appointment');
                    } else {
                        $error = 'Failed to request appointment. Please try again.';
                        queueToast($error, 'error', 'Booking Failed');
                    }
                }
            }
        }
    }
}

// Fetch this student's appointments
$up_stmt = $conn->prepare(
    "SELECT a.*, u.full_name AS counselor_name
     FROM appointments a
     JOIN users u ON a.counselor_id = u.user_id
     WHERE a.student_id = ?
     ORDER BY a.scheduled_at DESC
     LIMIT 20"
);
$up_stmt->bind_param("i", $student_id);
$up_stmt->execute();
$appointments = $up_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// (Counselors list removed for auto-assignment)

// Availability per counselor
$availability = [];
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$avail_res = $conn->query("SELECT ca.counselor_id, ca.day_of_week, ca.start_time, ca.end_time
     FROM counselor_availability ca WHERE ca.is_active = 1
     ORDER BY ca.counselor_id, ca.day_of_week, ca.start_time");
if ($avail_res) {
    while ($av = $avail_res->fetch_assoc()) $availability[$av['counselor_id']][] = $av;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            border-radius: var(--radius);
            padding: 2rem 2.5rem;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .page-header::after {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }


        .form-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.85rem 1.25rem;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            font-weight: 500;
            font-family: inherit;
            font-size: 0.95rem;
            background: var(--surface-2);
            transition: var(--transition);
        }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(13,148,136,0.06); background: white; }

        .appt-status {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.35rem 0.9rem; border-radius: 50px;
            font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
        }
        .status-requested  { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
        .status-confirmed  { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
        .status-completed  { background:#f5f3ff; color:#4f46e5; border:1px solid #c4b5fd; }
        .status-declined, .status-cancelled { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }

        .timeline-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem 2rem;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: var(--transition);
        }
        .timeline-card:hover { border-color: var(--primary-light); box-shadow: var(--shadow-sm); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width:1100px; padding-top:1.5rem; padding-bottom:3rem;">

    <!-- Page Header -->
    <div class="page-header">
        <div style="font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; opacity:0.8; margin-bottom:0.5rem;">Counseling Services</div>
        <h1 style="font-family:'Outfit',sans-serif; font-size:1.75rem; font-weight:700; margin-bottom:0.35rem;">Book an Appointment</h1>
        <p style="font-size:0.95rem; opacity:0.85; font-weight:400; max-width:400px;">Schedule a session with a guidance counselor. All appointments are confidential.</p>
    </div>



    <div style="display:grid; grid-template-columns:1fr 400px; gap:3rem; align-items:start;">

        <!-- Left: Booking Form -->
        <div>
            <form method="POST" id="bookingForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="book_appt" value="1">

                <h2 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; color:var(--text); margin-bottom:1.5rem;">Schedule Your Session</h2>
                <div class="form-card">
                    <div style="margin-bottom:1.5rem;">
                        <label class="form-label">Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduledAtInput" class="form-input" required>
                        <div style="font-size:0.78rem; color:var(--text-dim); margin-top:0.5rem; font-weight:600;">Sessions are 30 minutes long. Please book at least 10 minutes in advance.</div>
                    </div>
                    <div style="margin-bottom:2rem;">
                        <label class="form-label">Reason for Visit <span style="opacity:0.5;">(Optional)</span></label>
                        <textarea name="reason" class="form-input" rows="4" style="resize:none;" placeholder="Briefly describe what you'd like to discuss..."></textarea>
                    </div>
                    <button type="submit" style="width:100%; background:var(--primary); color:white; border:none; padding:0.85rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.9rem; cursor:pointer; box-shadow:0 4px 12px rgba(13,148,136,0.2); transition:var(--transition);">
                        Request Appointment →
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: My Appointments -->
        <div>
            <h2 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; color:var(--text); margin-bottom:1.5rem;">My Appointments</h2>
            <?php if (empty($appointments)): ?>
            <div style="text-align:center; padding:3rem 2rem; background:white; border-radius:24px; border:2px dashed var(--border);">
                <div style="font-size:2.5rem; margin-bottom:1rem; opacity:0.4;">📅</div>
                <p style="font-weight:700; color:var(--text-dim); font-size:0.95rem;">No appointments yet.</p>
                <p style="font-size:0.85rem; color:var(--text-dim); margin-top:0.4rem;">Book your first session using the form.</p>
            </div>
            <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <?php foreach ($appointments as $a):
                    $st = $a['status'];
                    $badgeCls = 'status-' . $st;
                    $dtObj = DateTime::createFromFormat('Y-m-d H:i:s', $a['scheduled_at']);
                    $statusIcon = ['requested' => '⏳', 'confirmed' => '✅', 'completed' => '🎓', 'declined' => '❌', 'cancelled' => '🚫'][$st] ?? '📅';
                ?>
                <div class="timeline-card">
                    <div style="width:44px; height:44px; border-radius:14px; background:var(--primary-glow); display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                        <?php echo $statusIcon; ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.25rem;">
                            <div style="font-weight:800; color:var(--text); font-size:0.92rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($a['counselor_name']); ?></div>
                            <span class="appt-status <?php echo $badgeCls; ?>"><?php echo $st; ?></span>
                        </div>
                        <div style="font-size:0.8rem; color:var(--primary); font-weight:700;">
                            <?php echo $dtObj ? $dtObj->format('M d, Y • g:i A') : $a['scheduled_at']; ?>
                        </div>
                        <?php if ($a['reason']): ?>
                        <div style="font-size:0.78rem; color:var(--text-dim); margin-top:0.4rem; font-style:italic; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            "<?php echo htmlspecialchars($a['reason']); ?>"
                        </div>
                        <?php endif; ?>
                        <?php if ($a['counselor_message']): ?>
                        <div style="margin-top:0.6rem; background:#f0fdf4; border-left:3px solid #10b981; padding:0.5rem 0.75rem; border-radius:0 8px 8px 0; font-size:0.78rem; color:#065f46; font-weight:600;">
                            Counselor: "<?php echo htmlspecialchars($a['counselor_message']); ?>"
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Schedule conflict popup (shown when selected slot conflicts with existing appointments) -->
<div id="conflictModal" class="conflict-popup-overlay" style="display: <?php echo $booking_conflict ? 'flex' : 'none'; ?>;">
    <div class="conflict-popup-box" onclick="event.stopPropagation()">
        <div class="conflict-popup-icon">⚠️</div>
        <h3 class="conflict-popup-title">Schedule conflict</h3>
        <p class="conflict-popup-message">The date and time you selected conflicts with another appointment. No counselor is available at that slot. Please choose a different date or time.</p>
        <button type="button" class="conflict-popup-btn" onclick="document.getElementById('conflictModal').style.display='none'">Choose another time</button>
    </div>
</div>
<style>
.conflict-popup-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    padding: 1rem;
}
.conflict-popup-box {
    background: white; border-radius: 24px; padding: 2.5rem; max-width: 400px; width: 100%;
    box-shadow: 0 25px 50px rgba(0,0,0,0.15); border: 1px solid var(--border);
    text-align: center;
}
.conflict-popup-icon { font-size: 3rem; margin-bottom: 1rem; }
.conflict-popup-title {
    font-family: 'Outfit', sans-serif; font-size: 1.35rem; font-weight: 800; color: var(--primary-dark);
    margin-bottom: 0.75rem;
}
.conflict-popup-message {
    color: var(--text-dim); font-size: 0.95rem; line-height: 1.5; font-weight: 600; margin-bottom: 1.5rem;
}
.conflict-popup-btn {
    width: 100%; padding: 1rem 1.5rem; border-radius: 14px; border: none;
    background: var(--primary); color: white; font-weight: 800; font-size: 1rem; cursor: pointer;
    transition: var(--transition);
}
.conflict-popup-btn:hover { filter: brightness(1.05); transform: translateY(-1px); }
</style>
<script>
(function() {
    var m = document.getElementById('conflictModal');
    if (m && m.style.display === 'flex') {
        m.onclick = function(e) { if (e.target === m) m.style.display = 'none'; };
    }
})();
</script>

<footer class="footer" style="padding:2.5rem; text-align:center; border-top:1px solid var(--border); margin-top:4rem;">
    <p style="color:var(--text-dim); font-weight:700; font-size:0.85rem;">© <?php echo date('Y'); ?> PSU Mental Health Portal. All appointments are confidential.</p>
</footer>

<script>

// Set min datetime to now+10min so past times can't be picked in the browser
(function() {
    const input = document.getElementById('scheduledAtInput');
    if (!input) return;
    const pad = n => String(n).padStart(2, '0');
    const d = new Date(Date.now() + 10 * 60 * 1000);
    input.min = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate())
              + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
})();
</script>
</body>
<?php include 'toast.php'; ?>
</html>
