<?php
require_once 'config.php';
requireAdmin(); // Head counselor uses the admin account

$success = '';
$error   = '';

// ── Add counselor ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_counselor'])) {
    $email     = sanitize($_POST['email']);
    $password  = $_POST['password'];
    $full_name = sanitize($_POST['full_name']);
    $dept      = sanitize($_POST['department']);

    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Full name, email and password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'That email is already registered.';
        } else {
            $hp  = hashPassword($password);
            $ins = $conn->prepare(
                "INSERT INTO users (email, password, full_name, user_type, department) VALUES (?,?,?,'counselor',?)"
            );
            $ins->bind_param("ssss", $email, $hp, $full_name, $dept);
            $ins->execute() ? ($success = "Counselor \"$full_name\" added successfully.") && queueToast($success, 'success', 'Staff Added') : ($error = 'Failed to add counselor.') && queueToast('Failed to add counselor.', 'error', 'Add Failed');
        }
    }
}

// ── Delete counselor ──────────────────────────────────
if (isset($_GET['delete'], $_GET['id'])) {
    $del_id   = intval($_GET['id']);
    $admin_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND user_type = 'counselor' AND user_id != ?");
    $stmt->bind_param("ii", $del_id, $admin_id);
    $stmt->execute() && $stmt->affected_rows > 0
        ? ($success = 'Counselor removed successfully.') && queueToast($success, 'success', 'Staff Removed')
        : ($error   = 'Could not remove counselor.') && queueToast('Could not remove counselor.', 'error', 'Remove Failed');
}

// ── Fetch all counselors ──────────────────────────────
$counselors = $conn->query(
    "SELECT u.user_id, u.full_name, u.email, u.department, u.created_at,
            COUNT(a.appointment_id) AS total_appointments
     FROM users u
     LEFT JOIN appointments a ON a.counselor_id = u.user_id
     WHERE u.user_type = 'counselor'
     GROUP BY u.user_id ORDER BY u.full_name ASC"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Counselors — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.1">
    <?php include 'theme_init.php'; ?>
    <style>
        .counselor-card {
            background: var(--surface-solid);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 2rem;
            transition: var(--transition);
        }
        .counselor-card:hover { transform: translateY(-5px); border-color: var(--primary-light); box-shadow: var(--shadow); }
        .staff-avatar { width: 60px; height: 60px; border-radius: 18px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.25rem; }
        .meta-group { display: flex; gap: 1.5rem; margin-top: 0.75rem; flex-wrap: wrap; }
        .meta-item { font-size: 0.8rem; color: var(--text-dim); font-weight: 600; display: flex; align-items: center; gap: 0.4rem; }
        .delete-confirm { display: none; background: #fff1f2; border: 1px solid #fecaca; padding: 1rem 1.5rem; border-radius: 15px; grid-column: 1 / -1; margin-top: 1rem; align-items: center; justify-content: space-between; gap: 1rem; }
        .delete-confirm.show { display: flex; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Clinical Personnel Registry</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Staff Management</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Internal registry for <?php echo count($counselors); ?> clinical professionals.</p>
        </div>
        <div style="padding: 0.5rem 1.25rem; border-radius: var(--radius-sm); background: #f0fdfa; color: var(--primary); font-weight: 600; font-size: 0.75rem; display: flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(13, 148, 136, 0.1);">
            <span>🛡️</span> Administrative Mode
        </div>
    </div>



    <!-- Personnel Registration -->
    <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 3rem;">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);">Onboard New Professional</h2>
        <form method="POST" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Full Name</label>
                <input type="text" name="full_name" required placeholder="Name" style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2);">
            </div>
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Email Address</label>
                <input type="email" name="email" required placeholder="Institutional email" style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2);">
            </div>
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Temporary Password</label>
                <input type="password" name="password" required placeholder="Password" style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2);">
            </div>
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Division / Department</label>
                <input type="text" name="department" placeholder="Guidance Department" style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2);">
            </div>
            <div style="grid-column: 1 / -1;">
                <button type="submit" name="add_counselor" style="padding: 0.75rem 2rem; border-radius: var(--radius-sm); background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">Register Professional</button>
            </div>
        </form>
    </div>

    <!-- Counselor Registry -->
    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);">Personnel Registry</h2>
    <?php if (empty($counselors)): ?>
        <div style="padding: 3rem; text-align: center; background: var(--surface-2); border-radius: var(--radius); border: 2px dashed var(--border);">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">👩‍⚕️</div>
            <h3 style="color: var(--text-muted); font-weight: 600; font-size: 1rem;">No clinical staff records found.</h3>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <?php foreach ($counselors as $c): 
                $nameParts = explode(' ', $c['full_name']);
                $initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
            ?>
            <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); transition: var(--transition);">
                <div style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: var(--surface-2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; border: 1px solid var(--border);">
                        <?php echo $initials; ?>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--text); font-size: 1.15rem;"><?php echo htmlspecialchars($c['full_name']); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;"><?php echo $c['email']; ?></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="background: var(--surface-2); padding: 1rem; border-radius: 12px; border: 1px solid var(--border);">
                        <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.35rem; letter-spacing: 0.04em;">Department</div>
                        <div style="font-weight: 600; color: var(--text); font-size: 0.88rem;"><?php echo htmlspecialchars($c['department'] ?: 'Guidance'); ?></div>
                    </div>
                    <div style="background: var(--surface-2); padding: 1rem; border-radius: 12px; border: 1px solid var(--border);">
                        <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.35rem; letter-spacing: 0.04em;">Activity</div>
                        <div style="font-weight: 600; color: var(--text); font-size: 0.88rem;"><?php echo $c['total_appointments']; ?> Sessions</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 500;">Joined <?php echo date('M Y', strtotime($c['created_at'])); ?></div>
                    <a href="?delete=1&id=<?php echo $c['user_id']; ?>" onclick="return confirm('Verify removal of clinical professional?')" style="text-decoration: none; font-weight: 600; color: #e11d48; font-size: 0.78rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); border: 1px solid #fff1f2; transition: var(--transition); background: #fff1f2;">Revoke Access</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>

<script>
function confirmDel(id, btn) {
    btn.style.display = 'none';
    document.getElementById('dc-' + id).classList.add('show');
}
function cancelDel(id) {
    document.getElementById('dc-' + id).classList.remove('show');
    document.querySelector('[onclick="confirmDel(' + id + ', this)"]').style.display = '';
}
</script>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
