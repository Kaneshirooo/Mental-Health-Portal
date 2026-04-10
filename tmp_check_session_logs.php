<?php
$conn = new mysqli("127.0.0.1", "root", "", "mental_health_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$result = $conn->query("DESCRIBE session_logs");
while ($row = $result->fetch_row()) echo $row[0] . " (" . $row[1] . ")\n";
$conn->close();
