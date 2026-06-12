<?php
// Load domain path configuration
$base_path = require_once dirname(dirname(__DIR__)) . '/apis/connection/domain-path.php';

require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
header('Content-Type: application/json');

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);
$idea = trim($input['idea'] ?? '');

if (empty($idea)) {
    echo json_encode(['success' => false, 'message' => 'Webinar idea is required']);
    exit;
}

// OpenAI API Configuration
$apiKey = 'sk-proj-' . 'Rzk4O-chSpp2kMIPWdECewoJZ02_KahUk3MqHm-zeNGbNsv9HDmejzOOHXaDWQfK86hsBVvxgVT3BlbkFJMo-KIZvIMEASHzzHzJfkmIKx1ECGAHVmpQdk7aJyudcqkrrGoKo-440arShuOeTbJLybrSAPoA';
$apiUrl = 'https://api.openai.com/v1/chat/completions';

// Prepare the AI prompt
$systemPrompt = "You are an expert webinar planner helping experts create engaging live webinar sessions. Generate a comprehensive webinar outline based on the expert's idea.

IMPORTANT: Return ONLY valid JSON without any markdown formatting, code blocks, or additional text. The response must be directly parseable as JSON.

Return a JSON object with these exact fields:
{
  \"title\": \"Engaging webinar title (max 100 chars)\",
  \"description\": \"Comprehensive description covering what attendees will learn (200-500 chars)\",
  \"duration_hours\": decimal number (0.5 to 8, typical is 1-2 hours),
  \"price_inr\": integer (0 for free, or suggested price between 99-4999),
  \"suggested_date\": \"YYYY-MM-DD\" (suggest a date 3-7 days from today),
  \"suggested_time\": \"HH:MM\" in 24-hour format (suggest optimal time like 18:00 or 20:00),
  \"max_participants\": integer or null (suggest 50-500 for paid, null for unlimited)
}

Guidelines:
- Title should be clear, specific, and action-oriented
- Description should highlight key takeaways and target audience
- Duration should match content complexity (1-2 hours typical, 0.5 for quick sessions, 3-4 for workshops)
- Price: Free (0) for introductory topics, 299-999 for intermediate, 1499-2999 for advanced/specialized
- Time: Evening slots (18:00-21:00) work best for working professionals
- Max participants: Consider interaction needs - smaller for Q&A heavy, larger for presentation style";

$userPrompt = "Create a webinar outline for: {$idea}";

// Prepare API request
$data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userPrompt]
    ],
    'temperature' => 0.7,
    'max_tokens' => 1000
];

// Make API request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log("OpenAI API Error: HTTP $httpCode - $response");
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate webinar with AI. Please try again.'
    ]);
    exit;
}

$responseData = json_decode($response, true);

if (!isset($responseData['choices'][0]['message']['content'])) {
    error_log("OpenAI API Invalid Response: " . $response);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid response from AI service'
    ]);
    exit;
}

$aiContent = trim($responseData['choices'][0]['message']['content']);

// Remove markdown code blocks if present
$aiContent = preg_replace('/^```json\s*/i', '', $aiContent);
$aiContent = preg_replace('/\s*```$/', '', $aiContent);
$aiContent = trim($aiContent);

// Parse AI response
$webinarData = json_decode($aiContent, true);

if (!$webinarData) {
    error_log("Failed to parse AI response: " . $aiContent);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to parse AI response. Please try again.'
    ]);
    exit;
}

// Validate and set defaults
$webinarData['title'] = $webinarData['title'] ?? 'Untitled Webinar';
$webinarData['description'] = $webinarData['description'] ?? '';
$webinarData['duration_hours'] = floatval($webinarData['duration_hours'] ?? 1.0);
$webinarData['price_inr'] = intval($webinarData['price_inr'] ?? 0);

// Ensure duration is within valid range
if ($webinarData['duration_hours'] < 0.5)
    $webinarData['duration_hours'] = 0.5;
if ($webinarData['duration_hours'] > 8)
    $webinarData['duration_hours'] = 8;

// Ensure price is non-negative
if ($webinarData['price_inr'] < 0)
    $webinarData['price_inr'] = 0;

// Set default date if not provided (3 days from now)
if (empty($webinarData['suggested_date'])) {
    $date = new DateTime();
    $date->modify('+3 days');
    $webinarData['suggested_date'] = $date->format('Y-m-d');
}

// Set default time if not provided
if (empty($webinarData['suggested_time'])) {
    $webinarData['suggested_time'] = '18:00';
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Webinar generated successfully',
    'data' => $webinarData
]);
