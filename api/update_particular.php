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
$id          = (int)($data['id']          ?? 0);
$name        = trim($data['name']         ?? '');
$description = trim($data['description']  ?? '');
$isActive    = (int)($data['isActive']    ?? 1);

if (!$id || !$name) {
    echo json_encode(['success' => false, 'message' => 'ID and name are required.']);
    exit;
}

try {
    $db   = isset($pdo) ? $pdo : getDB();
    $stmt = $db->prepare("UPDATE particulars SET name = :name, description = :description, isActive = :isActive WHERE particular_id = :id");
    $stmt->execute([':name' => $name, ':description' => $description ?: null, ':isActive' => $isActive, ':id' => $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
