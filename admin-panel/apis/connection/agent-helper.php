<?php
/**
 * Agent Helper
 * Handles LLM interactions for the Trust System Agents
 */

require_once __DIR__ . '/env-loader.php';

// OpenAI API Configuration
$apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? '';
define('AGENT_OPENAI_API_KEY', $apiKey);
define('AGENT_OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');

class AgentHelper
{

    /**
     * Extract trust signals from an event using LLM
     * 
     * @param string $agentType The type of agent (structure, outcome, boundary, consistency)
     * @param array $eventData Data about the event
     * @return array|null Extracted signal value and metadata
     */
    public static function extractSignal($agentType, $eventData)
    {
        if (empty(AGENT_OPENAI_API_KEY)) {
            error_log('AgentHelper: OpenAI API Key not configured');
            return null;
        }

        $prompts = [
            'structure' => "Analyze the following expert event for 'Structure' quality. Structure refers to how well organized the session/profile/interaction is. Output a score from 0 to 100 and a brief justification.",
            'outcome' => "Analyze the following expert event for 'Outcome' quality. Outcome refers to the tangible results or learning achieved. Output a score from 0 to 100 and a brief justification.",
            'boundary' => "Analyze the following expert event for 'Boundary' compliance. Boundary refers to professionalism, time management, and ethical conduct. Output a score from 0 to 100 and a brief justification.",
            'consistency' => "Analyze the following expert event for 'Consistency'. Consistency refers to how this event aligns with past performance. Output a score from 0 to 100 and a brief justification."
        ];

        $systemPrompt = $prompts[$agentType] ?? "Analyze this event for trust signals. Output JSON: { \"score\": 0-100, \"justification\": \"...\" }";

        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt . " Always return valid JSON."],
                ['role' => 'user', 'content' => json_encode($eventData)]
            ],
            'temperature' => 0.3,
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init(AGENT_OPENAI_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AGENT_OPENAI_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("AgentHelper Error: LLM request failed with code $httpCode. Response: $response");
            return null;
        }

        $result = json_decode($response, true);
        $content = json_decode($result['choices'][0]['message']['content'], true);

        return [
            'signal_value' => $content['score'] ?? 50,
            'metadata' => [
                'justification' => $content['justification'] ?? '',
                'agent_type' => $agentType,
                'model' => 'gpt-4o-mini'
            ]
        ];
    }
}
