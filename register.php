<?php
// Подключаем конфигурацию и функции (предполагается, что они в index.php)
require_once 'index.php';

// Проверяем, отправлена ли форма регистрации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    if (register_user($username, $password, $email)) {
        $register_success = true;
    } else {
        $register_error = "Ошибка при регистрации. Пожалуйста, попробуйте еще раз.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
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
        <h1>Регистрация</h1>
        <?php if (isset($register_success)): ?>
            <div class="alert alert-success" role="alert">
                Вы успешно зарегистрированы! <a href="login.php">Войти</a>
            </div>
        <?php endif; ?>
        <?php if (isset($register_error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $register_error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Имя пользователя:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>