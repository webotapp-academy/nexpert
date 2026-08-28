<?php
// API to manage System Settings (Admin)
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/connection/pdo.php';

// Check admin authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Admin privileges required.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value, setting_type, description FROM system_settings");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }

        // Ensure defaults if missing
        if (!isset($settings['enable_online_payment'])) $settings['enable_online_payment'] = '1';
        if (!isset($settings['enable_pay_later'])) $settings['enable_pay_later'] = '1';

        echo json_encode([
            'success' => true,
            'settings' => $settings,
            'details' => $rows
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $data['action'] ?? 'save';

    try {
        if ($action === 'toggle_payment_method') {
            $methodKey = $data['method'] ?? ''; // 'online' or 'pay_later'
            $settingKey = ($methodKey === 'online' || $methodKey === 'enable_online_payment') ? 'enable_online_payment' : 'enable_pay_later';

            // Get current value
            $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
            $stmt->execute([$settingKey]);
            $current = $stmt->fetchColumn();
            $newVal = ($current === '1') ? '0' : '1';

            $update = $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                VALUES (?, ?, 'boolean') 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $update->execute([$settingKey, $newVal, $newVal]);

            echo json_encode([
                'success' => true,
                'message' => 'Payment method status updated successfully',
                'setting_key' => $settingKey,
                'is_enabled' => ($newVal === '1')
            ]);
            exit;
        }

        // Save batch settings
        $settingsToSave = $data['settings'] ?? $data;
        unset($settingsToSave['action']);

        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($settingsToSave as $key => $val) {
            if (is_array($val) || is_object($val)) {
                $val = json_encode($val);
            }
            $stmt->execute([$key, (string)$val]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Platform settings saved successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
