<?php
session_start();

// GANTI BAGIAN INI SESUAI DENGAN DETAIL DATABASE INFINITYFREE ANDA
$host = 'sql207.infinityfree.com'; // Biasanya sqlXXX.infinityfree.com
$dbname = 'if0_42606039_man_tracker'; // Biasanya if0_xxxxxx_nama
$user = 'if0_42606039'; // Biasanya if0_xxxxxx
$pass = 'EYYLpoSnZP4q'; // Password database anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>