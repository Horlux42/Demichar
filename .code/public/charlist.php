<?php
session_start();
require_once __DIR__ . '/src/helpers.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: /");
    exit;
}

$userId = $_SESSION['user']['id'];
$characterId = (int)($_GET['id'] ?? 0);

$db = getDB();
$result = mysqli_query($db, "SELECT * FROM characters WHERE id = $characterId AND user_id = $userId");
$character = mysqli_fetch_assoc($result);

if (!$character) {
    header("Location: /characters.php");
    exit;
}

function refreshCharacterData($db, $characterId) {
    $result = mysqli_query($db, "SELECT * FROM characters WHERE id = $characterId");
    return mysqli_fetch_assoc($result);
}

$charData = json_decode($character['data'] ?? '', true);
if (!$charData) {
    $charData = [
        'level' => 1,
        'xp' => 0,
        'hp' => 0,
        'maxHp' => 0,
        'coins' => 0,
        'modules' => []
    ];
}

// Обработка загрузки аватарки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    error_log("=== ЗАГРУЗКА АВАТАРКИ ===");
    error_log("Файл: " . print_r($_FILES['avatar'], true));
    
    $uploadDir = __DIR__ . '/uploads/avatars/';
    
    // Создаём папку если нет
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        error_log("Создана папка: " . $uploadDir);
    }
    
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    error_log("Расширение: " . $ext);
    error_log("Разрешённые: " . implode(',', $allowed));
    
    if (in_array($ext, $allowed)) {
        $filename = 'avatar_' . $characterId . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        
        error_log("Целевой путь: " . $targetPath);
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
            error_log("Файл успешно перемещён!");
            $avatarPath = 'uploads/avatars/' . $filename;
            $escapedPath = mysqli_real_escape_string($db, $avatarPath);
            $updateResult = mysqli_query($db, "UPDATE characters SET avatar = '$escapedPath' WHERE id = $characterId");
            
            if ($updateResult) {
                error_log("БД обновлена: avatar = $escapedPath");
            } else {
                error_log("Ошибка БД: " . mysqli_error($db));
            }
            
            $character = refreshCharacterData($db, $characterId);
        } else {
            error_log("ОШИБКА: move_uploaded_file не сработал");
            error_log("tmp_name: " . $_FILES['avatar']['tmp_name']);
            error_log("error: " . $_FILES['avatar']['error']);
        }
    } else {
        error_log("НЕДОПУСТИМОЕ РАСШИРЕНИЕ: " . $ext);
    }
    
    header("Location: /charlist.php?id=" . $characterId . "&mode=" . ($_GET['mode'] ?? 'edit'));
    exit;
}

// Обработка удаления аватарки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_avatar'])) {
    if ($character['avatar'] && file_exists(__DIR__ . '/' . $character['avatar'])) {
        unlink(__DIR__ . '/' . $character['avatar']);
    }
    mysqli_query($db, "UPDATE characters SET avatar = NULL WHERE id = $characterId");
    $character = refreshCharacterData($db, $characterId);
    header("Location: /charlist.php?id=" . $characterId . "&mode=" . ($_GET['mode'] ?? 'edit'));
    exit;
}

// Обработка сохранения данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    header('Content-Type: application/json');
    $charData = json_decode($_POST['data'] ?? '', true);
    $jsonData = mysqli_real_escape_string($db, json_encode($charData));
    mysqli_query($db, "UPDATE characters SET data = '$jsonData' WHERE id = $characterId");
    
    if (isset($_POST['name'])) {
        $newName = mysqli_real_escape_string($db, trim($_POST['name']));
        mysqli_query($db, "UPDATE characters SET name = '$newName' WHERE id = $characterId");
    }
    echo json_encode(['success' => true]);
    exit;
}

$character = refreshCharacterData($db, $characterId);
$charData = json_decode($character['data'] ?? '', true);
if (!$charData) {
    $charData = [
        'level' => 1,
        'xp' => 0,
        'hp' => 0,
        'maxHp' => 0,
        'coins' => 0,
        'modules' => []
    ];
}

$mode = $_GET['mode'] ?? 'edit';
$avatarUrl = $character['avatar'] ? '/' . $character['avatar'] : null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($character['name']) ?> — DemiChar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/charlist.css">
</head>
<body data-character-id="<?= $characterId ?>" data-mode="<?= $mode ?>" 
      data-level="<?= $charData['level'] ?>" data-xp="<?= $charData['xp'] ?>"
      data-coins="<?= $charData['coins'] ?>" data-hp="<?= $charData['hp'] ?>"
      data-maxhp="<?= $charData['maxHp'] ?>" 
      data-modules='<?= json_encode($charData['modules']) ?>'>

