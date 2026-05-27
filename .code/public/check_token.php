<?php
session_start();
require_once __DIR__ . '/../src/helpers.php';

header('Content-Type: application/json');

if (isAuthenticated()) {
    $token = getAuthToken();
    $payload = verifyJWT($token);
    
    echo json_encode([
        'success' => true,
        'user_id' => getCurrentUserId(),
        'token_valid' => true,
        'token_expires' => $payload['exp'] ?? null,
        'message' => 'JWT токен валиден'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'JWT токен недействителен или истек'
    ]);
}