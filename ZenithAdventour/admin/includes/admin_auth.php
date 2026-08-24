<?php
/**
 * Wajib login sebagai admin untuk mengakses halaman di folder /admin.
 * Include file ini di baris paling atas setiap halaman admin.
 */
require_once __DIR__ . '/../../includes/session.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php?redirect=admin/index.php');
    exit;
}
