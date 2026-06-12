<?php
// Simple debug check for AI insights API
session_start();

// Set test session for debugging
$_SESSION['user_id'] = 19; // Set a test learner ID
$_SESSION['role'] = 'learner';

echo "<h1>AI Insights Debug Test</h1>";

// Test 1: Check if API file exists
$api_file = '/Applications/XAMPP/xamppfiles/htdocs/name/admin-panel/apis/learner/session-insights.php';
echo "<h2>1. API File Check:</h2>";
echo file_exists($api_file) ? "✅ API file exists" : "❌ API file missing";
echo "<br><br>";

// Test 2: Check database connection
echo "<h2>2. Database Connection:</h2>";
try {
    require_once '/Applications/XAMPP/xamppfiles/htdocs/name/admin-panel/apis/connection/pdo.php';
    echo "✅ Database connected successfully<br>";
    
    // Check if booking exists
    $stmt = $pdo->prepare("SELECT id, expert_id, learner_id FROM bookings WHERE id = ?");
    $stmt->execute([43]);
    $booking = $stmt->fetch();
    
    if ($booking) {
        echo "✅ Booking 43 found - Expert: {$booking['expert_id']}, Learner: {$booking['learner_id']}<br>";
    } else {
        echo "❌ Booking 43 not found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 3: Check OpenAI API key
echo "<h2>3. OpenAI API Key Check:</h2>";
$envFile = '/Applications/XAMPP/xamppfiles/htdocs/name/.env';
if (file_exists($envFile)) {
    echo "✅ .env file exists<br>";
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $api_key_found = false;
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, 'OPENAI_API_KEY') !== false) {
            $api_key_found = true;
            $parts = explode('=', $line, 2);
            $key = trim($parts[1] ?? '');
            if (!empty($key)) {
                echo "✅ OpenAI API key found (length: " . strlen($key) . ")<br>";
            } else {
                echo "❌ OpenAI API key empty<br>";
            }
            break;
        }
    }
    
    if (!$api_key_found) {
        echo "❌ OpenAI API key not found in .env<br>";
    }
} else {
    echo "❌ .env file not found<br>";
}
echo "<br>";

// Test 4: Direct API call
echo "<h2>4. Direct API Call Test:</h2>";
echo '<button onclick="testDirectAPI()">Test API Call</button>';
echo '<div id="api-result" style="margin-top: 10px; padding: 10px; border: 1px solid #ccc;"></div>';

?>

<script>
async function testDirectAPI() {
    const result = document.getElementById('api-result');
    result.innerHTML = 'Testing API call...';
    
    try {
        const response = await fetch('/name/admin-panel/apis/learner/session-insights.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'generate_expert_insights',
                booking_id: '43',
                force_refresh: '1'
            })
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers]);
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        result.innerHTML = `
            <h4>Response Status: ${response.status}</h4>
            <h4>Raw Response:</h4>
            <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;">${text}</pre>
        `;
        
        try {
            const data = JSON.parse(text);
            result.innerHTML += `
                <h4>Parsed JSON:</h4>
                <pre style="background: #e8f5e8; padding: 10px; overflow-x: auto;">${JSON.stringify(data, null, 2)}</pre>
            `;
        } catch (e) {
            result.innerHTML += `<h4>JSON Parse Error: ${e.message}</h4>`;
        }
        
    } catch (error) {
        console.error('Fetch error:', error);
        result.innerHTML = `<h4>Error:</h4><p style="color: red;">${error.message}</p>`;
    }
}
</script>