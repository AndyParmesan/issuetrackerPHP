<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "No ID provided. Use ?id=X"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Try to delete physical attachment files (skip if table doesn't exist)
    try {
        $attStmt = $pdo->prepare("SELECT filename FROM attachments WHERE issue_id = :id");
        $attStmt->execute([':id' => $id]);
        $attachments = $attStmt->fetchAll();
        foreach ($attachments as $att) {
            $filePath = '../uploads/' . $att['filename'];
            if (file_exists($filePath)) unlink($filePath);
        }
        // Delete attachment records
        $pdo->prepare("DELETE FROM attachments WHERE issue_id = :id")->execute([':id' => $id]);
    } catch (Exception $e) {
        // attachments table may not exist yet — skip silently
    }

    // 2. Delete comments (skip if table doesn't exist)
    try {
        $pdo->prepare("DELETE FROM comments WHERE issue_id = :id")->execute([':id' => $id]);
    } catch (Exception $e) {
        // comments table may not exist yet — skip silently
    }

    // 3. Delete activity logs (skip if table doesn't exist)
    try {
        $pdo->prepare("DELETE FROM activity_logs WHERE issue_id = :id")->execute([':id' => $id]);
    } catch (Exception $e) {
        // activity_logs table may not exist yet — skip silently
    }

    // 4. Delete the issue itself
    $stmt = $pdo->prepare("DELETE FROM issues WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Issue deleted successfully."]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
