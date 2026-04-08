<?php
// api/permanent_delete_issue.php
// Permanently and irreversibly deletes a soft-deleted issue.
// Only works on issues that are already in the deleted_at bin.

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
    // Safety: only allow permanent delete if already soft-deleted
    $check = $pdo->prepare("SELECT id FROM issues WHERE id = :id AND deleted_at IS NOT NULL");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Issue not found in Recently Deleted. Use soft_delete_issue.php first.']);
        exit;
    }

    $pdo->beginTransaction();

    // 1. Physical attachment files
    $attStmt = $pdo->prepare("SELECT filename FROM attachments WHERE issue_id = :id");
    $attStmt->execute([':id' => $id]);
    foreach ($attStmt->fetchAll() as $att) {
        $path = '../uploads/' . $att['filename'];
        if (file_exists($path)) unlink($path);
    }

    // 2. DB cascade: attachments → comments → activity_logs → issue
    $pdo->prepare("DELETE FROM attachments   WHERE issue_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM comments      WHERE issue_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM activity_logs WHERE issue_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM issues        WHERE id       = :id")->execute([':id' => $id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Issue permanently deleted.']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
