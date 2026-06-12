<?php
// Zoom API Helper - Create meetings using Server-to-Server OAuth

require_once __DIR__ . '/universal-env.php';

class ZoomHelper {
    private $accountId;
    private $clientId;
    private $clientSecret;
    private $accessToken = null;
    
    public function __construct() {
        $this->accountId = UniversalEnv::get('ZOOM_ACCOUNT_ID');
        $this->clientId = UniversalEnv::get('ZOOM_CLIENT_ID');
        $this->clientSecret = UniversalEnv::get('ZOOM_CLIENT_SECRET');
        
        // Debug logging
        error_log("Zoom credentials loaded - Account ID: " . ($this->accountId ? 'Present' : 'Missing'));
        error_log("Zoom credentials loaded - Client ID: " . ($this->clientId ? 'Present' : 'Missing'));
        error_log("Zoom credentials loaded - Client Secret: " . ($this->clientSecret ? 'Present' : 'Missing'));
    }
    
    // Get OAuth access token
    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        $url = "https://zoom.us/oauth/token?grant_type=account_credentials&account_id={$this->accountId}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Detailed logging
        error_log("Zoom OAuth Request URL: $url");
        error_log("Zoom OAuth HTTP Code: $httpCode");
        error_log("Zoom OAuth Response: $response");
        if ($curlError) {
            error_log("Zoom OAuth cURL Error: $curlError");
        }
        
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                error_log("Zoom OAuth Success - Token obtained");
                return $this->accessToken;
            } else {
                error_log("Zoom OAuth Error - No access_token in response: " . json_encode($data));
            }
        }
        
        // Return the actual Zoom API error response
        $errorData = json_decode($response, true);
        if ($errorData && isset($errorData['error'])) {
            return ['error' => $errorData['error'], 'reason' => $errorData['reason'] ?? 'Unknown', 'http_code' => $httpCode];
        }
        
        return ['error' => 'Failed to get access token', 'http_code' => $httpCode, 'response' => $response];
    }
    
    // Create a Zoom meeting
    public function createMeeting($topic, $startTime, $duration, $agenda = '') {
        $token = $this->getAccessToken();
        
        // Check if token retrieval returned an error
        if (is_array($token) && isset($token['error'])) {
            return $token; // Return the detailed error
        }
        
        if (!$token) {
            return ['error' => 'Failed to get Zoom access token'];
        }
        
        // Use 'me' as userId for Server-to-Server OAuth
        $url = 'https://api.zoom.us/v2/users/me/meetings';
        
        $meetingData = [
            'topic' => $topic,
            'type' => 2, // Scheduled meeting
            'start_time' => $startTime, // Format: 2024-10-17T10:00:00Z
            'duration' => $duration, // Duration in minutes
            'timezone' => 'Asia/Kolkata',
            'agenda' => $agenda,
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'join_before_host' => true,
                'mute_upon_entry' => false,
                'waiting_room' => false,
                'audio' => 'both',
                'auto_recording' => 'none'
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meetingData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 201) {
            $meeting = json_decode($response, true);
            return [
                'success' => true,
                'meeting_id' => $meeting['id'],
                'join_url' => $meeting['join_url'],
                'start_url' => $meeting['start_url'],
                'password' => $meeting['password'] ?? '',
                'meeting_data' => $meeting
            ];
        }
        
        return [
            'error' => 'Failed to create Zoom meeting',
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
}
