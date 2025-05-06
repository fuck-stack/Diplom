<?php
session_start();
// --- Конфигурация ---
$db_host = 'localhost';
$db_name = 'games_news';  // Имя базы данных
$db_user = 'root';        // Имя пользователя базы данных
$db_pass = '';            // Пароль пользователя базы данных

// --- Функции ---


// Функция для подключения к базе данных
function db_connect() {
    global $db_host, $db_name, $db_user, $db_pass;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die("Ошибка подключения к базе данных: " . $conn->connect_error);
    }
    return $conn;
}

// Функция для очистки входных данных
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Функция для регистрации пользователя
function register_user($username, $password, $email) {
    $conn = db_connect();

    $username = sanitize_input($username);
    $email = sanitize_input($email);
    $password = password_hash($password, PASSWORD_DEFAULT); // Хеширование пароля

    // Подготовленный запрос
    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $username, $password, $email); // "sss" означает три строки
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return true; // Успешная регистрация
        } else {
            echo "Ошибка при выполнении запроса: " . $stmt->error; // Вывод ошибки
            $stmt->close();
            $conn->close();
            return false; // Ошибка регистрации
        }
    } else {
        echo "Ошибка при подготовке запроса: " . $conn->error;
        $conn->close();
        return false;
    }
}

// Функция для проверки логина и пароля
function login_user($username, $password) {
    $conn = db_connect();
    $username = sanitize_input($username);

    // Подготовленный запрос
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result(); // Получаем результирующий набор

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Пароль верен
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $stmt->close();
                $conn->close();
                return true; // Успешный вход
            }
        }
        $stmt->close();
        $conn->close();
        return false; // Неверный логин или пароль
    } else {
        echo "Ошибка при подготовке запроса: " . $conn->error;
        $conn->close();
        return false;
    }
}


// Функция для проверки, авторизован ли пользователь
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}
// Функция для выхода из системы

function logout_user() {
    session_destroy();
}

// --- Обработка запросов ---

// Проверяем, отправлена ли форма регистрации
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    if (register_user($username, $password, $email)) {
        $register_success = true;
    } else {
        $register_error = "Ошибка при регистрации. Пожалуйста, попробуйте еще раз.";
    }
}

// Проверяем, отправлена ли форма входа
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    if (login_user($username, $password)) {
        header("Location: index.php"); // Перенаправление после успешного входа
        exit();
    } else {
        $login_error = "Неверное имя пользователя или пароль.";
    }
}

