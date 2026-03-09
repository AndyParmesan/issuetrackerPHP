<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['id']) || empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => 'ID and name are required.']);
        exit;
    }

    $id = intval($data['id']);
    $name = trim($data['name']);
    $isActive = isset($data['isActive']) ? intval($data['isActive']) : 1;

    $stmt = $pdo->prepare("UPDATE particulars SET name = ?, isActive = ? WHERE particular_id = ?");
    $stmt->execute([$name, $isActive, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Particular updated successfully.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

