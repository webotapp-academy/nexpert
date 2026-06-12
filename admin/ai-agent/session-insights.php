<?php
// Load domain path configuration
$base_path = require_once dirname(dirname(__DIR__)) . '/apis/connection/domain-path.php';

// Central session + config
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Load .env file if exists
$envFile = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!empty($key)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// OpenAI API Configuration
$openai_api_key = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? null;

if (empty($openai_api_key)) {
    echo json_encode([
        'success' => false,
        'message' => 'OpenAI API key not configured. Please contact administrator.'
    ]);
    exit;
}

if ($action === 'generate_expert_insights') {
    $bookingId = $_POST['booking_id'] ?? null;
    $forceRefresh = $_POST['force_refresh'] ?? false;
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID required']);
        exit;
    }
    
    // Verify booking belongs to this learner and get expert_id
    $stmt = $pdo->prepare("SELECT id, expert_id, ai_insights FROM bookings WHERE id = ? AND learner_id = ?");
    $stmt->execute([$bookingId, $userId]);
    $currentBooking = $stmt->fetch();
    
    if (!$currentBooking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    
    $expertId = $currentBooking['expert_id'];
    
    // If NOT force refresh, check for cached insights
    if (!$forceRefresh) {
        // Check if this learner has any previous booking with the same expert that has insights
        $stmt = $pdo->prepare("
            SELECT ai_insights 
            FROM bookings 
            WHERE learner_id = ? 
            AND expert_id = ? 
            AND id != ?
            AND ai_insights IS NOT NULL 
            AND ai_insights != ''
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId, $expertId, $bookingId]);
        $previousBooking = $stmt->fetch();
        
        // If previous insights exist for this learner-expert pair, reuse them
        if ($previousBooking && !empty($previousBooking['ai_insights'])) {
            $cachedInsights = json_decode($previousBooking['ai_insights'], true);
            
            if ($cachedInsights) {
                // Store same insights in current booking
                $stmt = $pdo->prepare("UPDATE bookings SET ai_insights = ? WHERE id = ?");
                $stmt->execute([$previousBooking['ai_insights'], $bookingId]);
                
                echo json_encode([
                    'success' => true,
                    'insights' => $cachedInsights,
                    'cached' => true
                ]);
                exit;
            }
        }
        
        // If current booking already has insights, return them
        if (!empty($currentBooking['ai_insights'])) {
            $existingInsights = json_decode($currentBooking['ai_insights'], true);
            if ($existingInsights) {
                echo json_encode([
                    'success' => true,
                    'insights' => $existingInsights,
                    'cached' => true
                ]);
                exit;
            }
        }
    }
    
    // Get expert profile data
    $stmt = $pdo->prepare("
        SELECT 
            b.session_topic,
            b.duration_minutes,
            ep.full_name,
            ep.bio_short,
            ep.bio_full,
            ep.tagline,
            ep.experience_years,
            ep.expertise_verticals,
            ep.category,
            ep.tags,
            ep.credentials
        FROM bookings b
        JOIN users u ON b.expert_id = u.id
        JOIN expert_profiles ep ON u.id = ep.user_id
        WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    $expert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$expert) {
        echo json_encode(['success' => false, 'message' => 'Expert profile not found']);
        exit;
    }
    
    // Build AI prompt
    $prompt = "You are an AI assistant helping a learner understand their expert mentor better. Based on the expert's profile, provide personalized insights.\n\n";
    $prompt .= "Expert Profile:\n";
    $prompt .= "- Name: " . ($expert['full_name'] ?? 'N/A') . "\n";
    $prompt .= "- Tagline: " . ($expert['tagline'] ?? 'N/A') . "\n";
    $prompt .= "- Category: " . ($expert['category'] ?? 'N/A') . "\n";
    $prompt .= "- Years of Experience: " . ($expert['experience_years'] ?? 'N/A') . "\n";
    $prompt .= "- Bio Short: " . ($expert['bio_short'] ?? 'N/A') . "\n";
    $prompt .= "- Bio Full: " . ($expert['bio_full'] ?? 'N/A') . "\n";
    $prompt .= "- Expertise Verticals: " . ($expert['expertise_verticals'] ?? 'N/A') . "\n";
    $prompt .= "- Tags: " . ($expert['tags'] ?? 'N/A') . "\n";
    $prompt .= "- Credentials: " . ($expert['credentials'] ?? 'N/A') . "\n\n";
    $prompt .= "Session Topic: " . ($expert['session_topic'] ?? 'General Session') . "\n";
    $prompt .= "Session Duration: " . ($expert['duration_minutes'] ?? '30') . " minutes\n\n";
    
    $prompt .= "Please provide insights in the following format (use JSON structure):\n\n";
    $prompt .= "{\n";
    $prompt .= '  "overview": "A comprehensive 2-3 sentence overview about the expert\'s background, expertise, and what makes them valuable as a mentor. Focus on their experience and specializations.",'. "\n";
    $prompt .= '  "session_goals": "Based on the session topic and expert\'s expertise, describe what the learner can expect to achieve in this session. Be specific and actionable (2-3 sentences).",'. "\n";
    $prompt .= '  "recommended_approach": "Provide 4-5 HIGHLY SPECIFIC tips on how THIS TYPE of expert can help learners. Focus on the UNIQUE VALUE this expert category provides. Examples:\n';
    $prompt .= '     - If DEVELOPER: Focus on technical guidance (code review, tech stack selection, architecture decisions, debugging strategies, development tools)\n';
    $prompt .= '     - If DIGITAL MARKETER: Focus on marketing help (campaign strategy, SEO/SEM, social media, analytics, ROI optimization, content marketing)\n';
    $prompt .= '     - If DESIGNER: Focus on design guidance (UI/UX principles, design tools, portfolio building, design critique, visual trends)\n';
    $prompt .= '     - If BUSINESS CONSULTANT: Focus on business advice (strategy, market analysis, financial planning, growth tactics, operations)\n';
    $prompt .= '     - If DATA SCIENTIST: Focus on data expertise (data analysis, ML models, statistical methods, data visualization, tools/platforms)\n';
    $prompt .= '     - If CAREER COACH: Focus on career development (resume building, interview prep, job search, networking, skill development)\n';
    $prompt .= '     Make recommendations ACTIONABLE and SPECIFIC to what this expert category can uniquely provide. Format as numbered list."'. "\n";
    $prompt .= "}\n\n";
    $prompt .= "CRITICAL: In 'recommended_approach', analyze the expert's CATEGORY and provide tips that are PERFECTLY MATCHED to what THIS TYPE of expert specializes in. Be concrete and practical.";

    
    // Call OpenAI API
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai_api_key
    ]);
    
    $requestData = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an AI assistant specialized in educational mentorship and learner success. Provide clear, actionable, and encouraging insights.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 800
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to generate insights. OpenAI API error.'
        ]);
        exit;
    }
    
    $result = json_decode($response, true);
    $aiResponse = $result['choices'][0]['message']['content'] ?? '';
    
    // Extract JSON from response
    preg_match('/\{[\s\S]*\}/', $aiResponse, $matches);
    if (empty($matches)) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to parse AI response'
        ]);
        exit;
    }
    
    $insights = json_decode($matches[0], true);
    
    if (!$insights) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to parse insights'
        ]);
        exit;
    }
    
    // Store insights in booking record (as JSON)
    $stmt = $pdo->prepare("UPDATE bookings SET ai_insights = ? WHERE id = ?");
    $stmt->execute([json_encode($insights), $bookingId]);
    
    echo json_encode([
        'success' => true,
        'insights' => $insights
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;
