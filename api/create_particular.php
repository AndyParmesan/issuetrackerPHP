<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once '../config/database.php';
$data        = json_decode(file_get_contents('php://input'), true);
$name        = trim($data['name']        ?? '');
$description = trim($data['description'] ?? '');
$isActive    = isset($data['isActive']) ? (int)$data['isActive'] : 1;

if (!$name) {
    echo json_encode(['success' => false, 'message' => 'Name is required.']);
    exit;
}

try {
    $db = isset($pdo) ? $pdo : getDB();

    // Get next ID manually since particular_id may not be AUTO_INCREMENT
    $maxStmt = $db->query("SELECT COALESCE(MAX(particular_id), 0) + 1 AS next_id FROM particulars");
    $nextId  = (int)$maxStmt->fetchColumn();

    $stmt = $db->prepare("INSERT INTO particulars (particular_id, name, description, isActive) VALUES (:id, :name, :description, :isActive)");
    $stmt->execute([
        ':id'          => $nextId,
        ':name'        => $name,
        ':description' => $description ?: null,
        ':isActive'    => $isActive
    ]);
    echo json_encode(['success' => true, 'id' => $nextId]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
