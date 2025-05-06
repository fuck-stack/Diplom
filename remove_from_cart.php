<?php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cart_id = intval($_POST['cart_id']);

    // Получаем ID пользователя
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        if (!isset($_SESSION['temp_user_id'])) {
            $_SESSION['temp_user_id'] = session_id();
        }
        $user_id = $_SESSION['temp_user_id'];
    }

    // Проверяем, принадлежит ли запись текущему пользователю
    $check_sql = "SELECT id FROM cart WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $cart_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Удаляем запись
        $delete_sql = "DELETE FROM cart WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $cart_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }

    $check_stmt->close();
    $conn->close();

    header("Location: cart.php");
    exit();
}
?>