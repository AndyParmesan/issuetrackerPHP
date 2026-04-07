<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
require_once '../config/database.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['description'])) {
        echo json_encode(["success" => false, "message" => "Description is required."]);
        exit;
    }

    $sql = "INSERT INTO issues 
                (title, dashboard, particular_id, module, description, state, status, priority,
                 story_points, area_path, iteration_path, acceptance_criteria,
                 issued_by, assigned_to, date_identified, source)
            VALUES 
                (:title, :dashboard, :particularId, :module, :description, :state, :status, :priority,
                 :storyPoints, :areaPath, :iterationPath, :acceptanceCriteria,
                 :issuedBy, :assignedTo, :dateIdentified, :source)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title'               => $data['title']              ?? null,
        ':dashboard'           => $data['dashboard']          ?? null,
        ':particularId'        => $data['particularId']       ?? null,
        ':module'              => $data['module']             ?? null,
        ':description'         => $data['description'],
        ':state'               => $data['state']              ?? 'New',
        ':status'              => $data['status']             ?? 'New',
        ':priority'            => $data['priority']           ?? '4-Medium',
        ':storyPoints'         => $data['storyPoints']        ?? null,
        ':areaPath'            => $data['areaPath']           ?? null,
        ':iterationPath'       => $data['iterationPath']      ?? null,
        ':acceptanceCriteria'  => $data['acceptanceCriteria'] ?? null,
        ':issuedBy'            => $data['issuedBy']           ?? null,
        ':assignedTo'          => $data['assignedTo']         ?? null,
        ':dateIdentified'      => $data['dateIdentified']     ?? date('Y-m-d'),
        ':source'              => $data['source']             ?? 'Manual',
    ]);

    $newId = $pdo->lastInsertId();

    // Notify assigned user
    $assignedTo = $data['assignedTo'] ?? null;
    if ($assignedTo) {
        $actorName = $data['actorName'] ?? null;
        if (!$actorName && !empty($data['issuedBy'])) {
            $uStmt = $pdo->prepare("SELECT name FROM users WHERE id = :id");
            $uStmt->execute([':id' => $data['issuedBy']]);
            $u = $uStmt->fetch(PDO::FETCH_ASSOC);
            $actorName = $u['name'] ?? 'Someone';
        }
        $actorName = $actorName ?: 'Someone';
        $desc = isset($data['title']) && $data['title']
                ? $data['title']
                : substr(strip_tags($data['description']), 0, 60);
        $message = "You were assigned by {$actorName} to Issue #{$newId}: {$desc}";
        $pdo->prepare("INSERT INTO notifications (user_id, issue_id, type, message) VALUES (:u, :i, 'assigned', :m)")
            ->execute([':u' => $assignedTo, ':i' => $newId, ':m' => $message]);
    }

    echo json_encode([
        "success" => true,
        "message" => "Issue created successfully.",
        "data"    => ["id" => $newId]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>