<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

try {
    $type = $_GET['type'] ?? 'items'; // 'items' or 'stories'

    if ($type === 'stories' || $type === 'user_story') {
        $where = "WHERE source = 'user_story'";
    } else {
        $where = "WHERE (source != 'user_story' OR source IS NULL OR source = 'Manual' OR source = 'XLSX Import')";
    }

    // Total
    $stmt = $pdo->query("SELECT COUNT(*) FROM issues $where");
    $total = (int)$stmt->fetchColumn();

    // Count by state
    $stmt = $pdo->query("SELECT state, COUNT(*) as cnt FROM issues $where GROUP BY state");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stateCounts = [];
    foreach ($rows as $row) {
        $stateCounts[$row['state']] = (int)$row['cnt'];
    }

    // Count by status (for progress-based counts)
    $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM issues $where GROUP BY status");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $statusCounts = [];
    foreach ($rows as $row) {
        $statusCounts[$row['status']] = (int)$row['cnt'];
    }

    $getState  = fn($k) => $stateCounts[$k]  ?? 0;
    $getStatus = fn($k) => $statusCounts[$k] ?? 0;

    if ($type === 'stories' || $type === 'user_story') {
        $data = [
            'total'            => $total,
            'draftCount'       => $getState('Draft'),
            'forReviewCount'   => $getState('For Review'),
            'approvedCount'    => $getState('Approved'),
            'inDevCount'       => $getState('In Development'),
            'testingCount'     => $getState('For Testing'),
            'qaFailedCount'    => $getState('QA Failed'),
            'forUatCount'      => $getState('For UAT'),
            'readyDeployCount' => $getState('Ready for Deployment'),
            'deployedCount'    => $getState('Deployed'),
            'closedCount'      => $getState('Closed'),
        ];
    } else {
        $data = [
            'total'              => $total,
            'newCount'           => $getState('New'),
            'bugCount'           => $getState('Bug'),
            'openCount'          => $getState('Open'),
            'inProgressCount'    => $getState('In Progress') + $getStatus('In Progress'),
            'asDesignedCount'    => $getState('As Designed'),
            'enhancementCount'   => $getState('Enhancement'),
            'needsClarifCount'   => $getState('Needs Clarification'),
            'monitoringCount'    => $getState('Monitoring'),
            'closedCount'        => $getState('Closed') + $getStatus('Closed') + $getStatus('Resolved'),
        ];
    }

    // By priority for chart
    $stmt = $pdo->query("SELECT priority, COUNT(*) as count FROM issues $where GROUP BY priority ORDER BY priority");
    $data['byPriority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>