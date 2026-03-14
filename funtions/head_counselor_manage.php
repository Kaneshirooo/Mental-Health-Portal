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
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .counselor-card {
            background: white;
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

<div class="container" style="max-width: 1400px; padding-top: 5rem; padding-bottom: 8rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Clinical Personnel Registry</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.75rem;">Staff Management</h1>
            <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">System is active. <?php echo count($counselors); ?> clinical professionals registered.</p>
        </div>
        <div style="padding: 1rem 2.5rem; border-radius: 50px; background: #ecfdf5; color: #059669; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 0.75rem;">
            <span>🛡️</span> Admin Authority Active
        </div>
    </div>



    <!-- Personnel Registration -->
    <div style="background: white; border-radius: 40px; padding: 4rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 6rem;">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; margin-bottom: 3rem; color: var(--text);">Initialize Clinical Staff</h2>
        <form method="POST" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
            <div>
                <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem;">Full Clinical Name</label>
                <input type="text" name="full_name" required placeholder="Dr. Maria Santos" style="width: 100%; padding: 1.25rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); font-weight: 600; font-family: inherit; font-size: 1rem; background: #f8fafc;">
            </div>
            <div>
                <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem;">Internal Email Identity</label>
                <input type="email" name="email" required placeholder="counselor@school.edu" style="width: 100%; padding: 1.25rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); font-weight: 600; font-family: inherit; font-size: 1rem; background: #f8fafc;">
            </div>
            <div>
                <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem;">Activation Passkey</label>
                <input type="password" name="password" required placeholder="Secure temporary credentials" style="width: 100%; padding: 1.25rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); font-weight: 600; font-family: inherit; font-size: 1rem; background: #f8fafc;">
            </div>
            <div>
                <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem;">Clinical Department</label>
                <input type="text" name="department" placeholder="e.g. Guidance Sanctuary" style="width: 100%; padding: 1.25rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); font-weight: 600; font-family: inherit; font-size: 1rem; background: #f8fafc;">
            </div>
            <div style="grid-column: 1 / -1; margin-top: 2rem;">
                <button type="submit" name="add_counselor" style="padding: 1.25rem 4rem; border-radius: 50px; background: var(--primary); color: white; border: none; font-weight: 800; cursor: pointer; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2); transition: var(--transition);">INITIALIZE STAFF MEMBER →</button>
            </div>
        </form>
    </div>

    <!-- Counselor Registry -->
    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; margin-bottom: 3rem; color: var(--text);">Personnel Archive</h2>
    <?php if (empty($counselors)): ?>
        <div style="padding: 6rem; text-align: center; background: #f8fafc; border-radius: 40px; border: 2.5px dashed var(--border);">
            <div style="font-size: 4rem; margin-bottom: 2rem;">👩‍⚕️</div>
            <h3 style="color: var(--text-dim); font-weight: 700; font-size: 1.25rem;">No clinical staff records found.</h3>
            <p style="color: var(--text-dim); opacity: 0.7; font-weight: 600;">Initialize your first professional identity above.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
            <?php foreach ($counselors as $c): 
                $initials = strtoupper(substr($c['full_name'], 0, 1) . substr(explode(' ', $c['full_name'])[1] ?? '', 0, 1));
            ?>
            <div style="background: white; border-radius: 40px; padding: 3rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); transition: var(--transition);" onmouseover="this.style.borderColor='var(--primary-light)'; this.style.boxShadow='var(--shadow)';" onmouseout="this.style.borderColor='var(--border)'; this.style.boxShadow='var(--shadow-sm)';">
                <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 2rem;">
                    <div style="width: 70px; height: 70px; border-radius: 20px; background: #f5f3ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.5rem; border: 1px solid var(--border);">
                        <?php echo $initials; ?>
                    </div>
                    <div>
                        <div style="font-weight: 800; color: var(--text); font-size: 1.5rem; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($c['full_name']); ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-dim); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;"><?php echo $c['email']; ?></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
                    <div style="background: #f8fafc; padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.5rem;">Department Sanctuary</div>
                        <div style="font-weight: 800; color: var(--text);"><?php echo htmlspecialchars($c['department'] ?: 'General Clinical'); ?></div>
                    </div>
                    <div style="background: #f8fafc; padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.5rem;">Total Clinical Intake</div>
                        <div style="font-weight: 800; color: var(--text);"><?php echo $c['total_appointments']; ?> Sessions</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.75rem; color: var(--text-dim); font-weight: 600;">Registered: <?php echo date('M d, Y', strtotime($c['created_at'])); ?></div>
                    <a href="?delete=1&id=<?php echo $c['user_id']; ?>" onclick="return confirm('Verify removal of clinical professional? This action is irreversible.')" style="text-decoration: none; font-weight: 800; color: #dc2626; font-size: 0.85rem; padding: 0.75rem 1.5rem; border-radius: 50px; border: 1.5px solid #fee2e2; transition: var(--transition);">REVOKE ACCESS</a>
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
