<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

$userId = $_GET['userId'] ?? null;
if (!$userId) {
    echo json_encode(["success" => false, "message" => "No userId provided."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT n.id, n.issue_id, n.type, n.message, n.is_read, n.created_at,
               i.title, i.description
        FROM notifications n
        LEFT JOIN issues i ON n.issue_id = i.id
        WHERE n.user_id = :userId
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([':userId' => $userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $unread = array_filter($notifications, fn($n) => $n['is_read'] == 0);

    echo json_encode([
        "success"       => true,
        "data"          => $notifications,
        "unread_count"  => count($unread)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>