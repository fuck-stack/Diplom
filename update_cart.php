<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = $_POST['item_id']; // ID записи в таблице cart, а не product_id
    $quantity = $_POST['quantity'];

    // Получаем ID пользователя
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
         if (!isset($_SESSION['temp_user_id'])) {
            $_SESSION['temp_user_id'] = session_id();
        }
        $user_id = $_SESSION['temp_user_id'];
    }

     // Проверка, принадлежит ли запись в корзине текущему пользователю (очень важно для безопасности!)
    $sql = "SELECT id FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $item_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Обновляем количество товара в корзине
        $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity, $item_id);
        $stmt->execute();
        echo "success";  // Ответ для JavaScript (для обработки ошибок)

    } else {
       echo "error: Item not found or does not belong to the user.";
    }

    exit();
}
?>