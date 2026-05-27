<?php
session_start();

// Удаляем сессию
unset($_SESSION['user']);
unset($_SESSION['user_login']);

// Удаляем куку с JWT токеном
setcookie('jwt_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

header("Location: /");
exit;