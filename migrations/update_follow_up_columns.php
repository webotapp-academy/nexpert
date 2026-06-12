<?php
/**
 * Migration Script: Add follow_up_count and last_follow_up_date columns
 * Run this to update the reviews table for recurring follow-up emails
 */

require_once __DIR__ . '/../admin-panel/apis/pdo.php';

try {
    // Check if columns already exist
    $check = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'follow_up_count'");
    
    if ($check->rowCount() == 0) {
        echo "Adding follow_up_count and last_follow_up_date columns...\n";
        
        $pdo->exec("
            ALTER TABLE reviews 
            ADD COLUMN follow_up_count INT DEFAULT 0 COMMENT 'Number of follow-up emails sent (max 3)' AFTER follow_up_email_sent_at,
            ADD COLUMN last_follow_up_date DATETIME NULL COMMENT 'Date of last follow-up email' AFTER follow_up_count
        ");
        
        echo "✅ Columns added successfully!\n";
        
        // Create index
        echo "Creating index...\n";
        $pdo->exec("CREATE INDEX idx_follow_up_count ON reviews(follow_up_count, last_follow_up_date)");
        echo "✅ Index created!\n";
        
        // Update existing records
        echo "Updating existing records...\n";
        $pdo->exec("
            UPDATE reviews 
            SET follow_up_count = 1, 
                last_follow_up_date = follow_up_email_sent_at 
            WHERE follow_up_email_sent = 1 
              AND follow_up_email_sent_at IS NOT NULL
        ");
        echo "✅ Existing records updated!\n";
        
    } else {
        echo "✅ Columns already exist!\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
