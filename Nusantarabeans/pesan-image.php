<?php
require_once __DIR__ . '/includes/auth.php';

if (!is_logged_in()) {
    http_response_code(403);
    exit;
}

$path = $_GET['path'] ?? '';
$path = str_replace(['..', '\\'], '', $path); // cegah path traversal

if (strpos($path, 'uploads/pesan/') !== 0) {
    http_response_code(403);
    exit;
}

$fullPath = __DIR__ . '/' . $path;
if (!is_file($fullPath)) {
    http_response_code(404);
    exit;
}

$mime = @mime_content_type($fullPath) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=86400');
readfile($fullPath);
