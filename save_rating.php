<?php
// save_rating.php


header('Content-Type: text/html; charset=utf-8');

//  данные POST-запроса
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    $game_id = intval($_POST['game_id']);
    $user_id = intval($_POST['user_id']);

    // Валидация
    if ($rating >= 1 && $rating <= 5 && $game_id > 0 && $user_id > 0) {

        // Подключение к базе данных
        $servername = "localhost";
        $username = "root"; 
        $password = ""; 
        $dbname = "games_ratings";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Установите кодировку соединения с базой данных
        $conn->set_charset("utf8");

        if ($conn->connect_error) {
            die("Ошибка подключения к базе данных: " . $conn->connect_error);
        }

        // Подготовленные запросы для безопасности
        $sql = "INSERT INTO game_ratings (game_id, user_id, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $game_id, $user_id, $rating, $rating); 

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
