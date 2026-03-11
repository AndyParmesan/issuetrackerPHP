<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

try {
    $input      = json_decode(file_get_contents('php://input'), true);
    $dateRange  = $input['dateRange']    ?? 'Last 30 Days';
    $statusFilter = $input['statusFilter'] ?? 'All Statuses';

    switch ($dateRange) {
        case 'This Day':
            $dateCond = "AND DATE(i.date_identified) = CURDATE()"; break;
        case 'Last 7 Days':
            $dateCond = "AND i.date_identified >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
        case 'Last 90 Days':
            $dateCond = "AND i.date_identified >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)"; break;
        case 'This Year':
            $dateCond = "AND YEAR(i.date_identified) = YEAR(CURDATE())"; break;
        default:
            $dateCond = "AND i.date_identified >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; break;
    }

    $statusCond = '';
    $params = [];
    if ($statusFilter !== 'All Statuses' && !empty($statusFilter)) {
        $statusCond = "AND i.state = :state";
        $params[':state'] = $statusFilter;
    }

    $baseWhere = "WHERE 1=1 $dateCond $statusCond";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM issues i $baseWhere");
    $stmt->execute($params);
    $totalIssues = (int)$stmt->fetchColumn();

    $counts = ['New'=>0,'Draft'=>0,'Bug'=>0,'Open'=>0,'In Progress'=>0,'Resolved'=>0,
               'For Review'=>0,'Approved'=>0,'In Development'=>0,'For Testing'=>0,
               'QA Failed'=>0,'For UAT'=>0,'Ready for Deployment'=>0,'Deployed'=>0,'Closed'=>0];

    $stmt = $pdo->prepare("SELECT state, COUNT(*) as cnt FROM issues i $baseWhere GROUP BY state");
    $stmt->execute($params);
    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['state']] = (int)$row['cnt'];
    }

    $newCount        = ($counts['New']   + $counts['Draft'] + $counts['For Review'] + $counts['Approved']);
    $bugCount        = ($counts['Bug']   + $counts['QA Failed']);
    $openCount       = ($counts['Open']);
    $inProgressCount = ($counts['In Progress'] + $counts['In Development'] + $counts['For Testing'] +
                        $counts['For UAT'] + $counts['Ready for Deployment']);
    $resolvedCount   = ($counts['Resolved'] + $counts['Deployed'] + $counts['Closed']);

    $stmt = $pdo->prepare("INSERT INTO reports (total_issues, new_count, bug_count, open_count, in_progress_count, resolved_count, date_range, status_filter, generated_at)
                           VALUES (:total, :new, :bug, :open, :prog, :res, :dr, :sf, NOW())");
    $stmt->execute([
        ':total' => $totalIssues,
        ':new'   => $newCount,
        ':bug'   => $bugCount,
        ':open'  => $openCount,
        ':prog'  => $inProgressCount,
        ':res'   => $resolvedCount,
        ':dr'    => $dateRange,
        ':sf'    => $statusFilter,
    ]);
    $reportId = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "data" => [
            "id"              => $reportId,
            "totalIssues"     => $totalIssues,
            "newCount"        => $newCount,
            "bugCount"        => $bugCount,
            "openCount"       => $openCount,
            "inProgressCount" => $inProgressCount,
            "resolvedCount"   => $resolvedCount,
            "dateRange"       => $dateRange,
            "statusFilter"    => $statusFilter,
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
