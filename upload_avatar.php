<?php
session_start();
// Подключение к БД (пример для MySQLi)
$db = new mysqli("localhost", "root", "", "games_news");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar'];
    
    // Проверка на ошибки загрузки
    if ($avatar['error'] === UPLOAD_ERR_OK) {
        // Проверка типа файла (дополнительная безопасность)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $avatar['tmp_name']);
        
        if (in_array($mime, $allowed_types)) {
            // Читаем содержимое файла
            $avatar_data = file_get_contents($avatar['tmp_name']);
            
            // Экранируем для безопасности
            $avatar_data = $db->real_escape_string($avatar_data);
            
            // Обновляем аватар в БД
            $user_id = $_SESSION['users_id']; // Предполагается, что ID пользователя в сессии
            $query = "UPDATE users SET avatar = '$avatar_data' WHERE id = $user_id";
            $db->query($query);
            
            // Перенаправляем, чтобы избежать повторной отправки формы
            header("Location: user.php");
            exit();
        } else {
            echo "Недопустимый тип файла";
        }
    }
}
?>