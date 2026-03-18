<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

try {
    // Total = ALL issues regardless of state
    $stmt = $pdo->query("SELECT COUNT(*) FROM issues");
    $totalIssues = (int)$stmt->fetchColumn();

    // Count by state grouping
    $stmt = $pdo->query("SELECT state, COUNT(*) as cnt FROM issues GROUP BY state");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $counts = [];
    foreach ($rows as $row) {
        $counts[$row['state']] = (int)$row['cnt'];
    }

    $get = fn($k) => $counts[$k] ?? 0;

    // Finding 26: Separate New and Draft counts
    $newCount        = $get('New') + $get('For Review') + $get('Approved');
    $draftCount      = $get('Draft');
    $bugCount        = $get('Bug') + $get('QA Failed');
    $openCount       = $get('Open');
    $inProgressCount = $get('In Progress') + $get('In Development') + $get('For Testing') +
                       $get('For UAT') + $get('Ready for Deployment');
    // Finding 27: Resolved renamed to Closed
    $resolvedCount   = $get('Resolved') + $get('Deployed') + $get('Closed');

    // Issues by priority
    $stmt = $pdo->query("SELECT priority, COUNT(*) as count FROM issues GROUP BY priority ORDER BY priority");
    $byPriority = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => [
            "totalIssues"     => $totalIssues,
            "newCount"        => $newCount,
            "draftCount"      => $draftCount,
            "bugCount"        => $bugCount,
            "openCount"       => $openCount,
            "inProgressCount" => $inProgressCount,
            "resolvedCount"   => $resolvedCount,
            "byPriority"      => $byPriority,
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
