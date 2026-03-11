<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

$issueId      = $_GET['issueId']      ?? null;
$attachmentId = $_GET['attachmentId'] ?? null;

if (!$issueId || !$attachmentId) {
    echo json_encode(["success" => false, "message" => "Missing issueId or attachmentId."]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM attachments WHERE id = :id AND issue_id = :issueId");
    $stmt->execute([':id' => $attachmentId, ':issueId' => $issueId]);
    $file = $stmt->fetch();

    if (!$file) {
        echo json_encode(["success" => false, "message" => "Attachment not found."]);
        exit;
    }

    $filePath = '../uploads/' . $file['filename'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    $del = $pdo->prepare("DELETE FROM attachments WHERE id = :id");
    $del->execute([':id' => $attachmentId]);

    echo json_encode(["success" => true, "message" => "Attachment deleted."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
