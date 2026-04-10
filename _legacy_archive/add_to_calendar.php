<?php
require_once 'config.php';
requireStudent();

$appointment_id = intval($_GET['id'] ?? 0);
if (!$appointment_id) {
    die("Invalid appointment ID.");
}

$user_id = $_SESSION['user_id'];

// Fetch appointment and counselor details
$stmt = $conn->prepare("
    SELECT a.*, u.full_name as counselor_name, u.email as counselor_email
    FROM appointments a
    JOIN users u ON a.counselor_id = u.user_id
    WHERE a.appointment_id = ? AND a.student_id = ?
");
$stmt->bind_param("ii", $appointment_id, $user_id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();

if (!$app) {
    die("Appointment not found or access denied.");
}

$start_time = date('Ymd\THis', strtotime($app['appointment_date'] . ' ' . $app['appointment_time']));
$end_time   = date('Ymd\THis', strtotime($app['appointment_date'] . ' ' . $app['appointment_time'] . ' +1 hour'));
$summary    = "Counseling Session with " . $app['counselor_name'];
$description = "Topic: " . ($app['reason'] ?: 'Not specified') . "\\nStatus: " . ucfirst($app['status']);
$location   = "School Counseling Office / Online";

// ICS file content
$ics = "BEGIN:VCALENDAR\r\n" .
       "VERSION:2.0\r\n" .
       "PROID:-//PSU Mental Health Portal//EN\r\n" .
       "CALSCALE:GREGORIAN\r\n" .
       "METHOD:PUBLISH\r\n" .
       "BEGIN:VEVENT\r\n" .
       "UID:" . uniqid() . "@psumentalportal.edu\r\n" .
       "DTSTAMP:" . date('Ymd\THis\Z') . "\r\n" .
       "DTSTART:" . $start_time . "\r\n" .
       "DTEND:" . $end_time . "\r\n" .
       "SUMMARY:" . $summary . "\r\n" .
       "DESCRIPTION:" . $description . "\r\n" .
       "LOCATION:" . $location . "\r\n" .
       "END:VEVENT\r\n" .
       "END:VCALENDAR";

// Set headers for download
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=appointment_' . $appointment_id . '.ics');

echo $ics;
exit;
