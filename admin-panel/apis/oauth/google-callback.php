<?php
/**
 * Google OAuth Callback Handler
 * Processes Google OAuth authentication for both learner and expert panels
 */

// Load session configuration
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/connection/pdo.php';
require_once dirname(__DIR__) . '/connection/google-oauth-helper.php';

// Get the current domain for redirect URI
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$redirectUri = $protocol . '://' . $host . BASE_PATH . '/admin-panel/apis/oauth/google-callback.php';

// Initialize OAuth helper
$oauth = new GoogleOAuthHelper($redirectUri);

// Check if credentials are configured
if (!$oauth->credentialsConfigured()) {
    die('Google OAuth is not configured. Please contact support.');
}

// Verify state parameter to prevent CSRF (REQUIRED)
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state'])) {
    error_log('Google OAuth: Missing state parameter or session state');
    die('Invalid request. Missing state parameter.');
}

if ($_GET['state'] !== $_SESSION['oauth_state']) {
    error_log('Google OAuth: State mismatch - Possible CSRF attack');
    error_log('Expected: ' . $_SESSION['oauth_state'] . ', Got: ' . $_GET['state']);
    die('Invalid state parameter. Possible CSRF attack.');
}

// Store state but don't clear yet - clear after successful authentication
$validatedState = $_SESSION['oauth_state'];

// Check for authorization code
if (!isset($_GET['code'])) {
    error_log('Google OAuth: No authorization code received');
    die('Authorization code not received from Google.');
}

// Get user role from session
$userRole = $_SESSION['oauth_role'] ?? 'learner';

try {
    // Exchange authorization code for access token
    $tokenData = $oauth->getAccessToken($_GET['code']);
    
    if (!$tokenData || !isset($tokenData['access_token'])) {
        error_log('Google OAuth: Failed to get access token');
        die('Failed to authenticate with Google. Please try again.');
    }

    // Get user information from Google
    $userInfo = $oauth->getUserInfo($tokenData['access_token']);
    
    if (!$userInfo || !isset($userInfo['email'])) {
        error_log('Google OAuth: Failed to get user info');
        die('Failed to retrieve user information from Google.');
    }

    // Extract user data
    $email = $userInfo['email'];
    $name = $userInfo['name'] ?? '';
    $googleId = $userInfo['id'] ?? '';
    $picture = $userInfo['picture'] ?? '';

    // Check if user exists in database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // User exists - log them in
        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['role'] = $existingUser['role'];
        $_SESSION['email'] = $existingUser['email'];

        // Update Google ID if not set
        if (empty($existingUser['google_id'])) {
            $updateStmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $updateStmt->execute([$googleId, $existingUser['id']]);
        }

        // Clear OAuth session data and regenerate session ID for security
        unset($_SESSION['oauth_state']);
        unset($_SESSION['oauth_role']);
        session_regenerate_id(true);

        // Redirect based on role
        if ($existingUser['role'] === 'expert') {
            header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=dashboard');
        } else {
            header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=dashboard');
        }
        exit;

    } else {
        // New user - create account
        $pdo->beginTransaction();

        try {
            // Create user account
            $insertUser = $pdo->prepare("
                INSERT INTO users (email, password_hash, role, email_verified, google_id, created_at)
                VALUES (?, ?, ?, 1, ?, NOW())
            ");
            
            // Use a random password since they're logging in with Google
            $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);
            
            if (!$insertUser->execute([$email, $randomPassword, $userRole, $googleId])) {
                throw new Exception('Failed to insert user: ' . json_encode($insertUser->errorInfo()));
            }
            
            $userId = $pdo->lastInsertId();
            
            if (!$userId) {
                throw new Exception('Failed to get user ID after insert');
            }

            // Create profile based on role
            if ($userRole === 'expert') {
                // Check if expert_profiles table exists and get its columns
                $insertProfile = $pdo->prepare("
                    INSERT INTO expert_profiles (user_id, full_name, created_at)
                    VALUES (?, ?, NOW())
                ");
                
                if (!$insertProfile->execute([$userId, $name])) {
                    throw new Exception('Failed to insert expert profile: ' . json_encode($insertProfile->errorInfo()));
                }
            } else {
                // Create learner profile
                $insertProfile = $pdo->prepare("
                    INSERT INTO learner_profiles (user_id, full_name, created_at)
                    VALUES (?, ?, NOW())
                ");
                
                if (!$insertProfile->execute([$userId, $name])) {
                    throw new Exception('Failed to insert learner profile: ' . json_encode($insertProfile->errorInfo()));
                }
            }

            $pdo->commit();

            // Set session
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $userRole;
            $_SESSION['email'] = $email;

            // Clear OAuth session data and regenerate session ID for security
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_role']);
            session_regenerate_id(true);

            // Redirect to appropriate dashboard
            if ($userRole === 'expert') {
                header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=settings#profile');
            } else {
                header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=dashboard');
            }
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errorDetails = 'Database error: ' . $e->getMessage();
            error_log('Google OAuth Registration Failed: ' . $errorDetails);
            error_log('User data: email=' . $email . ', role=' . $userRole . ', name=' . $name);
            die('Failed to create account. Database error: ' . htmlspecialchars($e->getMessage()) . '<br><br>Please contact support or try again later.');
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Google OAuth Registration Failed: ' . $e->getMessage());
            error_log('User data: email=' . $email . ', role=' . $userRole . ', name=' . $name);
            die('Failed to create account. Error: ' . htmlspecialchars($e->getMessage()) . '<br><br>Please contact support or try again later.');
        }
    }

} catch (Exception $e) {
    error_log('Google OAuth: Exception - ' . $e->getMessage());
    die('An error occurred during authentication. Please try again.');
}
