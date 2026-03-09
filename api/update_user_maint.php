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

    if (empty($data['id']) || empty($data['name']) || empty($data['username'])) {
        echo json_encode(['success' => false, 'message' => 'ID, name and username are required.']);
        exit;
    }

    $id = intval($data['id']);
    $name = trim($data['name']);
    $username = trim($data['username']);
    $email = trim($data['email'] ?? '');
    $role = $data['role'] ?? 'reporter';
    $password = $data['password'] ?? '';
    $particularId = $data['particular_id'] ?? null;

    // Update query - only update password if provided
    if (!empty($password)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, role = ?, password = ?, particular_id = ? WHERE id = ?");
        $stmt->execute([$name, $username, $email, $role, $password, $particularId, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, role = ?, particular_id = ? WHERE id = ?");
        $stmt->execute([$name, $username, $email, $role, $particularId, $id]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

