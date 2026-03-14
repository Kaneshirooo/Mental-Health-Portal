<?php
require_once 'config.php';
requireLogin();
// Allow both counselors AND the head counselor (admin) to manage their own availability
if (!isCounselor() && !isAdmin()) {
    redirect('unauthorized.php');
}

$counselor_id = $_SESSION['user_id'];
$success = '';
$error   = '';

$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

// ── Save availability slots ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_availability'])) {
    // Delete existing slots for this counselor
    $del = $conn->prepare("DELETE FROM counselor_availability WHERE counselor_id = ?");
    $del->bind_param("i", $counselor_id);
    $del->execute();

    $inserted = 0;
    if (!empty($_POST['slots']) && is_array($_POST['slots'])) {
        $ins = $conn->prepare(
            "INSERT INTO counselor_availability (counselor_id, day_of_week, start_time, end_time, is_active)
             VALUES (?, ?, ?, ?, 1)"
        );
        foreach ($_POST['slots'] as $slot) {
            $dow   = intval($slot['day'] ?? -1);
            $start = trim($slot['start'] ?? '');
            $end   = trim($slot['end']   ?? '');
            if ($dow < 0 || $dow > 6 || !$start || !$end || $start >= $end) continue;
            $ins->bind_param("iiss", $counselor_id, $dow, $start, $end);
            $ins->execute();
            $inserted++;
        }
    }
    $success = $inserted > 0
        ? "$inserted availability slot(s) saved successfully."
        : 'Availability cleared (no valid slots provided).';
    queueToast($success, $inserted > 0 ? 'success' : 'warning', 'Availability Updated');
    logActivity($counselor_id, 'Counselor updated availability slots');
}

