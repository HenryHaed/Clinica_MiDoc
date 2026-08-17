<?php
/**
 * Simple JSON health endpoint for AJAX / deployment checks.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once dirname(__DIR__) . '/config/db.php';

$db = db_status();

$response = [
    'ok' => true,
    'service' => 'Clinica Mi Doc API',
    'php' => PHP_VERSION,
    'time' => date('c'),
    'database' => $db,
];

http_response_code(200);
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
