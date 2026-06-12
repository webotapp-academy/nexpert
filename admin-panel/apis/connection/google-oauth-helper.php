<?php
/**
 * Google OAuth Helper Class
 * Handles Google OAuth 2.0 authentication flow
 */

require_once __DIR__ . '/universal-env.php';

class GoogleOAuthHelper {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    private $tokenUrl = 'https://oauth2.googleapis.com/token';
    private $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

    public function __construct($redirectUri) {
        $this->clientId = UniversalEnv::get('GOOGLE_CLIENT_ID');
        $this->clientSecret = UniversalEnv::get('GOOGLE_CLIENT_SECRET');
        $this->redirectUri = $redirectUri;

        if (!$this->clientId || !$this->clientSecret) {
            error_log('Google OAuth credentials not found in environment');
        }
    }

    /**
     * Generate Google OAuth login URL
     */
    public function getAuthUrl($state = null) {
        if (!$state) {
            $state = bin2hex(random_bytes(16));
        }

        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'state' => $state,
            'prompt' => 'select_account'
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken($authCode) {
        $params = [
            'code' => $authCode,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('Google OAuth token request failed: ' . $response);
            return null;
        }

        $result = json_decode($response, true);
        return $result;
    }

    /**
     * Get user information from Google
     */
    public function getUserInfo($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('Google OAuth user info request failed: ' . $response);
            return null;
        }

        $userInfo = json_decode($response, true);
        return $userInfo;
    }

    /**
     * Verify credentials are configured
     */
    public function credentialsConfigured() {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }
}
