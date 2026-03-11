<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once '../config/database.php';

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    die(json_encode(["success" => false, "message" => "Invalid JSON input."]));
}

$issueId = $data['issueId'] ?? null;
$comment = trim($data['comment'] ?? '');
$userId  = $data['userId']  ?? null;
$author  = $data['author']  ?? null;

if (!$issueId || empty($comment)) {
    die(json_encode(["success" => false, "message" => "issueId and comment are required."]));
}

if ($userId && !is_numeric($userId)) {
    $author = $userId;
    $userId = null;
}

if ($userId && is_numeric($userId)) {
    try {
        $uStmt = $pdo->prepare("SELECT name FROM users WHERE id = :id");
        $uStmt->execute([':id' => $userId]);
        $user = $uStmt->fetch();
        if ($user) $author = $user['name'];
    } catch (Exception $e) {
    }
}

if (empty($author)) {
    $author = 'Admin User';
}

try {
    $sql  = "INSERT INTO comments (issue_id, user_id, author, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$issueId, $userId ?: null, $author, $comment]);

    echo json_encode(["success" => true, "message" => "Comment saved successfully!"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
