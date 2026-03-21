<?php
require_once 'config.php';
requireAdmin();

$success = '';
$error   = '';

// Delete user
if (isset($_GET['delete'], $_GET['id'])) {
    $del_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND user_id != ?");
    $admin_id = $_SESSION['user_id'];
    $stmt->bind_param("ii", $del_id, $admin_id);
    $stmt->execute() ? ($success = 'User deleted successfully') && queueToast($success, 'success', 'User Deleted') : ($error = 'Failed to delete user') && queueToast('Failed to delete user', 'error', 'Delete Failed');
}

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $email     = sanitize($_POST['email']);
    $password  = $_POST['password'];
    $full_name = sanitize($_POST['full_name']);
    $user_type = sanitize($_POST['user_type']);

    if (empty($email) || empty($password) || empty($full_name) || empty($user_type)) {
        $error = 'All fields are required';
    } else {
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            $hp  = hashPassword($password);
            $ins = $conn->prepare("INSERT INTO users (email, password, full_name, user_type) VALUES (?,?,?,?)");
            $ins->bind_param("ssss", $email, $hp, $full_name, $user_type);
            $ins->execute() ? ($success = 'User added successfully') && queueToast($success, 'success', 'User Added') : ($error = 'Failed to add user') && queueToast('Failed to add user', 'error', 'Add Failed');
        }
    }
}

// Get all users + optional role filter
$role_filter = isset($_GET['role_filter']) ? sanitize($_GET['role_filter']) : '';
$search      = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$q = "SELECT user_id, full_name, email, user_type, created_at FROM users";
$conditions = [];
$params = []; $types = '';

if ($role_filter) { $conditions[] = "user_type = ?"; $params[] = $role_filter; $types .= 's'; }
if ($search)      { $conditions[] = "(full_name LIKE ? OR email LIKE ?)";
                    $s = "%$search%"; $params[] = $s; $params[] = $s; $types .= 'ss'; }

if ($conditions) $q .= " WHERE " . implode(' AND ', $conditions);
$q .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($q);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container">
    <div class="dashboard-header">
        <h1>👥 Manage Users</h1>
        <p><?php echo count($users); ?> user(s) shown</p>
    </div>



    <!-- Add user form -->
    <div class="card">
        <h2>Add New User</h2>
        <form method="POST" style="display:grid;grid-template-columns:1fr 1fr;gap:0 1.25rem">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required placeholder="user@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required placeholder="Min. 6 chars">
            </div>
            <div class="form-group">
                <label for="user_type">Role *</label>
                <select id="user_type" name="user_type" required>
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="counselor">Counselor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="grid-column:1/-1">
                <button type="submit" name="add_user" class="btn-primary">➕ Add User</button>
            </div>
        </form>
    </div>

    <!-- User list with filters -->
    <div class="card">
        <h2>All Users</h2>

        <!-- Search & role filter toolbar -->
        <form method="GET" class="filter-bar" style="margin-bottom:1.5rem">
            <input type="text" name="search" id="liveSearch"
                   placeholder="🔍 Search by name or email…"
                   value="<?php echo htmlspecialchars($search); ?>"
                   oninput="liveFilter()">
            <select name="role_filter" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <?php foreach (['student','counselor','admin'] as $r): ?>
                    <option value="<?php echo $r; ?>" <?php echo $role_filter === $r ? 'selected' : ''; ?>>
                        <?php echo ucfirst($r); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            <?php if ($search || $role_filter): ?>
                <a href="manage_users.php" class="btn-secondary btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($users)): ?>
        <div class="table-wrapper">
        <table class="table" id="usersTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            <?php foreach ($users as $user): ?>
            <tr class="user-row"
                data-name="<?php echo strtolower($user['full_name']); ?>"
                data-email="<?php echo strtolower($user['email']); ?>">
                <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <span class="role-chip role-<?php echo $user['user_type']; ?>">
                        <?php echo ucfirst($user['user_type']); ?>
                    </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                <td>
                    <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                        <!-- Styled inline confirm instead of browser alert -->
                        <button class="btn-sm btn-danger" onclick="confirmDelete(<?php echo $user['user_id']; ?>, this)">Delete</button>
                        <span class="delete-confirm" id="dc-<?php echo $user['user_id']; ?>">
                            Sure?
                            <a href="?delete=1&id=<?php echo $user['user_id']; ?>" class="btn-sm btn-danger">Yes</a>
                            <button class="btn-sm btn-secondary" onclick="cancelDelete(<?php echo $user['user_id']; ?>)">No</button>
                        </span>
                    <?php else: ?>
                        <span class="text-muted" style="font-size:.82rem">(You)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <p class="text-muted">No users found.</p>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>

<script>
function liveFilter() {
    const q = document.getElementById('liveSearch').value.toLowerCase();
    document.querySelectorAll('.user-row').forEach(row => {
        row.style.display =
            row.dataset.name.includes(q) || row.dataset.email.includes(q) ? '' : 'none';
    });
}
function confirmDelete(id, btn) {
    btn.style.display = 'none';
    document.getElementById('dc-' + id).classList.add('show');
}
function cancelDelete(id) {
    document.getElementById('dc-' + id).classList.remove('show');
    document.querySelector('[onclick="confirmDelete(' + id + ', this)"]').style.display = '';
}
</script>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
