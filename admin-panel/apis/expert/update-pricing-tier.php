<?php
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
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    error_log('Unauthorized access attempt');
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

    // Parse input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $pricingId = $input['pricing_id'] ?? null;
    $duration = $input['duration_minutes'] ?? null;
    $price = $input['price'] ?? null;
    $description = $input['description'] ?? '';
    $expertId = $_SESSION['user_id'];
    
    if (!$pricingId || !$duration || $price === null) {
        throw new Exception('All required fields must be filled');
    }
    
    // Verify that this pricing belongs to the logged-in expert
    $stmt = $pdo->prepare("SELECT expert_id FROM expert_pricing WHERE id = ?");
    $stmt->execute([$pricingId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing || $existing['expert_id'] != $expertId) {
        throw new Exception('Pricing not found or unauthorized');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update the pricing (without changing session_type)
    $stmt = $pdo->prepare("
        UPDATE expert_pricing 
        SET duration_minutes = ?,
            amount = ?,
            description = ?,
            updated_at = NOW()
        WHERE id = ? AND expert_id = ?
    ");
    
    $stmt->execute([
        $duration,
        $price,
        $description,
        $pricingId,
        $expertId
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    error_log('Pricing updated successfully - ID: ' . $pricingId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Pricing updated successfully'
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Update Pricing Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Update Pricing Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
