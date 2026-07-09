<?php
/**
 * Media Library API
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/class.news.php';

header('Content-Type: application/json');

// Require admin for write operations
$isAdmin = isAdmin();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $media = new MediaLibrary();
    
    switch ($method) {
        case 'GET':
            // List media
            $filters = [
                'mime' => $_GET['mime'] ?? '',
                'folder' => $_GET['folder'] ?? '',
                'search' => sanitize($_GET['search'] ?? '')
            ];
            $page = max(1, (int)($_GET['page'] ?? 1));
            $result = $media->getAll($filters, $page);
            echo json_encode($result);
            break;
            
        case 'POST':
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            
            // Upload
            if (empty($_FILES['file'])) {
                http_response_code(400);
                echo json_encode(['error' => 'No file uploaded']);
                break;
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            $id = $media->upload($_FILES['file'], $userId);
            
            if ($id) {
                echo json_encode(['success' => true, 'id' => $id, 'url' => $media->getById($id)['url']]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Upload failed']);
            }
            break;
            
        case 'PUT':
        case 'PATCH':
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);
            
            if ($id && isset($data['caption'])) {
                $media->updateCaption($id, $data['caption'], $data['alt_text'] ?? null);
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'DELETE':
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            
            $id = (int)($_GET['id'] ?? 0);
            if ($id) {
                $media->delete($id);
                echo json_encode(['success' => true]);
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