<div class="character-container">
    <a href="characters.php" class="back-link">← Назад к персонажам</a>

    <div class="character-header">
        <div class="mode-badge <?= $mode === 'edit' ? 'edit' : '' ?>" id="modeBadge">
            <?= $mode === 'edit' ? 'РЕДАКТИРОВАНИЕ' : 'ПРОСМОТР' ?>
        </div>

        <div class="header-content">
            <div class="avatar-wrapper">
                <div class="avatar-large" id="avatarImage">
                    <?php 
                    $avatarPath = __DIR__ . '/' . ($character['avatar'] ?? '');
                    if (!empty($character['avatar']) && file_exists($avatarPath)): 
                        $avatarUrl = '/' . $character['avatar'];
                    ?>
                        <img src="<?= $avatarUrl ?>?v=<?= filemtime($avatarPath) ?>" alt="avatar" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <span>⚔</span>
                    <?php endif; ?>
                </div>
                <div class="avatar-upload" id="uploadAvatarBtn">📷</div>
                <?php if ($character['avatar']): ?>
                    <div class="avatar-delete" id="deleteAvatarBtn">✕</div>
                <?php else: ?>
                    <div class="avatar-delete" id="deleteAvatarBtn" style="display: none;">✕</div>
                <?php endif; ?>
            </div>

            <div class="character-info">
                <div class="name-section">
                    <input type="text" id="charName" class="char-name-input" value="<?= htmlspecialchars($character['name']) ?>" <?= $mode !== 'edit' ? 'readonly' : '' ?>>
                </div>

                <div class="level-section">
                    <div class="level-text" id="levelLabel">Уровень <?= $charData['level'] ?></div>
                    <div class="exp-container" id="xpWrap">
                        <div class="exp-bar-bg">
                            <div class="exp-bar-fill" id="xpBar" style="width: <?= ($charData['xp'] / 300) * 100 ?>%"></div>
                        </div>
                        <div class="exp-text" id="xpTooltip"><?= $charData['xp'] ?>/300</div>
                    </div>
                </div>

                <div class="stats-section">
                    <div class="stat-coin" id="coinStat">
                        <span>💰</span>
                        <span id="coinCount"><?= $charData['coins'] ?></span>
                    </div>
                    <div class="stat-hp" id="hpStat">
                        ❤️ <span id="hpVal"><?= $charData['hp'] ?></span> / <span id="hpMax"><?= $charData['maxHp'] ?></span>
                    </div>
                </div>
            </div>

            <div class="mode-buttons">
                <div class="dice-container">
                    <button class="dice-btn" id="diceBtn">🎲</button>
                    <div class="dice-menu" id="diceMenu" style="display: none;">
                        <div class="dice-grid">
                            <div class="dice-option" data-dice="4"><img src="/assets/images/dice/d4.png" alt="d4" class="dice-img"> d4</div>
                            <div class="dice-option" data-dice="6"><img src="/assets/images/dice/d6.png" alt="d6" class="dice-img"> d6</div>
                            <div class="dice-option" data-dice="8"><img src="/assets/images/dice/d8.png" alt="d8" class="dice-img"> d8</div>
                            <div class="dice-option" data-dice="10"><img src="/assets/images/dice/d10.png" alt="d10" class="dice-img"> d10</div>
                            <div class="dice-option" data-dice="12"><img src="/assets/images/dice/d12.png" alt="d12" class="dice-img"> d12</div>
                            <div class="dice-option" data-dice="20"><img src="/assets/images/dice/d20.png" alt="d20" class="dice-img"> d20</div>
                        </div>
                    </div>
                </div>
                <button class="edit-btn <?= $mode !== 'edit' ? 'viewing' : '' ?>" id="editBtn">
                    <?= $mode === 'edit' ? 'Просмотр' : 'Редактировать' ?>
                </button>
            </div>
        </div>
    </div>

    <div class="modules-section">
        <div class="modules-header">
            <span class="modules-title">📦 Модули персонажа</span>
        </div>
        <div class="modules-area" id="modulesArea"></div>
    </div>
</div>

<form id="avatarForm" method="POST" enctype="multipart/form-data" style="display: none;">
    <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp">
</form>

<form id="deleteAvatarForm" method="POST" style="display: none;">
    <input type="hidden" name="delete_avatar" value="1">
</form>

