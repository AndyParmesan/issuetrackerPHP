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

    if (empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => 'Particular name is required.']);
        exit;
    }

    $name = trim($data['name']);

    $stmt = $pdo->prepare("INSERT INTO particulars (name) VALUES (?)");
    $stmt->execute([$name]);

    echo json_encode([
        'success' => true,
        'message' => 'Particular created successfully.',
        'data' => ['particular_id' => $pdo->lastInsertId()]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

