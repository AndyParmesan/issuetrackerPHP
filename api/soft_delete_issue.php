<?php
// api/soft_delete_issue.php
// Moves an issue to "Recently Deleted" (sets deleted_at timestamp).
// Replaces the hard-delete behaviour of delete_issue.php.

error_reporting(0);
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once '../config/database.php';

$id = $_GET['id'] ?? null;
$deletedBy = $_GET['deletedBy'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No ID provided. Use ?id=X']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE issues SET deleted_at = NOW(), deleted_by = :deletedBy WHERE id = :id AND deleted_at IS NULL"
    );
    $stmt->execute([':id' => $id, ':deletedBy' => $deletedBy ?: null]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Issue not found or already deleted.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Issue moved to Recently Deleted.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
