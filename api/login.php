<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['username']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name, username, role 
                           FROM users 
                           WHERE username = :username 
                           AND password = :password");
    $stmt->execute([
        ':username' => $data['username'],
        ':password' => $data['password']
    ]);

    $user = $stmt->fetch();

    if ($user) {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password.'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
