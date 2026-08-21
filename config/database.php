<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$dbHost = getenv('BENEST_DB_HOST') ?: '127.0.0.1';
$dbName = getenv('BENEST_DB_NAME') ?: 'benest';
$dbUser = getenv('BENEST_DB_USER') ?: 'root';
$dbPass = getenv('BENEST_DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(503);
    exit('BENEST needs a configured MySQL database. Import database/schema.sql, then check config/database.php.');
}
