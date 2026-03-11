<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM issues");
    $totalIssues = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT state, COUNT(*) as cnt FROM issues GROUP BY state");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $counts = [];
    foreach ($rows as $row) {
        $counts[$row['state']] = (int)$row['cnt'];
    }

    $get = fn($k) => $counts[$k] ?? 0;

    $newCount        = $get('New') + $get('Draft') + $get('For Review') + $get('Approved');
    $bugCount        = $get('Bug') + $get('QA Failed');
    $openCount       = $get('Open');
    $inProgressCount = $get('In Progress') + $get('In Development') + $get('For Testing') +
                       $get('For UAT') + $get('Ready for Deployment');
    $resolvedCount   = $get('Resolved') + $get('Deployed') + $get('Closed');

    $stmt = $pdo->query("SELECT priority, COUNT(*) as count FROM issues GROUP BY priority ORDER BY priority");
    $byPriority = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT COALESCE(dashboard, 'Unknown') as dashboard, COUNT(*) as count FROM issues GROUP BY dashboard ORDER BY count DESC LIMIT 10");
    $byDashboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => [
            "totalIssues"     => $totalIssues,
            "newCount"        => $newCount,
            "bugCount"        => $bugCount,
            "openCount"       => $openCount,
            "inProgressCount" => $inProgressCount,
            "resolvedCount"   => $resolvedCount,
            "byPriority"      => $byPriority,
            "byDashboard"     => $byDashboard,
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
