<?php
require_once __DIR__ . '/src/helpers.php';

echo "<h1>🔐 Проверка JWT</h1>";

// Создаем тестовый токен
$testToken = createJWT(999, 'test_user');
echo "<h2>1. Создание JWT токена:</h2>";
echo "<pre style='background:#f0f0f0;padding:10px;word-break:break-all;'>" . htmlspecialchars($testToken) . "</pre>";

// Проверяем токен
$decoded = verifyJWT($testToken);
echo "<h2>2. Проверка JWT токена:</h2>";
if ($decoded) {
    echo "<p style='color:green'>✅ Токен валиден!</p>";
    echo "<pre>";
    print_r($decoded);
    echo "</pre>";
} else {
    echo "<p style='color:red'>❌ Токен не прошел проверку</p>";
}

// Проверяем авторизацию
echo "<h2>3. Статус авторизации:</h2>";
if (isAuthenticated()) {
    echo "<p style='color:green'>✅ Вы авторизованы!</p>";
    echo "<p>ID пользователя: " . getCurrentUserId() . "</p>";
} else {
    echo "<p style='color:orange'>⚠️ Вы не авторизованы (зайдите под своим аккаунтом)</p>";
}

// Показываем куку с токеном
echo "<h2>4. JWT токен в куке:</h2>";
if (isset($_COOKIE['jwt_token'])) {
    echo "<p style='color:green'>✅ Токен найден в куке!</p>";
    echo "<pre style='background:#f0f0f0;padding:10px;word-break:break-all;'>" . htmlspecialchars($_COOKIE['jwt_token']) . "</pre>";
} else {
    echo "<p style='color:orange'>⚠️ Токен не найден в куке</p>";
}
?>