<?php
// save_rating.php

// Установите правильные заголовки для кодировки UTF-8
header('Content-Type: text/html; charset=utf-8');

// Получаем данные POST-запроса
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    $game_id = intval($_POST['game_id']);
    $user_id = intval($_POST['user_id']);

    // Валидация
    if ($rating >= 1 && $rating <= 5 && $game_id > 0 && $user_id > 0) {

        // Подключение к базе данных (замените на свои данные!)
        $servername = "localhost";
        $username = "root"; // Замените!
        $password = ""; // Замените!
        $dbname = "games_ratings"; // Замените!

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Установите кодировку соединения с базой данных
        $conn->set_charset("utf8");

        if ($conn->connect_error) {
            die("Ошибка подключения к базе данных: " . $conn->connect_error);
        }

        // Используйте подготовленные запросы для безопасности
        $sql = "INSERT INTO game_ratings (game_id, user_id, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $game_id, $user_id, $rating, $rating); // "iiii" - 4 integer значения

        if ($stmt->execute()) {
            echo "Оценка успешно сохранена!";
        } else {
            echo "Ошибка при сохранении оценки: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Неверные данные оценки.";
    }
} else {
    echo "Недопустимый запрос.";
}
?>