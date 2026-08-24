<?php
require __DIR__ . '/config/db.php';
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
