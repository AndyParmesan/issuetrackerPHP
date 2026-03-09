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

    if (empty($data['name']) || empty($data['email'])) {
        echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
        exit;
    }

    $name = trim($data['name']);
    $email = trim($data['email']);
    $role = $data['role'] ?? 'reporter';
    $particularId = $data['particular_id'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO users (name, email, role, particular_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $role, $particularId]);

    echo json_encode([
        'success' => true,
        'message' => 'User created successfully.',
        'data' => ['id' => $pdo->lastInsertId()]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

