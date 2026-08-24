<?php
require __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name !== '' && $message !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $message]);
    }
}

header('Location: index.php?sent=1');
exit;
