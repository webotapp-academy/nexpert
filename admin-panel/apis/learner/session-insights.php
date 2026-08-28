<?php
// Central session + config
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/connection/pdo.php';
require_once dirname(__DIR__) . '/connection/universal-env.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Session check for API
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Load OpenAI key via UniversalEnv
$openai_api_key = UniversalEnv::get('OPENAI_API_KEY');

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
    $prompt = "You are analyzing an EXPERT'S profile to help a learner understand their mentor better. Focus ONLY on the EXPERT'S background, skills, and expertise.\n\n";
    $prompt .= "EXPERT Profile to Analyze:\n";
    $prompt .= "- Expert Name: " . ($expert['full_name'] ?? 'N/A') . "\n";
    $prompt .= "- Expert Tagline: " . ($expert['tagline'] ?? 'N/A') . "\n";
    $prompt .= "- Expert Category: " . ($expert['category'] ?? 'N/A') . "\n";
    $prompt .= "- Expert Years of Experience: " . ($expert['experience_years'] ?? 'N/A') . "\n";
    $prompt .= "- Expert Bio Short: " . ($expert['bio_short'] ?? 'N/A') . "\n";
    $prompt .= "- Expert Bio Full: " . ($expert['bio_full'] ?? 'N/A') . "\n";
    $prompt .= "- Expert Expertise Verticals: " . ($expert['expertise_verticals'] ?? 'N/A') . "\n";
    $prompt .= "- Expert Tags: " . ($expert['tags'] ?? 'N/A') . "\n";
    $prompt .= "- Expert Credentials: " . ($expert['credentials'] ?? 'N/A') . "\n\n";
    $prompt .= "Session Topic: " . ($expert['session_topic'] ?? 'General Session') . "\n";
    $prompt .= "Session Duration: " . ($expert['duration_minutes'] ?? '30') . " minutes\n\n";
    
    $prompt .= "IMPORTANT: Write ONLY about the EXPERT, not the learner. Provide insights about the EXPERT'S capabilities, background, and how THIS SPECIFIC EXPERT can help.\n\n";
    $prompt .= "Please provide insights in the following format (use JSON structure):\n\n";
    $prompt .= "{\n";
    $prompt .= '  "overview": "Write a SHORT 3-4 line summary about THIS EXPERT: their background, expertise areas, years of experience, and what makes them valuable. Keep it concise and focused on key qualifications. Maximum 30-40 words total.",'. "\n";
    $prompt .= '  "session_goals": "Based on THIS EXPERT\'s expertise and session topic, describe what specific value they can provide. Write a SHORT 3-4 line summary about their unique qualifications to help. Maximum 30-40 words total.",'. "\n";
    $prompt .= '  "recommended_approach": "Provide 4-5 SHORT bullet points about what THIS EXPERT can uniquely offer. Each point should be MAXIMUM 8-10 words and start with a bullet (•) or dash (-). Format as a proper numbered list:\n';
    $prompt .= '     1. Point one (8-10 words max)\n';
    $prompt .= '     2. Point two (8-10 words max)\n';
    $prompt .= '     3. Point three (8-10 words max)\n';
    $prompt .= '     4. Point four (8-10 words max)\n';
    $prompt .= '     Examples for different categories:\n';
    $prompt .= '     - DEVELOPER: "1. Code review & debugging expertise\\n2. Modern tech stack knowledge\\n3. Architecture design skills"\n';
    $prompt .= '     - DIGITAL MARKETER: "1. SEO optimization strategies\\n2. Social media campaign management\\n3. Analytics & ROI tracking"\n';
    $prompt .= '     - DESIGNER: "1. UI/UX design principles\\n2. Design tools mastery\\n3. Visual branding expertise"\n';
    $prompt .= '     Write ONLY numbered list format (1. 2. 3. 4.) with line breaks between each point. Each point maximum 8-10 words."'. "\n";
    $prompt .= "}\n\n";
    $prompt .= "CRITICAL: Write EVERYTHING about the EXPERT, not the learner. Focus on the EXPERT's capabilities, experience, and specialized knowledge. Analyze what THIS PARTICULAR EXPERT brings to the table.";

    
    $insights = null;
    
    // Attempt OpenAI API Call if valid key is present
    if (!empty($openai_api_key) && strpos($openai_api_key, 'sk-') === 0 && strlen($openai_api_key) > 20) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openai_api_key
        ]);
        
        $requestData = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI assistant that analyzes EXPERT profiles for learners. Your job is to provide insights ABOUT THE EXPERT - their background, expertise, experience, and capabilities. Always focus on what the EXPERT can offer, not what the learner needs. Write about the EXPERT\'s professional qualifications and specialized knowledge. For the recommended_approach field, ALWAYS format as numbered list with line breaks: "1. Point one\n2. Point two\n3. Point three" - each point should be 8-10 words maximum.'
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
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $aiResponse = $result['choices'][0]['message']['content'] ?? '';
            
            preg_match('/\{[\s\S]*\}/', $aiResponse, $matches);
            if (!empty($matches)) {
                $decoded = json_decode($matches[0], true);
                if (is_array($decoded) && !empty($decoded['overview'])) {
                    $insights = $decoded;
                }
            }
        }
    }
    
    // Fallback: Generate intelligent tailored insights if OpenAI was unavailable/expired
    if (!$insights) {
        $name = !empty($expert['full_name']) ? $expert['full_name'] : 'The Expert';
        $title = !empty($expert['tagline']) ? $expert['tagline'] : (!empty($expert['category']) ? ucfirst($expert['category']) : 'Domain Specialist');
        $years = !empty($expert['experience_years']) ? $expert['experience_years'] . '+ years' : 'established industry experience';
        $topic = !empty($expert['session_topic']) ? $expert['session_topic'] : '1-on-1 Mentorship';
        
        $skillsList = [];
        if (!empty($expert['expertise_verticals'])) {
            $decoded = json_decode($expert['expertise_verticals'], true);
            if (is_array($decoded)) {
                $skillsList = $decoded;
            } else {
                $skillsList = explode(',', $expert['expertise_verticals']);
            }
        }
        $skillsStr = !empty($skillsList) ? implode(', ', array_slice(array_map('trim', $skillsList), 0, 4)) : 'practical problem-solving and industry best practices';

        $overview = "{$name} is a {$title} with {$years} specializing in {$skillsStr}. Known for actionable guidance and real-world execution strategy.";
        
        $goals = "In this session on '{$topic}', {$name} will analyze your goals, diagnose key blockers, and provide structured, high-impact recommendations.";

        $approachPoints = [];
        if (!empty($skillsList)) {
            foreach (array_slice($skillsList, 0, 3) as $i => $sk) {
                $approachPoints[] = ($i + 1) . ". In-depth breakdown of " . trim($sk);
            }
            $approachPoints[] = (count($approachPoints) + 1) . ". Actionable roadmap & next steps";
        } else {
            $approachPoints = [
                "1. Comprehensive review of current status & objectives",
                "2. Identification of strategic growth opportunities",
                "3. Best practice methodologies and frameworks",
                "4. Step-by-step roadmap for immediate execution"
            ];
        }
        
        $recommended_approach = implode("\n", $approachPoints);

        $insights = [
            'overview' => $overview,
            'session_goals' => $goals,
            'recommended_approach' => $recommended_approach
        ];
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
