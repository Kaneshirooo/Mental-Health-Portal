<?php
require_once 'config.php';
requireCounselor();

$counselor_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

// Build query for session logs (similar to counselor_ledger.php)
$query = "SELECT sl.*, u.full_name, u.user_type, u.email, u.roll_number 
          FROM session_logs sl 
          JOIN users u ON sl.user_id = u.user_id 
          WHERE 1=1";

$params = [];
$types = "";

if ($search) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.roll_number LIKE ?)";
    $st = "%$search%";
    $params[] = $st; $params[] = $st; $params[] = $st;
    $types .= "sss";
}

if ($role_filter) {
    $query .= " AND u.user_type = ?";
    $params[] = $role_filter;
    $types .= "s";
}

$query .= " ORDER BY sl.login_time DESC LIMIT 1000";

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=activity_ledger_' . date('Y-m-d') . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, ['Full Name', 'Role', 'Email', 'Roll Number', 'Login Time', 'Logout Time', 'Activity']);

// Loop over the logs and output them
foreach ($logs as $log) {
    fputcsv($output, [
        $log['full_name'],
        $log['user_type'],
        $log['email'],
        $log['roll_number'],
        $log['login_time'],
        $log['logout_time'] ?? 'LIVE SESSION',
        $log['activity']
    ]);
}

fclose($output);
exit;
