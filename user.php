<?php
session_start();
// --- Конфигурация ---
$db_host = 'localhost';
$db_name = 'games_news';
$db_user = 'root';
$db_pass = '';

// --- Функции ---
function db_connect() {
    global $db_host, $db_name, $db_user, $db_pass;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    return $conn;
}

function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_data($user_id) {
    $conn = db_connect();
    $sql = "SELECT id, username, email, avatar, phone, address FROM users WHERE id = ?";
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
    
    // Устанавливаем значения по умолчанию
    // $user['phone'] = $user['phone'] ?? '+7 (123) 456-7890';
    // $user['address'] = $user['address'] ?? 'Москва, Россия';
    
    return $user;
}

function upload_avatar($user_id, $avatar_file) {
    $conn = db_connect();
    
    // Проверка типа файла
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return "Допустимы только JPG, JPEG, PNG и GIF.";
    }
    
    // Проверка MIME-типа
    $image_info = @getimagesize($avatar_file['tmp_name']);
    if ($image_info === false) {
        return "Файл не является изображением.";
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

// --- Основной код ---
if (!is_user_logged_in()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$user_data = get_user_data($user_id);

// Обработка загрузки аватарки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $result = upload_avatar($user_id, $_FILES['avatar']);
    if ($result !== true) {
        $error_message = $result;
    } else {
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            padding-top: 20px;
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: none;
            transition: transform 0.3s;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 30px 0 20px;
            text-align: center;
            color: white;
        }
        
        .avatar-wrapper {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .profile-title {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 20px;
        }
        
        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: opacity 0.3s;
        }
        
        .social-links a:hover {
            opacity: 0.7;
            color: white;
        }
        
        .profile-details {
            padding: 30px;
        }
        
        .detail-item {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .detail-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(67, 97, 238, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0;
        }
        
        .detail-value {
            color: #6c757d;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .stats-title {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .skill-name {
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .btn-edit {
            background-color: var(--primary-color);
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .btn-edit:hover {
            background-color: var(--secondary-color);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <?php include 'navbar.php'; ?>
        </div>
    </div>
</div>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <div class="profile-card">
                <div class="profile-header">
                    <div class="avatar-wrapper">
                        <?php if (!empty($user_data['avatar'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($user_data['avatar']); ?>" 
                                 class="avatar-img" alt="Аватар">
                        <?php else: ?>
                            <img src="https://bootdey.com/img/Content/avatar/avatar7.png" 
                                 class="avatar-img" alt="Аватар">
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data" class="upload-form">
                            <label for="avatar-upload" class="upload-btn">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;">
                            <button type="submit" style="display: none;"></button>
                        </form>
                    </div>
                    <h1 class="profile-name"><?php echo htmlspecialchars($user_data['username']); ?></h1>
                    <p class="profile-title">Продавец</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-vk"></i></a>    
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-telegram"></i></a>
                    </div>
                </div>
                
                <div class="profile-details">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="detail-label">Email</h6>
                                    <p class="detail-value"><?php echo htmlspecialchars($user_data['email']); ?></p>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h6 class="detail-label">Телефон</h6>
                                    <p class="detail-value"><?php echo htmlspecialchars($user_data['phone']); ?></p>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="detail-label">Адрес</h6>
                                    <p class="detail-value"><?php echo htmlspecialchars($user_data['address']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="stats-card">
                                <h6 class="stats-title">Уровень</h6>
                                <div class="skill">
                                    <div class="skill-name">Покупка</div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 90%"></div>
                                    </div>
                                </div>
                                <div class="skill">
                                    <div class="skill-name">Продажа</div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 80%"></div>
                                    </div>
                                </div>
                               
                            </div>
                            
                            <div class="text-center">
                                <a href="edit_profile.php" class="btn btn-primary btn-edit">
                                    <i class="fas fa-edit me-2"></i>Редактировать профиль
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('avatar-upload').addEventListener('change', function() {
        this.closest('form').submit();
    });
</script>
</body>
</html>