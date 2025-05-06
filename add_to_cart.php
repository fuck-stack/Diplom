<?php
session_start();
include 'db_config.php';

// Получаем ID пользователя
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    if (!isset($_SESSION['temp_user_id'])) {
        $_SESSION['temp_user_id'] = session_id();
    }
    $user_id = $_SESSION['temp_user_id'];
}

// Проверяем, были ли переданы данные о товаре
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $product_title = $_POST['product_title'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $selected_size = isset($_POST['selected_size']) ? $_POST['selected_size'] : ''; // Заменяем ?? на тернарный оператор

    // Проверяем соединение с базой данных
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Проверяем, есть ли уже такой товар с таким же размером в корзине у пользователя
    $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size = ?";
    $check_stmt = $conn->prepare($check_sql);

    // Если prepare вернул false, выводим ошибку
    if ($check_stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $check_stmt->bind_param("sis", $user_id, $product_id, $selected_size);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Если товар уже есть в корзине с таким размером, увеличиваем количество
        $row = $check_result->fetch_assoc();
        $new_quantity = $row['quantity'] + 1;

        $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);

        if ($update_stmt === false) {
            die("Error preparing update statement: " . $conn->error);
        }

        $update_stmt->bind_param("ii", $new_quantity, $row['id']);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Если товара нет в корзине с таким размером, добавляем новый
        $insert_sql = "INSERT INTO cart (user_id, product_id, product_title, product_price, product_image, quantity, size)
                      VALUES (?, ?, ?, ?, ?, 1, ?)";
        $insert_stmt = $conn->prepare($insert_sql);

        if ($insert_stmt === false) {
            die("Error preparing insert statement: " . $conn->error);
        }

        $insert_stmt->bind_param("sisdss", $user_id, $product_id, $product_title, $product_price, $product_image, $selected_size);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $check_stmt->close();

    // Перенаправляем обратно на страницу товаров
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    // Если данные не были переданы, перенаправляем на главную
    header("Location: index.php");
    exit();
}

$conn->close();
?>