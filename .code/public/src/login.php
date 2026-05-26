<?php

session_start();


require_once __DIR__ . '/helpers.php';

//получение данных из формы входа

$login = $_POST['login'];
$password = $_POST ['password'];

// Проверка данных в бд

$connect = getDB();

$sql = "SELECT * FROM `users` WHERE login='$login' AND password='$password'";
 
$result = $connect -> query($sql);

//print_r($result);

//print_r($result ->fetch_assoc() );  

if ($result -> num_rows > 0) {
    while ($row = $result -> fetch_assoc()) {
        //echo $row ['id'];
        //echo $row ['login'];

        $_SESSION['user'] = ['id' => $row ['id'],];
       
        header ("Location: /characters.php");

    }
} else {
   echo 'Вы ввели неверные данные';
}