<!-- Модалка денег -->
<div class="stat-modal" id="coinModal">
    <div class="stat-modal-content">
        <h3>Деньги</h3>
        <input type="number" id="coinInput" step="any" placeholder="Количество денег" style="width: 100%; margin-bottom: 12px;">
        <div style="display: flex; gap: 8px; justify-content: center; margin-bottom: 16px;">
            <button type="button" class="quick-coin-btn" data-amount="10">+10</button>
            <button type="button" class="quick-coin-btn" data-amount="100">+100</button>
            <button type="button" class="quick-coin-btn" data-amount="1000">+1000</button>
        </div>
        <div style="display: flex; gap: 8px; justify-content: center;">
            <button id="coinSaveBtn">Сохранить</button>
            <button id="coinCloseBtn">Отмена</button>
        </div>
    </div>
</div>

<!-- Модалка HP -->
<div class="stat-modal" id="hpModal">
    <div class="stat-modal-content">
        <h3>Здоровье (HP)</h3>
        <div style="margin-bottom: 8px;">
            <label style="font-size: 12px; color: #6b6259; display: block; margin-bottom: 4px;">Текущее HP</label>
            <input type="number" id="hpValueInput" step="any" placeholder="10" style="width: 100%;">
        </div>
        <div style="margin-bottom: 12px;">
            <label style="font-size: 12px; color: #6b6259; display: block; margin-bottom: 4px;">Максимальное HP</label>
            <input type="number" id="hpMaxInput" step="any" placeholder="20" style="width: 100%;">
        </div>
        <div>
            <button id="hpSaveBtn">Сохранить</button>
            <button id="hpCloseBtn">Отмена</button>
        </div>
    </div>
</div>

<div class="dice-result" id="diceResult">
    <div class="dice-result-num" id="diceNum">—</div>
    <div class="dice-result-label" id="diceLabel">d20</div>
</div>

<div class="levelup-msg" id="levelupMsg">⬆ НОВЫЙ УРОВЕНЬ!</div>

<!-- НАДЁЖНАЯ МОДАЛКА ОПЫТА - ПОСЛЕДНЯЯ ПЕРЕД JS -->
<div id="xpOverlayRaw" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 999999;">
    <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; min-width: 280px;">
        <h3 style="margin-bottom: 10px;">⭐ Добавить опыт</h3>
        <input type="number" id="xpInputRaw" placeholder="Количество опыта" style="padding: 8px; margin: 15px 0; width: 100%; border: 2px solid #6E5847; border-radius: 8px;">
        <div>
            <button id="xpAddRaw" style="background: #6E5847; color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer;">Добавить</button>
            <button id="xpCloseRaw" style="background: #999; color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer;">Отмена</button>
        </div>
        <div style="margin-top: 15px; font-size: 13px;">Текущий XP: <span id="xpCurrentRaw">0</span> / 300</div>
    </div>
</div>

<script src="assets/js/charlist.js"></script>

<script>
    // Дополнительный скрипт для модалки опыта (на всякий случай)
    (function() {
        const xpWrap = document.getElementById('xpWrap');
        const overlay = document.getElementById('xpOverlayRaw');
        const xpInput = document.getElementById('xpInputRaw');
        const xpCurrent = document.getElementById('xpCurrentRaw');
        const xpAdd = document.getElementById('xpAddRaw');
        const xpClose = document.getElementById('xpCloseRaw');
        
        if (!xpWrap || !overlay) return;
        
        // Функция обновления XP на странице
        function updateXPDisplay() {
            const state = window.state || {};
            const pct = Math.min((state.xp / 300) * 100, 100);
            const xpBar = document.getElementById('xpBar');
            const levelLabel = document.getElementById('levelLabel');
            const xpTooltip = document.getElementById('xpTooltip');
            if (xpBar) xpBar.style.width = pct + '%';
            if (levelLabel) levelLabel.textContent = 'Уровень ' + (state.level || 1);
            if (xpTooltip) xpTooltip.textContent = (state.xp || 0) + ' / 300 XP';
        }
        
        // Открытие
        xpWrap.onclick = function(e) {
            e.stopPropagation();
            const state = window.state || {};
            xpCurrent.innerText = state.xp || 0;
            xpInput.value = '';
            overlay.style.display = 'flex';
        };
        
        // Добавление опыта
        xpAdd.onclick = function() {
            const val = parseInt(xpInput.value) || 0;
            if (val > 0 && window.addXP) {
                window.addXP(val);
                overlay.style.display = 'none';
            } else if (val > 0) {
                alert('Функция addXP не найдена');
                overlay.style.display = 'none';
            } else {
                alert('Введите количество опыта');
            }
        };
        
        // Закрытие
        xpClose.onclick = function() {
            overlay.style.display = 'none';
        };
        
        // Закрытие по клику на фон
        overlay.onclick = function(e) {
            if (e.target === overlay) overlay.style.display = 'none';
        };
        
        // Экспортируем обновление
        window.updateXPDisplay = updateXPDisplay;
    })();
</script>

</body>
</html>