// ── Load existing slots ────────────────────────────────
$avail_res = $conn->prepare(
    "SELECT * FROM counselor_availability WHERE counselor_id = ? AND is_active = 1 ORDER BY day_of_week, start_time ASC"
);
$avail_res->bind_param("i", $counselor_id);
$avail_res->execute();
$slots = $avail_res->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Availability — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .slot-row {
            display: grid;
            grid-template-columns: 180px 1fr 1fr auto;
            gap: 1.5rem;
            align-items: center;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 20px;
            margin-bottom: 1rem;
            transition: var(--transition);
            border: 1px solid transparent;
        }
        .slot-row:hover { background: white; border-color: var(--primary-light); box-shadow: var(--shadow-sm); }
        
        .add-slot-btn {
            width: 100%;
            background: white;
            border: 2px dashed var(--border);
            color: var(--primary);
            border-radius: 20px;
            padding: 1.25rem;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        .add-slot-btn:hover { border-color: var(--primary); background: var(--primary-glow); transform: translateY(-2px); }
        
        .day-chip {
            background: var(--primary);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem;
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            margin-bottom: 0.75rem;
            transition: var(--transition);
        }
        .schedule-item:hover { transform: translateX(5px); border-color: var(--primary-light); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1000px; padding-top: 3rem; padding-bottom: 6rem;">
    <div style="margin-bottom: 4rem;">
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.5rem;">Availability Studio</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 600;">Define your active clinical hours for student discovery and booking.</p>
    </div>



    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 3rem; align-items: start;">
        
        <!-- Current Schedule -->
        <div>
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 2rem;">Active Schedule</h2>
            <?php if (empty($slots)): ?>
                <div style="padding: 2.5rem; background: #f8fafc; border-radius: 24px; text-align: center; border: 2px dashed var(--border);">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">⏳</div>
                    <p style="font-weight: 700; color: var(--text-dim); font-size: 0.85rem;">No active hours defined yet.</p>
                </div>
            <?php else: ?>
                <div class="current-slots">
                    <?php foreach ($slots as $s): ?>
                    <div class="schedule-item">
                        <span class="day-chip"><?php echo $days[$s['day_of_week']]; ?></span>
                        <span style="font-weight: 800; color: var(--text); font-size: 0.9rem;">
                            <?php echo date('g:i A', strtotime($s['start_time'])); ?> - <?php echo date('g:i A', strtotime($s['end_time'])); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 2rem; padding: 1.5rem; background: var(--primary-glow); border-radius: 20px; border: 1px solid var(--primary-light);">
                <div style="font-weight: 800; color: var(--primary); margin-bottom: 0.5rem; font-size: 0.85rem;">Clinical Tip</div>
                <p style="font-size: 0.8rem; color: var(--primary-dark); line-height: 1.5; font-weight: 500;">Consistency in your availability helps students build trust and ensures a stable support structure.</p>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card" style="padding: 3rem; border-radius: 32px;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 2rem;">Modify Hours</h2>
            <form method="POST" id="availForm">
                <input type="hidden" name="save_availability" value="1">
                <div id="slotsContainer">
                    <?php foreach ($slots as $idx => $s): ?>
                    <div class="slot-row" id="slot-<?php echo $idx; ?>">
                        <select name="slots[<?php echo $idx; ?>][day]" style="padding: 0.75rem; border-radius: 12px; border: 1.5px solid var(--border); font-weight: 700;">
                            <?php foreach ($days as $i => $d): ?>
                                <option value="<?php echo $i; ?>" <?php echo $s['day_of_week'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $d; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-group" style="margin:0">
                            <label style="font-weight: 800; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase;">From</label>
                            <input type="time" name="slots[<?php echo $idx; ?>][start]" value="<?php echo htmlspecialchars($s['start_time']); ?>" style="padding: 0.75rem; border-radius: 12px; border: 1.5px solid var(--border); font-weight: 700;" required>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label style="font-weight: 800; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase;">To</label>
                            <input type="time" name="slots[<?php echo $idx; ?>][end]" value="<?php echo htmlspecialchars($s['end_time']); ?>" style="padding: 0.75rem; border-radius: 12px; border: 1.5px solid var(--border); font-weight: 700;" required>
                        </div>
                        <button type="button" onclick="removeSlot('slot-<?php echo $idx; ?>')" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: none; width: 35px; height: 35px; border-radius: 10px; font-weight: 800; cursor: pointer;">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="add-slot-btn" onclick="addSlot()">+ Add Another Slot</button>

                <div style="margin-top: 3rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn-primary" style="flex: 2; padding: 1rem; border-radius: 16px; font-weight: 800; background: var(--primary); border: none;">COMMIT CHANGES</button>
                    <a href="counselor_dashboard.php" class="btn-secondary" style="flex: 1; padding: 1rem; border-radius: 16px; font-weight: 800; border: 1.5px solid var(--border); text-align: center; text-decoration: none; color: var(--text-dim);">CANCEL</a>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>

<script>
const dayNames = <?php echo json_encode($days); ?>;
let slotIndex = <?php echo count($slots); ?>;

function addSlot() {
    const idx = slotIndex++;
    const container = document.getElementById('slotsContainer');
    const div = document.createElement('div');
    div.className = 'slot-row';
    div.id = 'slot-' + idx;

    const dayOptions = dayNames.map((d, i) => `<option value="${i}">${d}</option>`).join('');

    div.innerHTML = `
        <select name="slots[${idx}][day]" style="padding: 0.75rem; border-radius: 12px; border: 1.5px solid var(--border); font-weight: 700;">${dayOptions}</select>
        <div class="form-group" style="margin:0">
            <label style="font-weight: 800; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase;">From</label>
            <input type="time" name="slots[${idx}][start]" value="08:00" style="padding: 0.75rem; border-radius: 12px; border: 1.5px solid var(--border); font-weight: 700;" required>
        </div>
        <div class="form-group" style="margin:0">
            <label style="font-weight: 800; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase;">To</label>
            <input type="time" name="slots[${idx}][end]" value="12:00" style="padding: 0.75rem; border-radius: 12px; border: 1.5px solid var(--border); font-weight: 700;" required>
        </div>
        <button type="button" onclick="removeSlot('slot-${idx}')" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: none; width: 35px; height: 35px; border-radius: 10px; font-weight: 800; cursor: pointer;">✕</button>
    `;
    container.appendChild(div);
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function removeSlot(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

// Auto-add one slot if none exist yet
window.addEventListener('DOMContentLoaded', function() {
    if (document.querySelectorAll('.slot-row').length === 0) addSlot();
});
</script>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
