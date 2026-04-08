<?php
// api/get_deleted_issues.php
// Returns all soft-deleted issues within the 30-day retention window.
// Issues older than 30 days are auto-purged on each call.

error_reporting(0);
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once '../config/database.php';

try {
    // ── Auto-purge issues older than 30 days ──────────────────
    // Delete attachments first (physical files + records)
    $expiredIds = $pdo->query(
        "SELECT id FROM issues WHERE deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    )->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($expiredIds)) {
        $placeholders = implode(',', array_fill(0, count($expiredIds), '?'));

        // Fetch attachment filenames for physical deletion
        $attStmt = $pdo->prepare("SELECT filename FROM attachments WHERE issue_id IN ($placeholders)");
        $attStmt->execute($expiredIds);
        foreach ($attStmt->fetchAll() as $att) {
            $path = '../uploads/' . $att['filename'];
            if (file_exists($path)) unlink($path);
        }

        // Delete DB records: attachments, comments, activity_logs, then issues
        $pdo->prepare("DELETE FROM attachments   WHERE issue_id IN ($placeholders)")->execute($expiredIds);
        $pdo->prepare("DELETE FROM comments      WHERE issue_id IN ($placeholders)")->execute($expiredIds);
        $pdo->prepare("DELETE FROM activity_logs WHERE issue_id IN ($placeholders)")->execute($expiredIds);
        $pdo->prepare("DELETE FROM issues        WHERE id       IN ($placeholders)")->execute($expiredIds);
    }

    // ── Fetch remaining deleted issues (within 30 days) ───────
    $sql = "
        SELECT
            i.*,
            u1.name  AS issued_by_name,
            u2.name  AS assigned_to_name,
            u3.name  AS deleted_by_name,
            p.name   AS particular_name,
            DATEDIFF(DATE_ADD(i.deleted_at, INTERVAL 30 DAY), NOW()) AS days_remaining
        FROM issues i
        LEFT JOIN users       u1 ON i.issued_by    = u1.id
        LEFT JOIN users       u2 ON i.assigned_to  = u2.id
        LEFT JOIN users       u3 ON i.deleted_by   = u3.id
        LEFT JOIN particulars p  ON i.particular_id = p.particular_id
        WHERE i.deleted_at IS NOT NULL
          AND i.deleted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY i.deleted_at DESC
    ";

    $stmt = $pdo->query($sql);
    $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalise camelCase aliases for the frontend
    foreach ($issues as &$i) {
        $i['issuedByName']   = $i['issued_by_name'];
        $i['assignedToName'] = $i['assigned_to_name'];
        $i['deletedByName']  = $i['deleted_by_name'];
        $i['particularName'] = $i['particular_name'];
        $i['daysRemaining']  = max(0, (int)$i['days_remaining']);
    }
    unset($i);

    echo json_encode([
        'success'      => true,
        'data'         => $issues,
        'purged_count' => count($expiredIds),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
