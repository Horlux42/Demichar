<?php

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

const DB_HOST = 'localhost';
const DB_NAME = 'sys';
const DB_USER = 'root';
const DB_PASSWORD = '19042007';

// JWT настройки
define('JWT_SECRET', 'demichar_super_secret_key_2024_firebase_!!');
define('JWT_ALGO', 'HS256');
define('JWT_EXPIRY', 3600 * 24 * 7); // 7 дней

function getDB(): bool|mysqli {
    return mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
}

// ===== JWT ФУНКЦИИ =====

// Создание JWT токена
function createJWT($userId, $login): string {
    $issuedAt = time();
    $payload = [
        'iat' => $issuedAt,
        'exp' => $issuedAt + JWT_EXPIRY,
        'user_id' => $userId,
        'login' => $login,
        'iss' => 'demichar_app'
    ];
    return JWT::encode($payload, JWT_SECRET, JWT_ALGO);
}

// Проверка JWT токена
function verifyJWT($token) {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGO));
        return (array)$decoded;
    } catch (Exception $e) {
        return false;
    }
}

// Получение токена из куки
function getAuthToken(): ?string {
    return $_COOKIE['jwt_token'] ?? null;
}

// Проверка авторизации
function isAuthenticated(): bool {
    // Сначала проверяем сессию (для обратной совместимости)
    if (isset($_SESSION['user']['id'])) {
        return true;
    }
    
    // Проверяем JWT токен
    $token = getAuthToken();
    if ($token) {
        $payload = verifyJWT($token);
        if ($payload && isset($payload['user_id'])) {
            // Восстанавливаем сессию из токена
            $_SESSION['user'] = ['id' => $payload['user_id']];
            $_SESSION['user_login'] = $payload['login'];
            return true;
        }
    }
    
    return false;
}

// Принудительная проверка с редиректом
function requireAuth(): void {
    if (!isAuthenticated()) {
        header('Location: /');
        exit;
    }
}

// Получение ID текущего пользователя
function getCurrentUserId(): ?int {
    if (isset($_SESSION['user']['id'])) {
        return $_SESSION['user']['id'];
    }
    
    $token = getAuthToken();
    if ($token) {
        $payload = verifyJWT($token);
        if ($payload && isset($payload['user_id'])) {
            return $payload['user_id'];
        }
    }
    
    return null;
}

// ===== ФУНКЦИИ ДЛЯ ПЕРСОНАЖЕЙ =====

function getCharacters($userId): array {
    $db = getDB();
    $userId = (int)$userId;
    $result = mysqli_query($db, "SELECT * FROM `characters` WHERE user_id = $userId ORDER BY id");
    if (!$result) return [];
    
    $characters = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $characters[] = $row;
    }
    return $characters;
}

function addCharacter($userId, $name) {
    $db = getDB();
    $userId = (int)$userId;
    $name = mysqli_real_escape_string($db, $name);
    mysqli_query($db, "INSERT INTO `characters` (user_id, name) VALUES ($userId, '$name')");
    return mysqli_insert_id($db);
}

function updateCharacterName($characterId, $userId, $newName) {
    $db = getDB();
    $characterId = (int)$characterId;
    $userId = (int)$userId;
    $newName = mysqli_real_escape_string($db, $newName);
    mysqli_query($db, "UPDATE `characters` SET name = '$newName' WHERE id = $characterId AND user_id = $userId");
}

function deleteCharacter($characterId, $userId) {
    $db = getDB();
    $characterId = (int)$characterId;
    $userId = (int)$userId;
    mysqli_query($db, "DELETE FROM `characters` WHERE id = $characterId AND user_id = $userId");
}

function getUserLogin($userId): string {
    $db = getDB();
    $userId = (int)$userId;
    $result = mysqli_query($db, "SELECT login FROM users WHERE id = $userId");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['login'];
    }
    return 'Пользователь';
}

mysqli_report(MYSQLI_REPORT_OFF);