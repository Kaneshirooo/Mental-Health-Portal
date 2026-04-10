<?php
require_once 'config.php';
$columns = $conn->query("SHOW COLUMNS FROM users")->fetch_all(MYSQLI_ASSOC);
echo json_encode($columns, JSON_PRETTY_PRINT);
?>
