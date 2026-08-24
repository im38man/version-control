<?php
// =========================================
// Konfigurasi koneksi database
// Sesuaikan dengan hosting/XAMPP kamu
// =========================================
$DB_HOST = "sql203.infinityfree.com";
$DB_USER = "if0_42519214";
$DB_PASS = "oe0ULqLuM8EdYS";
$DB_NAME = "if0_42519214_mansekai_study";

$koneksi = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");
