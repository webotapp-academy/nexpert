<?php
/**
 * Google OAuth Callback Handler
 * Processes Google OAuth authentication for both learner and expert panels
 */

// Load session configuration
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/connection/pdo.php';
require_once dirname(__DIR__) . '/connection/google-oauth-helper.php';

function renderOAuthError($title, $message, $role = 'learner') {
    $loginUrl = BASE_PATH . '/index.php?panel=' . ($role === 'expert' ? 'expert' : 'learner') . '&page=auth';
    ?>
    <!DOCTYPE html>
    <html lang="en" class="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - nexpert</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-[#080B10] text-gray-100 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-[#0D131F] border border-gray-800 rounded-3xl p-8 text-center shadow-2xl">
            <div class="w-16 h-16 bg-red-500/10 border border-red-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4 text-red-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h1 class="text-xl font-black text-white mb-2"><?php echo htmlspecialchars($title); ?></h1>
            <p class="text-gray-400 text-sm mb-6 leading-relaxed"><?php echo htmlspecialchars($message); ?></p>
            <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="inline-block w-full py-3 px-4 bg-[#00D4AA] hover:bg-[#00bda0] text-[#080B10] font-black rounded-xl transition duration-200">
                Return to Login
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Get the current domain for redirect URI
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$redirectUri = $protocol . '://' . $host . BASE_PATH . '/admin-panel/apis/oauth/google-callback.php';

// Initialize OAuth helper
$oauth = new GoogleOAuthHelper($redirectUri);
$userRole = $_SESSION['oauth_role'] ?? 'learner';

// Check if credentials are configured
if (!$oauth->credentialsConfigured()) {
    renderOAuthError('Configuration Missing', 'Google OAuth credentials are not configured on the server. Please add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to the .env file.', $userRole);
}

// Verify state parameter to prevent CSRF
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state'])) {
    renderOAuthError('Session Expired', 'Invalid OAuth request or session expired. Please try signing in again.', $userRole);
}

if ($_GET['state'] !== $_SESSION['oauth_state']) {
    renderOAuthError('Verification Failed', 'Security state mismatch. Please try signing in again.', $userRole);
}

// Check for authorization code
if (!isset($_GET['code'])) {
    $errDesc = $_GET['error_description'] ?? $_GET['error'] ?? 'Authorization code not received from Google.';
    renderOAuthError('Authentication Cancelled', $errDesc, $userRole);
}

try {
    // Exchange authorization code for access token
    $tokenData = $oauth->getAccessToken($_GET['code']);
    
    if (!$tokenData || !isset($tokenData['access_token'])) {
        renderOAuthError('Authentication Error', 'Failed to retrieve access token from Google. Please check your credentials.', $userRole);
    }

    // Get user information from Google
    $userInfo = $oauth->getUserInfo($tokenData['access_token']);
    
    if (!$userInfo || !isset($userInfo['email'])) {
        renderOAuthError('Profile Error', 'Failed to retrieve email profile from Google.', $userRole);
    }

    // Extract user data
    $email = strtolower(trim($userInfo['email']));
    $name = trim($userInfo['name'] ?? '');
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

        // Update Google ID and last login
        $updateStmt = $pdo->prepare("UPDATE users SET google_id = COALESCE(google_id, ?), email_verified = 1, last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$googleId, $existingUser['id']]);

        // Clear OAuth session data and regenerate session ID
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
            $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);
            
            $insertUser = $pdo->prepare("
                INSERT INTO users (email, password, password_hash, role, email_verified, google_id, status, created_at)
                VALUES (?, ?, ?, ?, 1, ?, 'active', NOW())
            ");
            
            if (!$insertUser->execute([$email, $randomPassword, $randomPassword, $userRole, $googleId])) {
                throw new Exception('Failed to insert user');
            }
            
            $userId = $pdo->lastInsertId();
            
            if (!$userId) {
                throw new Exception('Failed to retrieve user ID');
            }

            // Create profile based on role
            if ($userRole === 'expert') {
                $insertProfile = $pdo->prepare("
                    INSERT INTO expert_profiles (user_id, full_name, profile_photo, verification_status, created_at)
                    VALUES (?, ?, ?, 'approved', NOW())
                ");
                $insertProfile->execute([$userId, $name ?: 'Expert', $picture ?: null]);
                
                // Initialize default trust state
                $trustStmt = $pdo->prepare("
                    INSERT IGNORE INTO trust_state (expert_id, overall_score, trust_tier, band_name, credibility_score, performance_score, reliability_score, updated_at)
                    VALUES (?, 75, 'B', 'Verified', 75, 75, 75, NOW())
                ");
                $trustStmt->execute([$userId]);
            } else {
                $insertProfile = $pdo->prepare("
                    INSERT INTO learner_profiles (user_id, full_name, profile_photo, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $insertProfile->execute([$userId, $name ?: 'Learner', $picture ?: null]);
            }

            $pdo->commit();

            // Set session
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $userRole;
            $_SESSION['email'] = $email;

            // Clear OAuth session data and regenerate session ID
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_role']);
            session_regenerate_id(true);

            // Redirect to dashboard
            if ($userRole === 'expert') {
                header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=dashboard');
            } else {
                header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=dashboard');
            }
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Google OAuth Registration Failed: ' . $e->getMessage());
            renderOAuthError('Account Creation Failed', 'Failed to create user profile: ' . $e->getMessage(), $userRole);
        }
    }

} catch (Exception $e) {
    error_log('Google OAuth: Exception - ' . $e->getMessage());
    renderOAuthError('Authentication Failed', 'An unexpected error occurred during Google authentication. Please try again.', $userRole);
}
