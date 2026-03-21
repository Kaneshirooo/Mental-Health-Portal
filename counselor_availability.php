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
    <style>
        .slot-row {
            display: grid;
            grid-template-columns: 140px 1fr 1fr auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            background: var(--surface-2);
            border-radius: var(--radius-sm);
            margin-bottom: 0.75rem;
            transition: var(--transition);
            border: 1px solid var(--border);
        }
        .slot-row:hover { background: white; border-color: var(--border-hover); box-shadow: var(--shadow-sm); }
        
        .add-slot-btn {
            width: 100%;
            background: white;
            border: 2px dashed var(--border);
            color: var(--primary);
            border-radius: var(--radius-sm);
            padding: 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .add-slot-btn:hover { border-color: var(--primary); background: #f0fdfa; transform: translateY(-1px); }
        
        .day-chip {
            background: var(--primary);
            color: white;
            padding: 0.35rem 0.85rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }
        .schedule-item:hover { transform: translateX(3px); border-color: var(--border-hover); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1000px; padding-top: 1.5rem; padding-bottom: 3rem;">
    <div style="margin-bottom: 2rem;">
        <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Clinical Profile</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Manage Availability</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Define your active clinical hours for student discovery and booking.</p>
    </div>



    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; align-items: start;">
        
        <!-- Current Schedule -->
        <div>
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin-bottom: 1.5rem; color: var(--text);">Active Hours</h2>
            <?php if (empty($slots)): ?>
                <div style="padding: 2.5rem; background: var(--surface-2); border-radius: var(--radius); text-align: center; border: 1px dashed var(--border);">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">⏳</div>
                    <p style="font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">No active hours defined yet.</p>
                </div>
            <?php else: ?>
                <div class="current-slots">
                    <?php foreach ($slots as $s): ?>
                    <div class="schedule-item">
                        <span class="day-chip"><?php echo $days[$s['day_of_week']]; ?></span>
                        <span style="font-weight: 600; color: var(--text); font-size: 0.88rem;">
                            <?php echo date('g:i A', strtotime($s['start_time'])); ?> - <?php echo date('g:i A', strtotime($s['end_time'])); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 1.5rem; padding: 1.25rem; background: #f0fdfa; border-radius: var(--radius-sm); border: 1px solid rgba(13, 148, 136, 0.1);">
                <div style="font-weight: 700; color: var(--primary); margin-bottom: 0.35rem; font-size: 0.8rem;">Clinical Tip</div>
                <p style="font-size: 0.78rem; color: var(--primary-dark); line-height: 1.5; font-weight: 400;">Consistency in your availability helps students build trust and ensures a stable support structure.</p>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card" style="padding: 1.5rem; border-radius: var(--radius);">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text);">Modify Schedule</h2>
            <form method="POST" id="availForm">
                <input type="hidden" name="save_availability" value="1">
                <div id="slotsContainer">
                    <?php foreach ($slots as $idx => $s): ?>
                    <div class="slot-row" id="slot-<?php echo $idx; ?>">
                        <select name="slots[<?php echo $idx; ?>][day]" style="padding: 0.6rem; border-radius: 8px; border: 1.5px solid var(--border); font-weight: 600; font-size: 0.85rem; font-family: inherit;">
                            <?php foreach ($days as $i => $d): ?>
                                <option value="<?php echo $i; ?>" <?php echo $s['day_of_week'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $d; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-group" style="margin:0">
                            <label style="font-weight: 600; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">From</label>
                            <input type="time" name="slots[<?php echo $idx; ?>][start]" value="<?php echo htmlspecialchars($s['start_time']); ?>" style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1.5px solid var(--border); font-weight: 600; font-size: 0.85rem; font-family: inherit;" required>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label style="font-weight: 600; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">To</label>
                            <input type="time" name="slots[<?php echo $idx; ?>][end]" value="<?php echo htmlspecialchars($s['end_time']); ?>" style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1.5px solid var(--border); font-weight: 600; font-size: 0.85rem; font-family: inherit;" required>
                        </div>
                        <button type="button" onclick="removeSlot('slot-<?php echo $idx; ?>')" style="background: #fff1f2; color: #e11d48; border: none; width: 32px; height: 32px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="add-slot-btn" onclick="addSlot()">+ Add Another Slot</button>

                <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
                    <button type="submit" class="btn-primary" style="flex: 2; padding: 0.85rem; border-radius: var(--radius-sm); font-weight: 600; background: var(--primary); border: none; color: white; cursor: pointer; font-size: 0.9rem;">Commit Changes</button>
                    <a href="counselor_dashboard.php" class="btn-secondary" style="flex: 1; padding: 0.85rem; border-radius: var(--radius-sm); font-weight: 600; border: 1.5px solid var(--border); text-align: center; text-decoration: none; color: var(--text-muted); font-size: 0.9rem;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
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
        <select name="slots[\${idx}][day]" style="padding: 0.6rem; border-radius: 8px; border: 1.5px solid var(--border); font-weight: 600; font-size: 0.85rem; font-family: inherit;">\${dayOptions}</select>
        <div class="form-group" style="margin:0">
            <label style="font-weight: 600; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">From</label>
            <input type="time" name="slots[\${idx}][start]" value="08:00" style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1.5px solid var(--border); font-weight: 600; font-size: 0.85rem; font-family: inherit;" required>
        </div>
        <div class="form-group" style="margin:0">
            <label style="font-weight: 600; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">To</label>
            <input type="time" name="slots[\${idx}][end]" value="12:00" style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1.5px solid var(--border); font-weight: 600; font-size: 0.85rem; font-family: inherit;" required>
        </div>
        <button type="button" onclick="removeSlot('slot-\${idx}')" style="background: #fff1f2; color: #e11d48; border: none; width: 32px; height: 32px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">✕</button>
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
