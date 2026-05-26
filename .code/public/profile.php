<?php
session_start();
require_once __DIR__ . '/src/helpers.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: /");
    exit;
}

$connect = getDB();
$iduser = $_SESSION['user']['id'];
$result = mysqli_query($connect, "SELECT login FROM users WHERE id = $iduser");
$login = mysqli_fetch_assoc($result)['login'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/styles/style.css">
    <title>Профиль</title>
</head>
<body>
<main>
    <h2>Личный кабинет</h2>
    <p>Добро пожаловать, <?= htmlspecialchars($login) ?>!</p>
    
    <a href="characters.php">📜 Мои персонажи</a>
    <a href="src/logout.php">🚪 Выйти</a>
</main>
</body>
</html>