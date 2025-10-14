<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexpert.ai Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Nexpert.ai Database Setup</h1>
        
        <?php
        // Check if this is a POST request to run setup
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_database'])) {
            echo "<h2>Setting up database...</h2>";
            
            // Database connection parameters
            $host = 'localhost';
            $username = 'root';
            $password = '';
            
            try {
                // Connect to MySQL without specifying database
                $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                
                echo "<p class='info'>✓ Connected to MySQL server successfully</p>";
                
                // Read and execute the SQL file
                $sqlFile = __DIR__ . '/complete_database_setup.sql';
                if (!file_exists($sqlFile)) {
                    throw new Exception("SQL file not found: $sqlFile");
                }
                
                $sql = file_get_contents($sqlFile);
                
                // Split SQL into individual statements
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
                    }
                );
                
                $successCount = 0;
                $errors = [];
                
                foreach ($statements as $statement) {
                    try {
                        $pdo->exec($statement);
                        $successCount++;
                    } catch (PDOException $e) {
                        // Skip harmless errors like "database already exists"
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            $errors[] = "Error executing statement: " . $e->getMessage();
                        }
                    }
                }
                
                if (empty($errors)) {
                    echo "<p class='success'>✅ Database setup completed successfully!</p>";
                    echo "<p class='success'>📊 Executed $successCount SQL statements</p>";
                    echo "<p class='info'>🔑 Default admin login:</p>";
                    echo "<pre>Email: admin@nexpert.ai\nPassword: password</pre>";
                    echo "<p class='warning'>⚠️ Please change the default admin password after first login!</p>";
                    
                    echo "<h3>Next Steps:</h3>";
                    echo "<ul>";
                    echo "<li>Visit your application homepage</li>";
                    echo "<li>Login as admin to configure settings</li>";
                    echo "<li>Create expert and learner test accounts</li>";
                    echo "<li>Configure payment gateways (Razorpay)</li>";
                    echo "</ul>";
                    
                    echo "<a href='index.php' class='btn'>Go to Application →</a>";
                } else {
                    echo "<p class='error'>❌ Some errors occurred:</p>";
                    foreach ($errors as $error) {
                        echo "<p class='error'>• $error</p>";
                    }
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Setup failed: " . $e->getMessage() . "</p>";
                echo "<p class='warning'>Please check:</p>";
                echo "<ul>";
                echo "<li>XAMPP MySQL service is running</li>";
                echo "<li>MySQL root user has no password (default XAMPP setup)</li>";
                echo "<li>complete_database_setup.sql file exists</li>";
                echo "</ul>";
            }
        } else {
            // Show setup form
            ?>
            <p>This will create the complete Nexpert.ai database with all tables and default data.</p>
            
            <h3>Prerequisites:</h3>
            <ul>
                <li>✅ XAMPP installed and running</li>
                <li>✅ MySQL service started</li>
                <li>✅ Default MySQL settings (root user, no password)</li>
            </ul>
            
            <h3>What will be created:</h3>
            <ul>
                <li>📁 Database: <code>nexpert_ai</code></li>
                <li>📊 30+ tables for complete functionality</li>
                <li>👤 Default admin user</li>
                <li>🏷️ Default categories (Technology, Business, Design, etc.)</li>
                <li>⚙️ System settings</li>
            </ul>
            
            <form method="POST">
                <button type="submit" name="setup_database" class="btn">
                    🚀 Setup Database Now
                </button>
            </form>
            
            <h3>Manual Setup:</h3>
            <p>If you prefer to setup manually, import the SQL file using phpMyAdmin:</p>
            <ol>
                <li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>
                <li>Create new database named <code>nexpert_ai</code></li>
                <li>Import the file: <code>complete_database_setup.sql</code></li>
            </ol>
            <?php
        }
        ?>
    </div>
</body>
</html>