<?php
require 'config.php';

$queries = [
    "ALTER TABLE anonymous_notes ADD COLUMN reply TEXT AFTER message",
    "ALTER TABLE anonymous_notes ADD COLUMN replied_at TIMESTAMP NULL AFTER reply",
    "ALTER TABLE anonymous_notes ADD COLUMN counselor_id INT NULL AFTER replied_at",
    "ALTER TABLE anonymous_notes MODIFY COLUMN status ENUM('new','read','replied','archived') DEFAULT 'new'",
    "ALTER TABLE anonymous_notes ADD CONSTRAINT fk_anon_counselor FOREIGN KEY (counselor_id) REFERENCES users(user_id) ON DELETE SET NULL"
];

foreach ($queries as $sql) {
    try {
        if ($conn->query($sql)) {
            echo "Successfully executed: $sql\n";
        }
    } catch (Exception $e) {
        echo "Error executing $sql: " . $e->getMessage() . "\n";
    }
}
?>
