<?php
require_once 'config.php';
requireStudent();

$student_id = $_SESSION['user_id'];
$user       = getUserData($student_id);
$name       = htmlspecialchars(explode(' ', $user['full_name'])[0]);

$success = '';
$error   = '';

function findAvailableCounselor(mysqli $conn, string $scheduledAt, int $durationMin = 30, ?int $preferredCounselorId = null): ?int {
    $counselors = [];
    if ($preferredCounselorId) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND user_type IN ('counselor','admin') LIMIT 1");
        $stmt->bind_param("i", $preferredCounselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) $counselors[] = (int)$row['user_id'];
    } else {
        $res = $conn->query("SELECT user_id FROM users WHERE user_type IN ('counselor','admin') ORDER BY created_at ASC");
        while ($r = $res->fetch_assoc()) $counselors[] = (int)$r['user_id'];
    }
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
    $preferred_id     = (int)($_POST['counselor_id'] ?? 0);
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
                $assigned = findAvailableCounselor($conn, $scheduled_at, $duration_min, $preferred_id ?: null);
                if (!$assigned) {
                    $error = 'No counselor is available at that time. Please choose another slot.';
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

// Counselors list
$counselors_res = $conn->query("SELECT user_id, full_name, user_type FROM users WHERE user_type IN ('counselor','admin') ORDER BY user_type DESC, full_name ASC");
$counselors = [];
while ($r = $counselors_res->fetch_assoc()) $counselors[] = $r;

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
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            border-radius: 28px;
            padding: 2.75rem 3rem;
            color: white;
            margin-bottom: 3rem;
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

        .counselor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }

        .counselor-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            border: 2px solid var(--border);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }
        .counselor-card:hover { border-color: var(--primary-light); transform: translateY(-4px); box-shadow: var(--shadow); }
        .counselor-card.selected { border-color: var(--primary); background: #f5f3ff; }
        .counselor-card.selected::after {
            content:'✓';
            position:absolute; top:1rem; right:1rem;
            width:26px; height:26px; border-radius:50%;
            background:var(--primary); color:white;
            display:flex; align-items:center; justify-content:center;
            font-weight:800; font-size:0.8rem;
        }

        .form-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .form-label {
            display: block;
            font-weight: 800;
            font-size: 0.75rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.6rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border-radius: 14px;
            border: 1.5px solid var(--border);
            font-weight: 600;
            font-family: inherit;
            font-size: 0.95rem;
            background: #f8fafc;
            transition: var(--transition);
        }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-glow); background: white; }

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

<div class="container" style="max-width:1200px; padding-top:3rem; padding-bottom:6rem;">

    <!-- Page Header -->
    <div class="page-header">
        <div style="font-size:0.8rem; font-weight:800; text-transform:uppercase; letter-spacing:0.12em; opacity:0.7; margin-bottom:0.75rem;">Counseling Services</div>
        <h1 style="font-family:'Outfit',sans-serif; font-size:2.5rem; font-weight:800; margin-bottom:0.5rem;">Book an Appointment</h1>
        <p style="font-size:1.05rem; opacity:0.85; font-weight:500; max-width:500px;">Schedule a session with a guidance counselor. All appointments are strictly confidential.</p>
    </div>



    <div style="display:grid; grid-template-columns:1fr 400px; gap:3rem; align-items:start;">

        <!-- Left: Booking Form -->
        <div>
            <form method="POST" id="bookingForm">
                <input type="hidden" name="book_appt" value="1">
                <input type="hidden" name="counselor_id" id="selected_counselor" value="">

                <h2 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; color:var(--text); margin-bottom:1.5rem;">1. Choose a Counselor</h2>
                <div class="counselor-grid">
                    <!-- Any Available option -->
                    <div class="counselor-card selected" id="card-0" onclick="selectCounselor(0, this)">
                        <div style="width:48px; height:48px; border-radius:14px; background:var(--primary-glow); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.4rem; margin-bottom:1rem;">🎯</div>
                        <div style="font-weight:800; color:var(--text); margin-bottom:0.25rem;">Any Available</div>
                        <div style="font-size:0.75rem; color:var(--text-dim); font-weight:700; text-transform:uppercase;">Auto-Assign</div>
                        <div style="margin-top:0.75rem;">
                            <span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.3rem 0.75rem; background:#ecfdf5; color:#059669; border-radius:50px; font-size:0.7rem; font-weight:800;">● Available</span>
                        </div>
                    </div>

                    <?php foreach ($counselors as $c):
                        $is_avail = isset($availability[$c['user_id']]);
                    ?>
                    <div class="counselor-card" id="card-<?php echo $c['user_id']; ?>" onclick="selectCounselor(<?php echo $c['user_id']; ?>, this)">
                        <div style="width:48px; height:48px; border-radius:14px; background:#f1f5f9; color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.1rem; margin-bottom:1rem; border:1px solid var(--border);">
                            <?php echo strtoupper(substr($c['full_name'], 0, 1)); ?>
                        </div>
                        <div style="font-weight:800; color:var(--text); margin-bottom:0.25rem;"><?php echo htmlspecialchars($c['full_name']); ?></div>
                        <div style="font-size:0.72rem; color:var(--text-dim); font-weight:700; text-transform:uppercase; letter-spacing:0.04em;">
                            <?php echo $c['user_type'] === 'admin' ? 'Head Counselor' : 'Guidance Counselor'; ?>
                        </div>
                        <div style="margin-top:0.75rem;">
                            <span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.3rem 0.75rem; border-radius:50px; font-size:0.7rem; font-weight:800; <?php echo $is_avail ? 'background:#ecfdf5; color:#059669;' : 'background:#f8fafc; color:var(--text-dim);'; ?>">
                                <?php echo $is_avail ? '● Slots Set' : '○ No Schedule'; ?>
                            </span>
                        </div>
                        <?php if ($is_avail && !empty($availability[$c['user_id']])): ?>
                        <div style="margin-top:0.75rem; display:flex; flex-wrap:wrap; gap:0.4rem;">
                            <?php foreach (array_slice($availability[$c['user_id']], 0, 3) as $slot): ?>
                            <span style="background:#f8fafc; border:1px solid var(--border); padding:0.25rem 0.6rem; border-radius:8px; font-size:0.7rem; font-weight:700; color:var(--text);">
                                <?php echo $days[$slot['day_of_week']]; ?> <?php echo date('g:i A', strtotime($slot['start_time'])); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h2 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; color:var(--text); margin-bottom:1.5rem;">2. Schedule Your Session</h2>
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
                    <button type="submit" style="width:100%; background:var(--primary); color:white; border:none; padding:1.1rem; border-radius:14px; font-weight:800; font-size:1rem; cursor:pointer; box-shadow:0 8px 20px rgba(79,70,229,0.2); transition:var(--transition);">
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

<footer class="footer" style="padding:2.5rem; text-align:center; border-top:1px solid var(--border); margin-top:4rem;">
    <p style="color:var(--text-dim); font-weight:700; font-size:0.85rem;">© <?php echo date('Y'); ?> PSU Mental Health Portal. All appointments are confidential.</p>
</footer>

<script>
function selectCounselor(id, el) {
    document.querySelectorAll('.counselor-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selected_counselor').value = id;
}

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
