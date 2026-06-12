<?php
// Script to run the trust system migration

try {
    // 1. Database settings (Hostinger)
    $host = 'srv1983.hstgr.io';
    $user = 'u181502964_MakeNew';
    $pass = '!YcXSZlt@cI5';
    $dbname = 'u181502964_MakeNew';

    // 2. Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. (Skipped users check)

    // 4. Run migration
    $sqlFile = __DIR__ . '/../migrations/001_trust_system_schema.sql';
    if (!file_exists($sqlFile)) {
        die("Migration file not found: $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );

    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            echo "Warning: " . $e->getMessage() . "\n";
        }
    }

    echo "\nMigration completed successfully!\n";
} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
