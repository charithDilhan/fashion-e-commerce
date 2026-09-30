<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/functions.php';

$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'fashion_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$configuredPort = getenv('DB_PORT');
$ports = $configuredPort !== false && $configuredPort !== '' ? [(int) $configuredPort] : [3306, 3307];
$conn = null;
$connectionError = null;

foreach (array_unique($ports) as $port) {
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
        $conn = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        break;
    } catch (Throwable $exception) {
        $connectionError = $exception;
    }
}

if (!$conn instanceof PDO) {
    http_response_code(500);
    $message = $connectionError ? $connectionError->getMessage() : 'Unknown database error';
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Database setup required</title><style>body{font-family:Arial,sans-serif;background:#f7f7f5;color:#191919;padding:40px}.card{max-width:720px;margin:auto;background:white;border:1px solid #ddd;border-radius:18px;padding:30px}code{background:#f1f1ef;padding:3px 7px;border-radius:6px}h1{font-size:26px}</style></head><body><div class="card"><h1>Database connection failed</h1><p>Import <code>fashion_db_fixed.sql</code> into phpMyAdmin and check the database settings in <code>components/connect.php</code>.</p><p><strong>Technical detail:</strong> ' . e($message) . '</p></div></body></html>';
    exit;
}

[$user_id, $seller_id] = synchronise_auth_sessions($conn);

verify_csrf();
