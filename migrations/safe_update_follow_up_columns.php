<?php
/**
 * Safe Migration: Check and add missing columns for recurring follow-up emails
 * This script checks which columns exist before adding them
 */

require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';

try {
    echo "🔍 Checking existing columns in reviews table...\n\n";
    
    // Get all columns in reviews table
    $stmt = $pdo->query("SHOW COLUMNS FROM reviews");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing columns: " . implode(', ', $existingColumns) . "\n\n";
    
    // Check each column
    $columnsToAdd = [
        'follow_up_email_sent' => "TINYINT(1) DEFAULT 0 COMMENT '0=not sent, 1=sent' AFTER status",
        'follow_up_email_sent_at' => "DATETIME NULL COMMENT 'When follow-up email was sent' AFTER follow_up_email_sent",
        'follow_up_count' => "INT DEFAULT 0 COMMENT 'Number of follow-up emails sent (max 3)' AFTER follow_up_email_sent_at",
        'last_follow_up_date' => "DATETIME NULL COMMENT 'Date of last follow-up email' AFTER follow_up_count"
    ];
    
    $addedColumns = [];
    
    foreach ($columnsToAdd as $columnName => $definition) {
        if (!in_array($columnName, $existingColumns)) {
            echo "➕ Adding column: {$columnName}\n";
            
            // Need to adjust AFTER clause based on what exists
            if ($columnName === 'follow_up_email_sent') {
                $sql = "ALTER TABLE reviews ADD COLUMN {$columnName} {$definition}";
            } elseif ($columnName === 'follow_up_email_sent_at') {
                $afterCol = in_array('follow_up_email_sent', $existingColumns) ? 'follow_up_email_sent' : 'status';
                $sql = "ALTER TABLE reviews ADD COLUMN {$columnName} DATETIME NULL COMMENT 'When follow-up email was sent' AFTER {$afterCol}";
            } elseif ($columnName === 'follow_up_count') {
                $afterCol = in_array('follow_up_email_sent_at', $existingColumns) ? 'follow_up_email_sent_at' : 
                           (in_array('follow_up_email_sent', $existingColumns) ? 'follow_up_email_sent' : 'status');
                $sql = "ALTER TABLE reviews ADD COLUMN {$columnName} INT DEFAULT 0 COMMENT 'Number of follow-up emails sent (max 3)' AFTER {$afterCol}";
            } else { // last_follow_up_date
                $afterCol = in_array('follow_up_count', $existingColumns) ? 'follow_up_count' : 
                           (in_array('follow_up_email_sent_at', $existingColumns) ? 'follow_up_email_sent_at' : 
                           (in_array('follow_up_email_sent', $existingColumns) ? 'follow_up_email_sent' : 'status'));
                $sql = "ALTER TABLE reviews ADD COLUMN {$columnName} DATETIME NULL COMMENT 'Date of last follow-up email' AFTER {$afterCol}";
            }
            
            $pdo->exec($sql);
            $addedColumns[] = $columnName;
            echo "   ✅ Added!\n";
        } else {
            echo "⏭️  Column '{$columnName}' already exists, skipping\n";
        }
    }
    
    echo "\n";
    
    // Check and create indexes
    echo "🔍 Checking indexes...\n\n";
    
    $stmt = $pdo->query("SHOW INDEX FROM reviews WHERE Key_name = 'idx_follow_up_count'");
    if ($stmt->rowCount() == 0) {
        echo "➕ Creating index: idx_follow_up_count\n";
        $pdo->exec("CREATE INDEX idx_follow_up_count ON reviews(follow_up_count, last_follow_up_date)");
        echo "   ✅ Created!\n";
    } else {
        echo "⏭️  Index 'idx_follow_up_count' already exists\n";
    }
    
    echo "\n";
    
    // Update existing records if needed
    if (count($addedColumns) > 0 && in_array('follow_up_count', $addedColumns)) {
        echo "🔄 Updating existing records...\n";
        $pdo->exec("
            UPDATE reviews 
            SET follow_up_count = 1, 
                last_follow_up_date = follow_up_email_sent_at 
            WHERE follow_up_email_sent = 1 
              AND follow_up_email_sent_at IS NOT NULL
              AND follow_up_count = 0
        ");
        $affected = $pdo->exec("SELECT ROW_COUNT()");
        echo "   ✅ Updated existing follow-up records!\n\n";
    }
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Migration completed successfully!\n";
    echo "📊 Columns added: " . (count($addedColumns) > 0 ? implode(', ', $addedColumns) : 'None (all existed)') . "\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
