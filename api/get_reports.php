<?php
require_once '../config/database.php';

try {
    $issueId = $_GET['id'] ?? null;

    if ($issueId) {
        // 1. Fetch the main issue details
        $stmt = $pdo->prepare("SELECT * FROM issues WHERE id = ?");
        $stmt->execute([$issueId]);
        $issue = $stmt->fetch();

        if (!$issue) {
            throw new Exception("Issue not found.");
        }

        // 2. Fetch all comments for this issue
        $commentStmt = $pdo->prepare("SELECT author, comment, created_at FROM comments WHERE issue_id = ? ORDER BY created_at ASC");
        $commentStmt->execute([$issueId]);
        $comments = $commentStmt->fetchAll();

        $reportData = [
            "issue" => $issue,
            "comments" => $comments
        ];

        echo json_encode(["data" => $reportData]);
    } 
    // If NO ID is provided, fetch the history list of reports (FIX FOR THE REPORTS TAB)
    else {
        $stmt = $pdo->query("SELECT * FROM reports ORDER BY generated_at DESC");
        $reports = $stmt->fetchAll();
        echo json_encode(["data" => $reports]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => $e->getMessage()]);
}
?>