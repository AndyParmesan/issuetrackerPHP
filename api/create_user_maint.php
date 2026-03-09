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

    if (empty($data['name']) || empty($data['username'])) {
        echo json_encode(['success' => false, 'message' => 'Name and username are required.']);
        exit;
    }

    $name = trim($data['name']);
    $username = trim($data['username']);
    $email = trim($data['email'] ?? '');
    $role = $data['role'] ?? 'reporter';
    $password = $data['password'] ?? '';
    $particularId = $data['particular_id'] ?? null;

    // If password is provided, use it as MD5 (matching existing system)
    $passwordHash = $password;
    
    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, role, password, particular_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $username, $email, $role, $passwordHash, $particularId]);

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

