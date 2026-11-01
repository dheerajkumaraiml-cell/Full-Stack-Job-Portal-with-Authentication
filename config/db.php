<?php
declare(strict_types=1);

$dbHost = getenv('SEE_DB_HOST') ?: '127.0.0.1';
$dbName = getenv('SEE_DB_NAME') ?: 'see_your_jobs';
$dbUser = getenv('SEE_DB_USER') ?: 'root';
$dbPassword = getenv('SEE_DB_PASSWORD') ?: '';

$pdo = new PDO(
    "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
    $dbUser,
    $dbPassword,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
