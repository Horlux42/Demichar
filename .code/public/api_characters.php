<?php
session_start();
require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

// Проверка авторизации через JWT
if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'error' => 'unauthorized']);
    exit;
}

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'error' => 'unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'get':
        $characters = getCharacters($userId);
        echo json_encode(['success' => true, 'characters' => $characters]);
        break;
        
    case 'add':
        $characters = getCharacters($userId);
        if (count($characters) >= 30) {
            echo json_encode(['success' => false, 'error' => 'limit']);
            break;
        }
        $name = $input['name'] ?? 'Новый персонаж';
        $id = addCharacter($userId, $name);
        echo json_encode(['success' => true, 'id' => $id]);
        break;
        
    case 'update':
        $id = (int)($input['id'] ?? 0);
        $name = $input['name'] ?? '';
        if ($id && $name) {
            updateCharacterName($id, $userId, $name);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
        
    case 'delete':
        $id = (int)($input['id'] ?? 0);
        if ($id) {
            deleteCharacter($id, $userId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'unknown action']);
}