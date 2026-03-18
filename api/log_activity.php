<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$issueId     = $data['issueId']     ?? null;
$userId      = $data['userId']      ?? null;
$userName    = $data['userName']    ?? 'Unknown';
$fieldChanged = $data['fieldChanged'] ?? '';
$oldValue    = $data['oldValue']    ?? null;
$newValue    = $data['newValue']    ?? null;

if (!$issueId || !$fieldChanged) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO activity_logs (issue_id, user_id, user_name, field_changed, old_value, new_value) VALUES (:issueId, :userId, :userName, :fieldChanged, :oldValue, :newValue)");
    $stmt->execute([
        ':issueId'      => $issueId,
        ':userId'       => $userId,
        ':userName'     => $userName,
        ':fieldChanged' => $fieldChanged,
        ':oldValue'     => $oldValue,
        ':newValue'     => $newValue,
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>