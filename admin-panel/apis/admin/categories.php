<?php
// API to manage Categories (Admin)
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
        $stmt = $pdo->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM expert_profiles ep WHERE ep.category = c.name OR ep.category = c.slug) as expert_count
            FROM categories c
            ORDER BY c.display_order ASC, c.name ASC
        ");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $data['action'] ?? 'create';

    try {
        if ($action === 'create') {
            $name = trim($data['name'] ?? '');
            $slug = trim($data['slug'] ?? '');
            if (empty($slug)) {
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
                $slug = trim($slug, '-');
            }
            $description = trim($data['description'] ?? '');
            $iconUrl = trim($data['icon_url'] ?? 'briefcase');
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            $displayOrder = isset($data['display_order']) ? (int)$data['display_order'] : 0;

            if (empty($name)) {
                echo json_encode(['success' => false, 'error' => 'Category name is required']);
                exit;
            }

            // Check duplicate name or slug
            $chk = $pdo->prepare("SELECT id FROM categories WHERE name = ? OR slug = ?");
            $chk->execute([$name, $slug]);
            if ($chk->fetch()) {
                echo json_encode(['success' => false, 'error' => 'A category with this name or slug already exists']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon_url, is_active, display_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $iconUrl, $isActive, $displayOrder]);

            echo json_encode([
                'success' => true,
                'message' => 'Category created successfully',
                'category_id' => $pdo->lastInsertId()
            ]);
        } elseif ($action === 'update') {
            $id = (int)($data['id'] ?? 0);
            $name = trim($data['name'] ?? '');
            $slug = trim($data['slug'] ?? '');
            if (empty($slug)) {
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
                $slug = trim($slug, '-');
            }
            $description = trim($data['description'] ?? '');
            $iconUrl = trim($data['icon_url'] ?? 'briefcase');
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            $displayOrder = isset($data['display_order']) ? (int)$data['display_order'] : 0;

            if ($id <= 0 || empty($name)) {
                echo json_encode(['success' => false, 'error' => 'Valid ID and name are required']);
                exit;
            }

            // Check duplicate name or slug on different ID
            $chk = $pdo->prepare("SELECT id FROM categories WHERE (name = ? OR slug = ?) AND id != ?");
            $chk->execute([$name, $slug, $id]);
            if ($chk->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Another category with this name or slug already exists']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, icon_url = ?, is_active = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $iconUrl, $isActive, $displayOrder, $id]);

            echo json_encode([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        } elseif ($action === 'toggle_status') {
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid category ID']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE categories SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode([
                'success' => true,
                'message' => 'Category status toggled'
            ]);
        } elseif ($action === 'delete') {
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid category ID']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
