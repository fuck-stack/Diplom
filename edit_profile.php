<?php
session_start();

// Конфигурация базы данных
$db_host = 'localhost';
$db_name = 'games_news';
$db_user = 'root';
$db_pass = '';

// Функция подключения к БД
function db_connect() {
    global $db_host, $db_name, $db_user, $db_pass;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    return $conn;
}

// Проверка авторизации
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

// Получение данных пользователя
function get_user_data($user_id) {
    $conn = db_connect();
    $sql = "SELECT id, username, email, image, avatar, phone, address FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

// Обновление профиля
function update_user_profile($user_id, $username, $email, $phone, $address) {
    $conn = db_connect();
    $sql = "UPDATE users SET username = ?, email = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    
    $stmt->bind_param("ssssi", $username, $email, $phone, $address, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return true;
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        return "Ошибка при обновлении: " . $error;
    }
}

// Загрузка аватара
function upload_avatar($user_id, $avatar_file) {
    $conn = db_connect();
    
    // Проверка типа файла
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return "Допустимы только JPG, JPEG, PNG и GIF.";
    }
    
    // Проверка размера (максимум 2MB)
    if ($avatar_file['size'] > 2097152) {
        return "Максимальный размер - 2MB.";
    }
    
    // Чтение файла
    $avatar_data = file_get_contents($avatar_file['tmp_name']);
    
    // Обновление в БД
    $sql = "UPDATE users SET avatar = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return "Ошибка подготовки запроса: " . $conn->error;
    }
    
    $null = NULL;
    $stmt->bind_param("bi", $null, $user_id);
    $stmt->send_long_data(0, $avatar_data);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return true;
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        return "Ошибка загрузки: " . $error;
    }
}

// Основной код
if (!is_user_logged_in()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Получение текущих данных
$user_data = get_user_data($user_id);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['avatar'])) {
        // Обработка загрузки аватара
        $result = upload_avatar($user_id, $_FILES['avatar']);
        if ($result === true) {
            $success_message = "Аватар успешно обновлен!";
            $user_data = get_user_data($user_id); // Обновляем данные
        } else {
            $error_message = $result;
        }
    } else {
        // Обработка обновления профиля
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        if (empty($username) || empty($email)) {
            $error_message = "Имя и email обязательны";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Некорректный email";
        } else {
            $result = update_user_profile($user_id, $username, $email, $phone, $address);
            if ($result === true) {
                $success_message = "Профиль обновлен!";
                $_SESSION['username'] = $username;
                $user_data = get_user_data($user_id);
            } else {
                $error_message = $result;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="form-container">
            <h2 class="text-center mb-4">Редактирование профиля</h2>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <!-- Превью аватара -->
                <div class="text-center">
                    <?php if (!empty($user_data['avatar'])): ?>
                        <img src="" class="avatar-preview">
                    <?php else: ?>
                        <img src="" class="avatar-preview">
                    <?php endif; ?>
                </div>
                
                <!-- Поле для загрузки аватара -->
                <div class="mb-3">
                    <label for="avatar" class="form-label">Аватар</label>
                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                </div>
                
                <!-- Основные поля формы -->
                <div class="mb-3">
                    <label for="username" class="form-label">Имя пользователя</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?= htmlspecialchars($user_data['username']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($user_data['email']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Телефон</label>
                    <input type="text" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars(isset($user_data['phone']) ? $user_data['phone'] : ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Адрес</label>
                    <input type="text" class="form-control" id="address" name="address" 
                           value="<?php echo htmlspecialchars(isset($user_data['address']) ? $user_data['address'] : ''); ?>">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    <a href="user.php" class="btn btn-secondary">Назад к профилю</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Превью аватара перед загрузкой
        document.getElementById('avatar').addEventListener('change', function(e) {
            const preview = document.querySelector('.avatar-preview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>