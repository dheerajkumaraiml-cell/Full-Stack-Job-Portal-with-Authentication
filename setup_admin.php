<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/config/db.php';
$name = trim((string)getenv('SEE_ADMIN_NAME'));
$email = trim((string)getenv('SEE_ADMIN_EMAIL'));
$password = (string)getenv('SEE_ADMIN_PASSWORD');
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 14 || strlen($password) > 4096) {
    fwrite(STDERR, "Set SEE_ADMIN_NAME, SEE_ADMIN_EMAIL, and SEE_ADMIN_PASSWORD (at least 14 characters).\n");
    exit(1);
}
require_once __DIR__ . '/config/db.php';
$statement = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$statement->execute([$email]);
if ($statement->fetch()) {
    fwrite(STDERR, "An account with that email already exists; no changes made.\n");
    exit(1);
}
$statement = $pdo->prepare("INSERT INTO users (full_name, email, role, password, profile_completed) VALUES (?, ?, 'admin', ?, 1)");
$statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
fwrite(STDOUT, "Admin account created for {$email}.\n");
