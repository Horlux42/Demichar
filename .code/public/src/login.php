<?php
session_start();
require_once __DIR__ . '/helpers.php';

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

$connect = getDB();
$login = mysqli_real_escape_string($connect, $login);
$password = mysqli_real_escape_string($connect, $password);

$sql = "SELECT * FROM `users` WHERE login='$login' AND password='$password'";
$result = $connect->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    $_SESSION['user'] = ['id' => $row['id']];
    
    // Создаем JWT токен
    $jwt = createJWT($row['id'], $row['login']);
    
    // Устанавливаем куку с JWT (HttpOnly для безопасности)
    setcookie('jwt_token', $jwt, [
        'expires' => time() + JWT_EXPIRY,
        'path' => '/',
        'domain' => '',
        'secure' => false,  // В production поставьте true (требуется HTTPS)
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    header("Location: /characters.php");
    exit;
} else {
    echo 'Вы ввели неверные данные';
}