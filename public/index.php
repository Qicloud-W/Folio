<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/health') {
    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'app' => 'Folio',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return;
}

if ($path === '/api/v1/ping') {
    http_response_code(200);
    echo json_encode([
        'message' => 'pong',
        'locale' => 'zh-CN',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return;
}

http_response_code(404);
echo json_encode([
    'error' => [
        'code' => 'NOT_FOUND',
        'message' => 'Route not found',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
