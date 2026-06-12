<?php
/**
 * OpenAI API Helper
 * Provides functions to interact with OpenAI API
 */

// Load environment variables
require_once __DIR__ . '/env-loader.php';

// OpenAI API Configuration - prioritize .env file over Replit secrets
$apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? '';
define('OPENAI_API_KEY', $apiKey);
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');

/**
 * Generate program description using OpenAI
 * 
 * @param string $programIdea The user's program idea
 * @return array Response with success status and generated content
 */
function generateProgramDescription($programIdea) {
    // Validate API key exists
    if (empty(OPENAI_API_KEY)) {
        error_log('OpenAI API Key not configured');
        return [
            'success' => false,
            'message' => 'AI service not configured. Please contact administrator.'
        ];
    }

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ];

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant that creates detailed learning program descriptions for an educational platform. Given a program idea, generate a comprehensive program with title, description, and learning objectives. Keep the tone professional and educational.'
            ],
            [
                'role' => 'user',
                'content' => "Create a learning program based on this idea: {$programIdea}\n\nProvide the response in this exact JSON format:\n{\n  \"title\": \"Program Title\",\n  \"description\": \"Detailed program description (2-3 paragraphs)\",\n  \"duration_weeks\": 8,\n  \"price_inr\": 15000,\n  \"milestones\": [\n    {\"title\": \"Milestone 1\", \"week\": 1, \"deliverable\": \"What to deliver\"},\n    {\"title\": \"Milestone 2\", \"week\": 3, \"deliverable\": \"What to deliver\"}\n  ],\n  \"assignments\": [\n    {\"title\": \"Assignment 1\", \"type\": \"project\", \"description\": \"Assignment description\"},\n    {\"title\": \"Assignment 2\", \"type\": \"quiz\", \"description\": \"Assignment description\"}\n  ]\n}\n\nGuidelines:\n- duration_weeks: Estimate realistic weeks needed (typically 4-12 weeks)\n- price_inr: Set competitive price in Indian Rupees based on content depth (5000-25000)\n- Create 3-5 milestones with specific deliverables\n- Create 2-4 assignments (types: project, quiz, reading, or practical)"
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 1000
    ];

    $ch = curl_init(OPENAI_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log('OpenAI API cURL Error: ' . $curlError);
        return [
            'success' => false,
            'message' => 'Failed to connect to AI service: ' . $curlError
        ];
    }

    if ($httpCode !== 200) {
        error_log('OpenAI API HTTP Error: ' . $httpCode);
        error_log('OpenAI API Response: ' . $response);
        return [
            'success' => false,
            'message' => 'AI service returned error code: ' . $httpCode
        ];
    }

    $result = json_decode($response, true);

    if (!isset($result['choices'][0]['message']['content'])) {
        error_log('OpenAI API Invalid Response: ' . $response);
        return [
            'success' => false,
            'message' => 'Invalid response from AI service'
        ];
    }

    $content = $result['choices'][0]['message']['content'];
    
    // Extract JSON from the response (in case it's wrapped in markdown)
    if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
        $content = $matches[1];
    }

    $programData = json_decode($content, true);

    if (!$programData) {
        error_log('Failed to parse AI response as JSON: ' . $content);
        return [
            'success' => false,
            'message' => 'Failed to parse AI response'
        ];
    }

    return [
        'success' => true,
        'data' => $programData
    ];
}
