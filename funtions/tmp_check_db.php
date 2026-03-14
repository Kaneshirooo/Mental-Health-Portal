<?php
require 'config.php';
$res = $conn->query('DESCRIBE anonymous_notes');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo 'Table not found: ' . $conn->error;
}
?>