// --- Заголовок HTML ---
?>
<!DOCTYPE html>
<html>
<head>
    <title>GameKeys - Магазин цифровых ключей игр</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            padding-top: 20px;
            background-color: #f5f7fa;
        }
        .news-item { 
            margin-bottom: 20px; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 10px; 
        }
        
        /* Стили для секции с играми */
        .game-section {
            margin: 30px 0;
        }
        
        .game-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
            background: white;
        }
        
        .game-card:hover {
            transform: translateY(-5px);
        }
        
        .game-img {
            height: 180px;
            object-fit: cover;
        }
        
        .game-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.8rem;
        }
        
        .platform-icon {
            margin-right: 5px;
            color: #6c757d;
        }
        
        .price {
            font-weight: bold;
            color: #e74c3c;
            font-size: 1.2rem;
        }
        
        .old-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .discount {
            color: #2ecc71;
            font-weight: bold;
        }
        
        .section-title {
            margin: 30px 0 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title:after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'navbar.php'; ?>
        
        <!-- Секция с цифровыми ключами -->
        <div class="game-section">
            <h2 class="section-title">Цифровые ключи игр</h2>
            
            <div class="row">
                <!-- Игра 1 -->
                <div class="col-md-4">
                    <div class="card game-card">
                        <span class="badge badge-success game-badge">-20%</span>
                        <img src="https://images.unsplash.com/photo-1551103782-8ab07afd45c1?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top game-img" alt="Cyber Odyssey">
                        <div class="card-body">
                            <h5 class="card-title">Cyber Odyssey</h5>
                            <p class="card-text">Новаторская RPG в стиле киберпанк с потрясающей графикой.</p>
                            <div class="mb-2">
                                <i class="fab fa-windows platform-icon" title="Windows"></i>
                                <i class="fab fa-playstation platform-icon" title="PlayStation"></i>
                                <i class="fab fa-xbox platform-icon" title="Xbox"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="price">1 599 ₽</span>
                                    <span class="old-price">1 999 ₽</span>
                                    <span class="discount ml-2">20%</span>
                                </div>
                                <button class="btn btn-primary btn-sm">Купить</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Игра 2 -->
                <div class="col-md-4">
                    <div class="card game-card">
                        <span class="badge badge-danger game-badge">Хит</span>
                        <img src="https://images.unsplash.com/photo-1542751371-adc38448a05e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top game-img" alt="Space Adventure">
                        <div class="card-body">
                            <h5 class="card-title">Space Adventure</h5>
                            <p class="card-text">Космическая стратегия с элементами экшена и исследованием планет.</p>
                            <div class="mb-2">
                                <i class="fab fa-windows platform-icon" title="Windows"></i>
                                <i class="fab fa-apple platform-icon" title="Mac"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="price">2 299 ₽</span>
                                </div>
                                <button class="btn btn-primary btn-sm">Купить</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Игра 3 -->
                <div class="col-md-4">
                    <div class="card game-card">
                        <span class="badge badge-warning game-badge">Новинка</span>
                        <img src="https://images.unsplash.com/photo-1489850846882-35ef10a4b480?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top game-img" alt="Fantasy Kingdom">
                        <div class="card-body">
                            <h5 class="card-title">Fantasy Kingdom</h5>
                            <p class="card-text">Фэнтезийная RPG с открытым миром и нелинейным сюжетом.</p>
                            <div class="mb-2">
                                <i class="fab fa-windows platform-icon" title="Windows"></i>
                                <i class="fab fa-playstation platform-icon" title="PlayStation"></i>
                                <i class="fab fa-xbox platform-icon" title="Xbox"></i>
                                <i class="fab fa-apple platform-icon" title="Mac"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="price">2 799 ₽</span>
                                    <span class="old-price">3 499 ₽</span>
                                    <span class="discount ml-2">20%</span>
                                </div>
                                <button class="btn btn-primary btn-sm">Купить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Фильтры и сортировка -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-control">
                                        <option>Все платформы</option>
                                        <option>PC (Windows)</option>
                                        <option>PlayStation</option>
                                        <option>Xbox</option>
                                        <option>Mac</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control">
                                        <option>Все жанры</option>
                                        <option>RPG</option>
                                        <option>Экшен</option>
                                        <option>Стратегии</option>
                                        <option>Симуляторы</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control">
                                        <option>Сортировать по</option>
                                        <option>Цена (по возрастанию)</option>
                                        <option>Цена (по убыванию)</option>
                                        <option>Популярности</option>
                                        <option>Новизне</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" placeholder="Поиск игр...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Дополнительные игры -->
            <div class="row mt-4">
                <!-- Игра 4 -->
                <div class="col-md-3">
                    <div class="card game-card">
                        <img src="https://images.unsplash.com/photo-1542751371-adc38448a05e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top game-img" alt="Racing Pro">
                        <div class="card-body">
                            <h5 class="card-title">Racing Pro</h5>
                            <div class="mb-2">
                                <i class="fab fa-windows platform-icon" title="Windows"></i>
                                <i class="fab fa-playstation platform-icon" title="PlayStation"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">1 199 ₽</span>
                                <button class="btn btn-outline-primary btn-sm">Купить</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Игра 5 -->
                <div class="col-md-3">
                    <div class="card game-card">
                        <img src="https://images.unsplash.com/photo-1612287230202-1ff1d85d1bdf?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top game-img" alt="Zombie Survival">
                        <div class="card-body">
                            <h5 class="card-title">Zombie Survival</h5>
                            <div class="mb-2">
                                <i class="fab fa-windows platform-icon" title="Windows"></i>
                                <i class="fab fa-xbox platform-icon" title="Xbox"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">899 ₽</span>
                                <button class="btn btn-outline-primary btn-sm">Купить</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Игра 6 -->
                <div class="col-md-3">
                    <div class="card game-card">
                        <span class="badge badge-info game-badge">Предзаказ</span>
                        <img src="https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top game-img" alt="Cyber War">
                        <div class="card-body">
                            <h5 class="card-title">Cyber War</h5>
                            <div class="mb-2">
                                <i class="fab fa-windows platform-icon" title="Windows"></i>
                                <i class="fab fa-playstation platform-icon" title="PlayStation"></i>
                                <i class="fab fa-xbox platform-icon" title="Xbox"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">2 499 ₽</span>
                                <button class="btn btn-outline-primary btn-sm">Предзаказ</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Игра 7 -->
                <div class="col-md-3">
                    <div class="card game-card">
                        <span class="badge badge-success game-badge">-30%</span>
                        <img src="https://images.unsplash.com/photo-1586182987320-4f376d39d787?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top game-img" alt="Football 2023">
                        <div class="card-body">
                            <h5 class="card-title">Football 2023</h5>
                            <div class="mb-2">
                                <i class="fab fa-windows platform-icon" title="Windows"></i>
                                <i class="fab fa-playstation platform-icon" title="PlayStation"></i>
                                <i class="fab fa-xbox platform-icon" title="Xbox"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="price">1 749 ₽</span>
                                    <span class="old-price">2 499 ₽</span>
                                </div>
                                <button class="btn btn-outline-primary btn-sm">Купить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<!-- // include 'create_news_form.php';
                // Форма для создания новости (только для авторизованных пользователей)
                // echo "<h1 class='mt-4'>Создать новость2</h1>";
                // echo "<form action='create_news.php' method='POST'>";
                // echo "<div class='form-group'>";
                // echo "<label for='title'>Заголовок</label>";
                // echo "<input type='text' class='form-control' id='title' name='title' required>";
                // echo "</div>";
                // echo "<div class='form-group'>";
                // echo "<label for='content'>Содержание</label>";
                // echo "<textarea class='form-control' id='content' name='content' rows='5' required></textarea>";
                // echo "</div>";
                // echo "<button type='submit' class='btn btn-primary'>Опубликовать</button>";
                // echo "</form>"; -->