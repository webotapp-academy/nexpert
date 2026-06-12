<?php
/**
 * Migration: Add cancellation columns to bookings table
 * This adds columns needed for learner session cancellation feature
 */

require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

echo "Starting migration: Add booking cancellation columns\n";

try {
    // Check if columns already exist
    $stmt = $pdo->query("DESCRIBE bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns in bookings table:\n";
    print_r($columns);
    
    $columnsToAdd = [];
    
    // Check and add cancellation_reason column
    if (!in_array('cancellation_reason', $columns)) {
        $columnsToAdd[] = "ADD COLUMN cancellation_reason TEXT NULL AFTER status";
        echo "Will add: cancellation_reason\n";
    } else {
        echo "Column cancellation_reason already exists\n";
    }
    
    // Check and add cancelled_by column
    if (!in_array('cancelled_by', $columns)) {
        $columnsToAdd[] = "ADD COLUMN cancelled_by ENUM('learner', 'expert', 'admin') NULL AFTER cancellation_reason";
        echo "Will add: cancelled_by\n";
    } else {
        echo "Column cancelled_by already exists\n";
    }
    
    // Check and add cancelled_at column
    if (!in_array('cancelled_at', $columns)) {
        $columnsToAdd[] = "ADD COLUMN cancelled_at DATETIME NULL AFTER cancelled_by";
        echo "Will add: cancelled_at\n";
    } else {
        echo "Column cancelled_at already exists\n";
    }
    
    // Run ALTER TABLE if there are columns to add
    if (!empty($columnsToAdd)) {
        $sql = "ALTER TABLE bookings " . implode(", ", $columnsToAdd);
        echo "\nExecuting SQL:\n$sql\n\n";
        $pdo->exec($sql);
        echo "Migration completed successfully!\n";
    } else {
        echo "\nNo columns to add. Migration not needed.\n";
    }
    
    // Verify the changes
    echo "\nVerifying columns after migration:\n";
    $stmt = $pdo->query("DESCRIBE bookings");
    $newColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($newColumns as $col) {
        if (in_array($col['Field'], ['cancellation_reason', 'cancelled_by', 'cancelled_at'])) {
            echo "  - {$col['Field']}: {$col['Type']} ({$col['Null']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
