<?php
/**
 * Generate Program Description using AI
 * POST endpoint that accepts a program idea and returns AI-generated program details
 */

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/openai-helper.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Ensure POST method is used
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Method Not Allowed');
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['idea']) || empty(trim($input['idea']))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Program idea is required'
        ]);
        exit;
    }

    $programIdea = trim($input['idea']);

    error_log('Generating program from AI for idea: ' . $programIdea);

    // Generate program description using OpenAI
    $result = generateProgramDescription($programIdea);

    if ($result['success']) {
        error_log('AI Program Generation Success: ' . json_encode($result['data']));
        echo json_encode([
            'success' => true,
            'data' => $result['data']
        ]);
    } else {
        error_log('AI Program Generation Failed: ' . $result['message']);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }

} catch (Exception $e) {
    error_log('Generate Program AI API Error: ' . $e->getMessage());
    error_log('Trace: ' . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ]);
}
