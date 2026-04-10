<?php
/**
 * Counselor/Head Counselor: view student case file by user_id.
 * Redirects to student_profile (same data, counselor-facing view).
 */
require_once 'config.php';
requireLogin();
if (!isCounselor() && !isAdmin()) {
    header('Location: unauthorized.php');
    exit;
}
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($user_id < 1) {
    header('Location: ' . (isAdmin() ? 'head_counselor_appointments.php' : 'student_list.php'));
    exit;
}
header('Location: student_profile.php?user_id=' . $user_id);
exit;
