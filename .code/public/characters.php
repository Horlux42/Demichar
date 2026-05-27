<?php
session_start();
require_once __DIR__ . '/src/helpers.php';

// Проверка авторизации через JWT
requireAuth();

// Проверка авторизации
if (!isset($_SESSION['user']['id'])) {
    header("Location: /");
    exit;
}

$userId = $_SESSION['user']['id'];
$userLogin = getUserLogin($userId);
$characters = getCharacters($userId);

// Обработка POST-запросов для API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $characterId = (int)($_POST['id'] ?? 0);
    $characterName = trim($_POST['name'] ?? '');
    
    switch ($action) {
        case 'get':
            echo json_encode(['success' => true, 'characters' => $characters]);
            break;
            
        case 'add':
            $chars = getCharacters($userId);
            if (count($chars) >= 30) {
                echo json_encode(['success' => false, 'error' => 'limit']);
                break;
            }
            $newId = addCharacter($userId, $characterName ?: 'Новый персонаж');
            echo json_encode(['success' => true, 'id' => $newId]);
            break;
            
        case 'update':
            if ($characterId && $characterName) {
                updateCharacterName($characterId, $userId, $characterName);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            break;
            
        case 'delete':
            if ($characterId) {
                $chars = getCharacters($userId);
                if (count($chars) <= 1) {
                    echo json_encode(['success' => false, 'error' => 'last']);
                    break;
                }
                deleteCharacter($characterId, $userId);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            break;
            
        default:
            echo json_encode(['success' => false]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DemiChar — Мои персонажи</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #e9e7e5;
    min-height: 100vh;
}

/* Header */
.header {
    display: flex;
    justify-content: space-between;
    padding: 15px 30px;
    align-items: center;
    background: #e9e7e5;
    border-bottom: 1px solid #d6d2ce;
    position: relative;
}

.logo {
    display: flex;
    align-items: center;
    gap: 15px;
}

.circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.green { background: #7ed957; }
.red { background: #ff5c5c; }

.title {
    font-size: 28px;
    font-weight: bold;
    color: #4a4a4a;
}

/* Профиль с выпадающим меню */
.profile {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 8px 14px;
    border-radius: 40px;
    transition: background 0.2s;
    font-weight: 500;
    color: #4a4a4a;
    position: relative;
}

.profile:hover {
    background: rgba(0,0,0,0.05);
}

/* Выпадающее меню профиля */
.profile-dropdown {
    position: absolute;
    top: 55px;
    right: 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    min-width: 200px;
    z-index: 100;
    overflow: hidden;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-user {
    padding: 16px;
    background: #f5f3f0;
    border-bottom: 1px solid #e0dbd6;
}

.dropdown-user-name {
    font-weight: 600;
    font-size: 16px;
    color: #2c2c2c;
}

.dropdown-user-email {
    font-size: 12px;
    color: #8b7f72;
    margin-top: 4px;
}

.dropdown-divider {
    height: 1px;
    background: #e0dbd6;
}

.dropdown-item {
    padding: 12px 16px;
    color: #4a4a4a;
    text-decoration: none;
    display: block;
    transition: background 0.15s;
    cursor: pointer;
}

.dropdown-item:hover {
    background: #f5f3f0;
}

.dropdown-item.logout {
    color: #e05a5a;
}

/* Container */
.container {
    padding: 20px 30px 80px;
}

.subtitle {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 25px;
    color: #3a3a3a;
    border-left: 5px solid #7ed957;
    padding-left: 15px;
}

.cards {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

/* Карточка */
.card {
    width: 260px;
    min-height: 90px;
    background: #f9f9f9;
    border-radius: 16px;
    padding: 12px 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    border: 1px solid #e0dbd6;
    position: relative;
}

.card-content {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #cbc3bb, #b8b0a8);
    border-radius: 12px;
    flex-shrink: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar span {
    font-size: 24px;
}

.card-info {
    flex: 1;
}

.card-name {
    font-weight: 600;
    color: #2c2c2c;
    font-size: 16px;
    display: block;
    margin-bottom: 4px;
}

.card-hint {
    font-size: 9px;
    color: #bbb;
    display: block;
}

/* Троеточие */
.card-menu-btn {
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 20px;
    color: #9b8f84;
    cursor: pointer;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.card-menu-btn:hover {
    background: rgba(0,0,0,0.08);
}

/* Выпадающее меню карточки */
.card-menu {
    position: absolute;
    top: 35px;
    right: 10px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    z-index: 20;
    min-width: 150px;
    border: 1px solid #e0dbd6;
}

.card-menu-item {
    padding: 10px 16px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.15s;
}

.card-menu-item:hover {
    background: #f5f3f0;
}

.card-menu-item.delete {
    color: #e05a5a;
}

/* Кнопка добавления */
.add-card {
    width: 260px;
    min-height: 90px;
    background: #f2efe8;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    cursor: pointer;
    border: 1px solid #e0dbd6;
    color: #8b7f72;
}

.add-card:hover {
    background: #e5dfd7;
}

/* Модалка */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 25px;
    border-radius: 20px;
    width: 320px;
    text-align: center;
}

.modal-content button {
    margin-top: 15px;
    padding: 8px 20px;
    background: #7ed957;
    border: none;
    border-radius: 40px;
    cursor: pointer;
}

/* Футер */
.footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background: #4a4744;
    color: #e2dbd4;
    padding: 12px;
    text-align: center;
    font-size: 13px;
}
</style>
</head>
<body>

<div class="header">
    <div class="logo">
        <div class="circle green"></div>
        <div class="title">DEMICHAR</div>
    </div>

    <div class="profile" id="profileBtn">
        <span>Профиль</span>
        <div class="circle red"></div>
    </div>
</div>

<!-- Выпадающее меню профиля -->
<div id="profileDropdown" class="profile-dropdown" style="display: none;">
    <div class="dropdown-user">
        <div class="dropdown-user-name"><?= htmlspecialchars($userLogin) ?></div>
        <div class="dropdown-user-email">Ваш аккаунт</div>
    </div>
    <div class="dropdown-divider"></div>
    <a href="src/logout.php" class="dropdown-item logout">🚪 Выйти из аккаунта</a>
</div>

<div class="container">
    <div class="subtitle">Мои персонажи (<span id="count"><?= count($characters) ?></span>/30)</div>
    <div class="cards" id="cards"></div>
</div>

<!-- Модалка -->
<div class="modal" id="modal" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <h3 id="modalName"></h3>
        <p>📖 Здесь будут характеристики персонажа</p>
        <button onclick="closeModalManual()">Закрыть</button>
    </div>
</div>

<div class="footer">© DemiChar — ⋮ меню для управления персонажем</div>

<script>
const userId = <?= $userId ?>;

async function api(action, data = {}) {
    const formData = new URLSearchParams();
    formData.append('action', action);
    for (let [key, value] of Object.entries(data)) {
        formData.append(key, value);
    }
    
    const response = await fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    });
    return response.json();
}

async function loadCharacters() {
    const res = await api('get');
    if (res.success) {
        renderCards(res.characters);
    }
}

function renderCards(characters) {
    const container = document.getElementById('cards');
    container.innerHTML = '';
    document.getElementById('count').innerText = characters.length;
    
    characters.forEach(char => {
        const card = document.createElement('div');
        card.className = 'card';
        card.dataset.id = char.id;
        
        // Формируем HTML аватара
        let avatarHtml = '';
        if (char.avatar && char.avatar !== 'null') {
            avatarHtml = `<img src="/${char.avatar}?t=${Date.now()}" alt="avatar">`;
        } else {
            avatarHtml = `<span>⚔</span>`;
        }
        
        card.innerHTML = `
            <div class="card-content">
                <div class="avatar">
                    ${avatarHtml}
                </div>
                <div class="card-info">
                    <span class="card-name">${escapeHtml(char.name)}</span>
                    <span class="card-hint">📋 клик для просмотра</span>
                </div>
            </div>
            <div class="card-menu-btn">⋮</div>
        `;
        
        // ✅ ЛКМ на контент — переход на страницу персонажа (charlist.php)
        card.querySelector('.card-content').onclick = () => {
            window.location.href = `charlist.php?id=${char.id}`;
        };
        
        // Меню троеточия
        const menuBtn = card.querySelector('.card-menu-btn');
        menuBtn.onclick = (e) => {
            e.stopPropagation();
            showCardMenu(card, char);
        };
        
        container.appendChild(card);
    });
    
    // Кнопка добавления
    const addCard = document.createElement('div');
    addCard.className = 'add-card';
    addCard.innerText = '+';
    addCard.onclick = async () => {
        const res = await api('add', { name: 'Новый персонаж' });
        if (res.success) {
            loadCharacters();
        } else if (res.error === 'limit') {
            alert('⚠️ Лимит 30 персонажей');
        }
    };
    container.appendChild(addCard);
}

function showCardMenu(card, character) {
    document.querySelectorAll('.card-menu').forEach(m => m.remove());
    
    const menu = document.createElement('div');
    menu.className = 'card-menu';
    menu.innerHTML = `
        <div class="card-menu-item">✏️ Изменить имя</div>
        <div class="card-menu-item delete">🗑️ Удалить персонажа</div>
    `;
    
    menu.querySelector('.card-menu-item:first-child').onclick = async () => {
        const newName = prompt('Новое имя персонажа:', character.name);
        if (newName && newName.trim()) {
            await api('update', { id: character.id, name: newName.trim() });
            loadCharacters();
        }
        menu.remove();
    };
    
    menu.querySelector('.delete').onclick = async () => {
        const res = await api('delete', { id: character.id });
        if (res.success) {
            loadCharacters();
        } else if (res.error === 'last') {
            alert('❌ Нельзя удалить последнего персонажа');
        }
        menu.remove();
    };
    
    card.appendChild(menu);
}

function closeModal(e) {
    if (e.target.id === 'modal') {
        document.getElementById('modal').style.display = 'none';
    }
}

function closeModalManual() {
    document.getElementById('modal').style.display = 'none';
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Выпадающее меню профиля
const profileBtn = document.getElementById('profileBtn');
const profileDropdown = document.getElementById('profileDropdown');

profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isVisible = profileDropdown.style.display === 'block';
    profileDropdown.style.display = isVisible ? 'none' : 'block';
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.profile') && !e.target.closest('.profile-dropdown')) {
        profileDropdown.style.display = 'none';
    }
    if (!e.target.closest('.card-menu-btn') && !e.target.closest('.card-menu')) {
        document.querySelectorAll('.card-menu').forEach(m => m.remove());
    }
});

loadCharacters();
</script>
</body>
</html>