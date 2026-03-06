<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

try {
    $issueId = $_GET['issueId'] ?? null;

    if (!$issueId) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "issueId is required."]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.issue_id,
            c.user_id,
            c.author,
            c.comment,
            c.created_at,
            u.name AS user_name
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.issue_id = :issueId
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([':issueId' => $issueId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $comments]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
