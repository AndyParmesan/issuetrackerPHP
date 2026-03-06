<?php
require_once '../config/database.php';

$id   = $_GET['id']   ?? null;
$view = $_GET['view'] ?? '0';   // ?view=1 = inline, omit = force download

if (!$id) {
    http_response_code(400);
    echo "Missing attachment ID.";
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM attachments WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $file = $stmt->fetch();

    if (!$file) {
        http_response_code(404);
        echo "Attachment not found.";
        exit;
    }

    $filePath = '../uploads/' . $file['filename'];

    if (!file_exists($filePath)) {
        http_response_code(404);
        echo "File not found on server.";
        exit;
    }

    $mimeType     = $file['file_type'] ?? mime_content_type($filePath);
    $originalName = $file['original_name'] ?? basename($filePath);

    // Viewable inline types — images, PDF, plain text
    $inlineTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'application/pdf',
        'text/plain', 'text/html', 'text/csv',
    ];

    $isViewable = in_array($mimeType, $inlineTypes);

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));

    if ($view === '1' && $isViewable) {
        // Open inline in browser tab
        header('Content-Disposition: inline; filename="' . $originalName . '"');
    } else {
        // Force download
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
    }

    header('Cache-Control: private, max-age=0');
    readfile($filePath);

} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
