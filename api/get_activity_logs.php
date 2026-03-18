<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

$issueId = $_GET['issueId'] ?? null;

if (!$issueId) {
    echo json_encode(['success' => false, 'message' => 'Missing issueId']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE issue_id = :issueId ORDER BY created_at DESC");
    $stmt->execute([':issueId' => $issueId]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $logs]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>