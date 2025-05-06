<?php
// session_start();
include 'index.php'; // ***ОЧЕНЬ ПЛОХО***
// include 'functions.php';

// Создаем соединение с базой данных
$conn = db_connect();

if (!$conn) {
    die("Ошибка подключения к базе данных.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Вызываем функцию login_user и передаем $conn
    if (login_user($conn, $username, $password)) {
        // Успешная авторизация
        header("Location: index.php");
        exit();
    } else {
        $login_error = "Неверное имя пользователя или пароль.";
    }
}

// ... HTML-форма для авторизации ...
$conn->close(); // Закрываем соединение
?>
<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
         body { 
            padding-top: 20px;
            background-color: #f5f7fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Вход</h1>
        <?php if (isset($login_error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $login_error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Имя пользователя:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Войти</button>  <!--  <-- Добавлена кнопка  -->
        </form>
        <p>Еще нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>