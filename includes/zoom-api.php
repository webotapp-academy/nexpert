<?php
/**
 * Zoom API Helper (Server-to-Server OAuth)
 *
 * NOTE: Populate the environment variables or replace placeholders below:
 * ZOOM_ACCOUNT_ID, ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET
 * For security DO NOT hardcode real secrets in repository.
 */

// Basic guard to prevent direct access disclosure of secrets
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    exit('Forbidden');
}

function zoom_get_credentials(): array {
    return [
        'account_id' => getenv('ZOOM_ACCOUNT_ID') ?: 'YOUR_ZOOM_ACCOUNT_ID',
        'client_id' => getenv('ZOOM_CLIENT_ID') ?: 'YOUR_ZOOM_CLIENT_ID',
        'client_secret' => getenv('ZOOM_CLIENT_SECRET') ?: 'YOUR_ZOOM_CLIENT_SECRET',
    ];
}

/**
 * Fetch access token using Server-to-Server OAuth
 * Caches token in $_SESSION for its lifetime to reduce API calls.
 */
function zoom_get_access_token(): ?string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_SESSION['zoom_token']) && !empty($_SESSION['zoom_token_expires']) && time() < $_SESSION['zoom_token_expires']) {
        return $_SESSION['zoom_token'];
    }

    $creds = zoom_get_credentials();
    if (strpos($creds['account_id'], 'YOUR_') === 0) {
        error_log('Zoom credentials not set.');
        return null;
    }

    $url = 'https://zoom.us/oauth/token';
    $params = http_build_query([
        'grant_type' => 'account_credentials',
        'account_id' => $creds['account_id']
    ]);

    $authHeader = base64_encode($creds['client_id'] . ':' . $creds['client_secret']);

    $ch = curl_init($url . '?' . $params);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . $authHeader,
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ''
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($response === false) {
        error_log('Zoom token curl error: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if ($httpCode !== 200) {
        error_log('Zoom token error (' . $httpCode . '): ' . $response);
        return null;
    }

    $accessToken = $data['access_token'] ?? null;
    $expiresIn = $data['expires_in'] ?? 0;

    if ($accessToken) {
        $_SESSION['zoom_token'] = $accessToken;
        $_SESSION['zoom_token_expires'] = time() + (int)$expiresIn - 30; // 30s buffer
    }

    return $accessToken;
}

/**
 * Create a Zoom meeting for a booking.
 * @param array $booking associative booking row
 * @param string $hostUserId Zoom user ID or email who will host (could store in expert profile)
 */
function zoom_create_meeting(array $booking, string $hostUserId, string $topic = 'Session Meeting'): ?array {
    $token = zoom_get_access_token();
    if (!$token) return null;

    $startTimeUtc = (new DateTime($booking['session_datetime'], new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');

    $payload = [
        'topic' => $topic,
        'type' => 2,
        'start_time' => $startTimeUtc,
        'duration' => (int)$booking['duration_minutes'],
        'timezone' => 'UTC',
        'settings' => [
            'join_before_host' => true,
            'waiting_room' => false,
            'approval_type' => 2,
            'audio' => 'voip',
            'auto_recording' => 'none'
        ]
    ];

    $ch = curl_init('https://api.zoom.us/v2/users/' . urlencode($hostUserId) . '/meetings');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($response === false) {
        error_log('Zoom create meeting curl error: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if ($httpCode !== 201) {
        error_log('Zoom create meeting error (' . $httpCode . '): ' . $response);
        return null;
    }
    return $data;
}

?>
