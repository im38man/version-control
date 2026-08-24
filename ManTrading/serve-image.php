<?php
/**
 * ManTrading - Image Proxy untuk foto Community
 * Beberapa hosting gratis (termasuk InfinityFree) kadang memblokir akses
 * langsung ke file di folder uploads/. File ini menyajikan foto lewat PHP
 * supaya selalu bisa tampil di semua hosting.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$file = basename($_GET['f'] ?? ''); // basename() cegah path traversal (../)
$path = UPLOAD_DIR . $file;

if ($file === '' || !is_file($path)) {
    http_response_code(404);
    exit('Foto tidak ditemukan.');
}

$mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=86400');
readfile($path);
exit;
