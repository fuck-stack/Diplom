<?php
$db_host = 'localhost';
$db_name = 'games_news';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

// Создаем соединение
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Устанавливаем кодировку
$conn->set_charset($charset);
?>