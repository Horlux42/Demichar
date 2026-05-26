<?php

require_once __DIR__ . '/helpers.php';

//получение данных из форм регистрации

$login = $_POST['login'];
$password = $_POST ['password'];

//запись данных в базу данных

$connect = getDB();

$sql = "INSERT INTO `users` (login, password) VALUES ('$login', '$password')";

if ($connect -> query($sql) === TRUE) {
    //echo 'Регистрация прошла успешно!';
    header ("Location: /login.html");
} else {
    echo 'Данный пользователь уже зарегестрирован :(';
}
