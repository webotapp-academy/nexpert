<?php
/**
 * Agent Helper — MVP2
 * LLM interactions for Trust Signal extraction.
 *
 * WHAT CHANGED:
 * - Retry with exponential backoff (3 attempts)
 * - Deterministic fallback if OpenAI unavailable
 * - Cleaner error logging
 *
 * WHAT IS UNCHANGED:
 * - All 4 agent prompts (structure/outcome/boundary/consistency)
 * - OpenAI model: gpt-4o-mini
 * - Output format: signal_value 0-100 + justification
 */

require_once __DIR__ . '/env-loader.php';

$apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? '';
define('AGENT_OPENAI_API_KEY', $apiKey);
define('AGENT_OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');

class AgentHelper {

    private static $prompts = [
        'structure' => "Analyze this expert event for 'Structure' quality. Structure means how well organized, clear, and professional the session or interaction is. Score 0-100 and give a brief justification.",
        'outcome'   => "Analyze this expert event for 'Outcome' quality. Outcome means tangible results, learning achieved, or goals progressed. Score 0-100 and give a brief justification.",
        'boundary'  => "Analyze this expert event for 'Boundary' compliance. Boundary means professionalism, punctuality, ethics, and time management. Score 0-100 and give a brief justification.",
        'consistency' => "Analyze this expert event for 'Consistency'. Consistency means how well this event aligns with and continues the expert's past performance pattern. Score 0-100 and give a brief justification.",
    ];

    // Deterministic fallback scores when OpenAI is unavailable
    private static $fallbackScores = [
        'session_completed'      => ['structure'=>70,'outcome'=>65,'boundary'=>75,'consistency'=>70],
        'feedback_submitted'     => ['structure'=>60,'outcome'=>70,'boundary'=>65,'consistency'=>65],
        'kyc_verified'           => ['structure'=>80,'outcome'=>60,'boundary'=>85,'consistency'=>70],
        'complaint_logged'       => ['structure'=>30,'outcome'=>25,'boundary'=>20,'consistency'=>30],
        'booking_created'        => ['structure'=>65,'outcome'=>60,'boundary'=>70,'consistency'=>65],
        'outcome_achieved'       => ['structure'=>80,'outcome'=>90,'boundary'=>80,'consistency'=>80],
        'goal_completed'         => ['structure'=>75,'outcome'=>88,'boundary'=>78,'consistency'=>75],
        'repeat_booking'         => ['structure'=>70,'outcome'=>72,'boundary'=>75,'consistency'=>80],
        'session_no_show'        => ['structure'=>20,'outcome'=>15,'boundary'=>10,'consistency'=>20],
        'late_start'             => ['structure'=>50,'outcome'=>55,'boundary'=>40,'consistency'=>50],
    ];

    public static function extractSignal(string $agentType, array $eventData): ?array {
        // Try OpenAI with retry
        if (!empty(AGENT_OPENAI_API_KEY)) {
            $result = self::callOpenAI($agentType, $eventData);
            if ($result !== null) return $result;
        }

        // Deterministic fallback
        return self::fallback($agentType, $eventData);
    }

    private static function callOpenAI(string $agentType, array $eventData): ?array {
        $systemPrompt = (self::$prompts[$agentType] ?? "Analyze this event for trust signals.")
            . " Always return valid JSON: {\"score\": 0-100, \"justification\": \"...\"}";

        $payload = [
            'model'           => 'gpt-4o-mini',
            'messages'        => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => json_encode($eventData)],
            ],
            'temperature'     => 0.3,
            'response_format' => ['type' => 'json_object'],
        ];

        $maxAttempts = 3;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $ch = curl_init(AGENT_OPENAI_API_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . AGENT_OPENAI_API_KEY,
                ],
                CURLOPT_TIMEOUT        => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $result  = json_decode($response, true);
                $content = json_decode($result['choices'][0]['message']['content'] ?? '{}', true);
                if (isset($content['score'])) {
                    return [
                        'signal_value' => (float)$content['score'],
                        'metadata'     => [
                            'justification' => $content['justification'] ?? '',
                            'agent_type'    => $agentType,
                            'model'         => 'gpt-4o-mini',
                            'attempt'       => $attempt,
                        ],
                    ];
                }
            }

            // Retry with exponential backoff: 2s, 4s
            if ($attempt < $maxAttempts) {
                error_log("AgentHelper: Attempt {$attempt} failed (HTTP {$httpCode}). Retrying...");
                sleep($attempt * 2);
            } else {
                error_log("AgentHelper: All {$maxAttempts} attempts failed. Using fallback. Error: {$curlErr}");
            }
        }

        return null;
    }

    private static function fallback(string $agentType, array $eventData): array {
        $eventType     = $eventData['event_type'] ?? 'session_completed';
        $fallbackTable = self::$fallbackScores[$eventType] ?? self::$fallbackScores['session_completed'];
        $score         = $fallbackTable[$agentType] ?? 50;

        return [
            'signal_value' => (float)$score,
            'metadata'     => [
                'justification' => "Deterministic fallback score for {$agentType} on {$eventType} (OpenAI unavailable).",
                'agent_type'    => $agentType,
                'model'         => 'fallback',
                'is_fallback'   => true,
            ],
        ];
    }
}
