<?php
/**
 * Migration: Add expert card fields (strengths, outcomes, stats)
 * Run this to add new columns for the improved expert card design
 */

require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';

try {
    echo "🔧 Adding new columns to expert_profiles table...\n\n";
    
    // Check if columns already exist
    $stmt = $pdo->query("SHOW COLUMNS FROM expert_profiles LIKE 'strengths'");
    
    if ($stmt->rowCount() == 0) {
        echo "➕ Adding: strengths, expected_outcomes, bookings_this_month, satisfaction_percent\n";
        
        $pdo->exec("
            ALTER TABLE expert_profiles 
            ADD COLUMN strengths TEXT NULL COMMENT 'JSON array of strengths',
            ADD COLUMN expected_outcomes TEXT NULL COMMENT 'JSON array of expected outcomes',
            ADD COLUMN bookings_this_month INT DEFAULT 0 COMMENT 'Bookings count this month',
            ADD COLUMN satisfaction_percent INT DEFAULT 97 COMMENT 'Satisfaction percentage (0-100)'
        ");
        
        echo "✅ Columns added successfully!\n\n";
        
        // Add sample data to existing experts
        echo "📊 Adding sample data to existing experts...\n";
        
        $pdo->exec("
            UPDATE expert_profiles 
            SET 
                strengths = CASE 
                    WHEN category LIKE '%Developer%' OR category LIKE '%Software%' THEN 
                        '[\"Code Architecture\", \"Full Stack Development\", \"Technical Mentoring\"]'
                    WHEN category LIKE '%Marketing%' OR category LIKE '%Digital%' THEN 
                        '[\"Campaign Strategy\", \"SEO & Analytics\", \"Growth Hacking\"]'
                    WHEN category LIKE '%Business%' OR category LIKE '%Consultant%' THEN 
                        '[\"Business Strategy\", \"Market Analysis\", \"Operations Optimization\"]'
                    WHEN category LIKE '%Design%' THEN 
                        '[\"UI/UX Design\", \"Brand Identity\", \"Design Systems\"]'
                    WHEN category LIKE '%Data%' THEN 
                        '[\"Data Analysis\", \"ML Models\", \"Statistical Methods\"]'
                    ELSE 
                        '[\"Professional Guidance\", \"Industry Expertise\", \"Practical Solutions\"]'
                END,
                expected_outcomes = CASE 
                    WHEN category LIKE '%Developer%' OR category LIKE '%Software%' THEN 
                        '[\"Clean code practices\", \"Scalable architecture\", \"Production-ready apps\"]'
                    WHEN category LIKE '%Marketing%' OR category LIKE '%Digital%' THEN 
                        '[\"ROI improvement\", \"Traffic growth\", \"Conversion optimization\"]'
                    WHEN category LIKE '%Business%' OR category LIKE '%Consultant%' THEN 
                        '[\"Growth strategy\", \"Market fit\", \"Revenue scaling\"]'
                    WHEN category LIKE '%Design%' THEN 
                        '[\"User-centered design\", \"Brand consistency\", \"Design portfolio\"]'
                    WHEN category LIKE '%Data%' THEN 
                        '[\"Data insights\", \"Predictive models\", \"Business intelligence\"]'
                    ELSE 
                        '[\"Skill improvement\", \"Goal achievement\", \"Career growth\"]'
                END,
                bookings_this_month = FLOOR(RAND() * 15) + 5,
                satisfaction_percent = FLOOR(RAND() * 8) + 92
            WHERE strengths IS NULL
        ");
        
        echo "✅ Sample data added!\n\n";
        
        // Create indexes
        echo "🔍 Creating indexes...\n";
        $pdo->exec("CREATE INDEX idx_bookings_month ON expert_profiles(bookings_this_month)");
        $pdo->exec("CREATE INDEX idx_satisfaction ON expert_profiles(satisfaction_percent)");
        echo "✅ Indexes created!\n\n";
        
    } else {
        echo "✅ Columns already exist!\n\n";
        
        // Update NULL values with sample data
        echo "🔄 Updating NULL values with sample data...\n";
        $pdo->exec("
            UPDATE expert_profiles 
            SET 
                strengths = CASE 
                    WHEN category LIKE '%Developer%' OR category LIKE '%Software%' THEN 
                        '[\"Code Architecture\", \"Full Stack Development\", \"Technical Mentoring\"]'
                    WHEN category LIKE '%Marketing%' OR category LIKE '%Digital%' THEN 
                        '[\"Campaign Strategy\", \"SEO & Analytics\", \"Growth Hacking\"]'
                    WHEN category LIKE '%Business%' OR category LIKE '%Consultant%' THEN 
                        '[\"Business Strategy\", \"Market Analysis\", \"Operations Optimization\"]'
                    WHEN category LIKE '%Design%' THEN 
                        '[\"UI/UX Design\", \"Brand Identity\", \"Design Systems\"]'
                    WHEN category LIKE '%Data%' THEN 
                        '[\"Data Analysis\", \"ML Models\", \"Statistical Methods\"]'
                    ELSE 
                        '[\"Professional Guidance\", \"Industry Expertise\", \"Practical Solutions\"]'
                END
            WHERE strengths IS NULL OR strengths = ''
        ");
        
        $pdo->exec("
            UPDATE expert_profiles 
            SET 
                expected_outcomes = CASE 
                    WHEN category LIKE '%Developer%' OR category LIKE '%Software%' THEN 
                        '[\"Clean code practices\", \"Scalable architecture\", \"Production-ready apps\"]'
                    WHEN category LIKE '%Marketing%' OR category LIKE '%Digital%' THEN 
                        '[\"ROI improvement\", \"Traffic growth\", \"Conversion optimization\"]'
                    WHEN category LIKE '%Business%' OR category LIKE '%Consultant%' THEN 
                        '[\"Growth strategy\", \"Market fit\", \"Revenue scaling\"]'
                    WHEN category LIKE '%Design%' THEN 
                        '[\"User-centered design\", \"Brand consistency\", \"Design portfolio\"]'
                    WHEN category LIKE '%Data%' THEN 
                        '[\"Data insights\", \"Predictive models\", \"Business intelligence\"]'
                    ELSE 
                        '[\"Skill improvement\", \"Goal achievement\", \"Career growth\"]'
                END
            WHERE expected_outcomes IS NULL OR expected_outcomes = ''
        ");
        
        $pdo->exec("
            UPDATE expert_profiles 
            SET bookings_this_month = FLOOR(RAND() * 15) + 5
            WHERE bookings_this_month = 0 OR bookings_this_month IS NULL
        ");
        
        $pdo->exec("
            UPDATE expert_profiles 
            SET satisfaction_percent = FLOOR(RAND() * 8) + 92
            WHERE satisfaction_percent = 0 OR satisfaction_percent IS NULL
        ");
        
        echo "✅ Data updated!\n\n";
    }
    
    // Show sample records
    echo "📋 Sample Expert Cards:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $stmt = $pdo->query("
        SELECT full_name, category, strengths, expected_outcomes, 
               bookings_this_month, satisfaction_percent 
        FROM expert_profiles 
        LIMIT 3
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "\n👤 " . $row['full_name'] . " (" . $row['category'] . ")\n";
        echo "   Strengths: " . substr($row['strengths'], 0, 60) . "...\n";
        echo "   Outcomes: " . substr($row['expected_outcomes'], 0, 60) . "...\n";
        echo "   📅 Bookings: " . $row['bookings_this_month'] . " this month\n";
        echo "   👍 Satisfaction: " . $row['satisfaction_percent'] . "%\n";
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Migration completed successfully!\n";
    echo "🎉 Expert cards will now show the new design!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
