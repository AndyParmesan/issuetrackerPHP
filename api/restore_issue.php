<?php
// api/restore_issue.php
// Restores a soft-deleted issue back to active (clears deleted_at / deleted_by).

error_reporting(0);
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once '../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No ID provided. Use ?id=X']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE issues SET deleted_at = NULL, deleted_by = NULL WHERE id = :id AND deleted_at IS NOT NULL"
    );
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Issue not found in Recently Deleted.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Issue restored successfully.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
