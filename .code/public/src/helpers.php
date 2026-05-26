<?php

const DB_HOST = 'localhost';
const DB_NAME = 'sys';
const DB_USER = 'root';
const DB_PASSWORD = '19042007';

function getDB(): bool|mysqli {
    return mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
}

// ===== ФУНКЦИИ ДЛЯ ПЕРСОНАЖЕЙ =====

// Получить всех персонажей пользователя
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

// Добавить персонажа
function addCharacter($userId, $name) {
    $db = getDB();
    $userId = (int)$userId;
    $name = mysqli_real_escape_string($db, $name);
    mysqli_query($db, "INSERT INTO `characters` (user_id, name) VALUES ($userId, '$name')");
    return mysqli_insert_id($db);
}

// Обновить имя персонажа
function updateCharacterName($characterId, $userId, $newName) {
    $db = getDB();
    $characterId = (int)$characterId;
    $userId = (int)$userId;
    $newName = mysqli_real_escape_string($db, $newName);
    mysqli_query($db, "UPDATE `characters` SET name = '$newName' WHERE id = $characterId AND user_id = $userId");
}

// Удалить персонажа
function deleteCharacter($characterId, $userId) {
    $db = getDB();
    $characterId = (int)$characterId;
    $userId = (int)$userId;
    mysqli_query($db, "DELETE FROM `characters` WHERE id = $characterId AND user_id = $userId");
}

// ===== НОВАЯ ФУНКЦИЯ (которой не хватало) =====

// Получить логин пользователя по ID
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