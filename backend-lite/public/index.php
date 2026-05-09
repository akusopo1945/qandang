<?php
// Minimal PHP Router to handle Qandang API routes without full Laravel
// Since the project files are currently incomplete.

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Log request
file_put_contents('php://stderr', sprintf("[%s] %s %s\n", date('Y-m-d H:i:s'), $_SERVER['REQUEST_METHOD'], $uri));

header('Content-Type: application/json');

// Root status
if ($uri === '/status' || $uri === '') {
    echo json_encode([
        'status' => 'success',
        'message' => 'Qandang API is running (Lite Mode)',
        'version' => '1.0.0-lite'
    ]);
    exit;
}

// Goats routes
if (preg_match('/^\/goats$/', $uri)) {
    echo json_encode([
        ['id' => 1, 'name' => 'Kambing A', 'weight' => 25.5],
        ['id' => 2, 'name' => 'Kambing B', 'weight' => 28.2]
    ]);
    exit;
}

if (preg_match('/^\/goats\/(\d+)$/', $uri, $matches)) {
    echo json_encode(['id' => $matches[1], 'name' => 'Goat ' . $matches[1]]);
    exit;
}

if (preg_match('/^\/goats\/(\d+)\/weight$/', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode(['status' => 'recorded', 'goat_id' => $matches[1]]);
    exit;
}

// Health routes
if (preg_match('/^\/health\/records\/(\d+)$/', $uri, $matches)) {
    echo json_encode([
        ['date' => '2026-05-01', 'type' => 'Vaccination', 'status' => 'Completed']
    ]);
    exit;
}

// 404
http_response_code(404);
echo json_encode(['error' => 'Not Found', 'uri' => $uri]);
