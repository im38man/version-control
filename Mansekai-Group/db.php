<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    if (php_sapi_name() !== 'cli' && strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Koneksi database gagal. Cek config.php.']);
    } else {
        echo "Koneksi database gagal. Silakan cek pengaturan DB_HOST / DB_NAME / DB_USER / DB_PASS di config.php.";
    }
    exit;
}
$conn->set_charset('utf8mb4');
