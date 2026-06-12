<?php
require_once __DIR__ . '/admin-panel/apis/connection/pdo.php';

$queries = [
    "ALTER TABLE bookings ADD COLUMN reschedule_requested TINYINT(1) DEFAULT 0",
    "ALTER TABLE bookings ADD COLUMN reschedule_new_datetime DATETIME NULL",
    "ALTER TABLE bookings ADD COLUMN reschedule_reason TEXT NULL",
    "ALTER TABLE bookings ADD COLUMN reschedule_requested_by ENUM('learner', 'expert') NULL",
    "ALTER TABLE bookings ADD COLUMN reschedule_requested_at DATETIME NULL",
    "ALTER TABLE bookings ADD COLUMN reschedule_accepted TINYINT(1) DEFAULT 0",
    "ALTER TABLE bookings ADD COLUMN reschedule_accepted_at DATETIME NULL",
    "ALTER TABLE bookings ADD COLUMN reschedule_declined TINYINT(1) DEFAULT 0",
    "ALTER TABLE bookings ADD COLUMN reschedule_declined_at DATETIME NULL"
];

foreach ($queries as $query) {
    try {
        $pdo->exec($query);
        echo "Success: $query\n";
    } catch (PDOException $e) {
        echo "Note: " . $e->getMessage() . "\n";
    }
}
echo "Migration complete!\n";
