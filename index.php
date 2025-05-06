<?php
session_start();
// --- Конфигурация ---
$db_host = 'localhost';
$db_name = 'games_news';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

// --- Функции ---
function db_connect() {
    global $db_host, $db_name, $db_user, $db_pass;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die("Ошибка подключения к базе данных: " . $conn->connect_error);
    }
    return $conn;
}
$conn = db_connect();

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function register_user($conn, $username, $password, $email) {
    $username = sanitize_input($username);
    $email = sanitize_input($email);
    $password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $username, $password, $email);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            echo "Ошибка при выполнении запроса: " . $stmt->error;
            $stmt->close();
            return false;
        }
    } else {
        echo "Ошибка при подготовке запроса: " . $conn->error;
        return false;
    }
}

function login_user($conn, $username, $password) {
    $username = sanitize_input($username);

    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    } else {
        echo "Ошибка при подготовке запроса: " . $conn->error;
        return false;
    }
}

function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

function logout_user() {
    session_destroy();
}

// --- Обработка запросов ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    if (register_user($conn, $username, $password, $email)) {
        $register_success = true;
    } else {
        $register_error = "Ошибка при регистрации. Пожалуйста, попробуйте еще раз.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    if (login_user($conn, $username, $password)) {
        header("Location: index.php");
        exit();
    } else {
        $login_error = "Неверное имя пользователя или пароль.";
    }
}

// --- Обработка создания новости ---
$news_success = false;
$news_error = '';

if (isset($_GET['page']) && $_GET['page'] == 'create_news' && is_user_logged_in() && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $price = $_POST['price'];
    $image_path = null;
    
    $title = htmlspecialchars(trim($title));
    $content = htmlspecialchars(trim($content));
    
    if (!is_numeric($price)) {
        $news_error = "Цена должна быть числом.";
    } else {
        $price = floatval($price);
        if ($price < 0) {
            $news_error = "Цена не может быть отрицательной.";
        }
    }
    
    if (empty($news_error)) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/diplom/img/news/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $original_name = basename($_FILES['image']['name']);
            $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_types)) {
                $new_filename = uniqid() . '.' . $file_ext;
                $target_file = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = '/diplom/img/news/' . $new_filename;
                } else {
                    $news_error = "Ошибка при загрузке файла.";
                }
            } else {
                $news_error = "Недопустимый тип файла. Разрешены только JPG, PNG, GIF.";
            }
        }
    }
    
    if (empty($news_error)) {
        $conn->begin_transaction();
        
        try {
            if ($image_path) {
                $sql = "INSERT INTO news (title, content, price, image_path, created_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssds", $title, $content, $price, $image_path);
            } else {
                $sql = "INSERT INTO news (title, content, price, created_at) VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssd", $title, $content, $price);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Ошибка при создании товара: " . $stmt->error);
            }
            
            $product_id = $conn->insert_id;
            $stmt->close();
            
            $user_id = $_SESSION['user_id'];
            $quantity = 1;
            
            $sql_cart = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
            $stmt_cart = $conn->prepare($sql_cart);
            $stmt_cart->bind_param("iii", $user_id, $product_id, $quantity);
            
            if (!$stmt_cart->execute()) {
                throw new Exception("Ошибка при добавлении в корзину: " . $stmt_cart->error);
            }
            $stmt_cart->close();
            
            $conn->commit();
            $news_success = true;
        } catch (Exception $e) {
            $conn->rollback();
            $news_error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Торговая площадка</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            padding-top: 20px;
            background-color: #f5f7fa;
        }
        .news-item { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .btn-add-to-cart { margin-top: 10px; }
        .comment-avatar a {
            display: inline-block;
            text-decoration: none;
        }
        .comment-header a {
            color: inherit;
            text-decoration: none;
        }
        .comment-header a:hover {
            text-decoration: underline;
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="container">
    <?php include 'navbar.php'; ?>
    
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_comment"]) && is_user_logged_in()) {
        $news_id = intval($_POST["news_id"]);
        $comment_text = trim($_POST["comment_text"]);
        $user_id = $_SESSION['user_id'];
        
        if (!empty($comment_text)) {
            $insert_sql = "INSERT INTO comments (news_id, user_id, comment_text) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("iis", $news_id, $user_id, $comment_text);
            
            if ($stmt->execute()) {
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                echo "<div class='alert alert-danger'>Ошибка при добавлении комментария</div>";
            }
        }
    }

    if (isset($_GET['page']) && $_GET['page'] == 'news') {
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
        $order_by = '';
        
        switch ($sort) {
            case 'price_asc':
                $order_by = 'price ASC';
                break;
            case 'price_desc':
                $order_by = 'price DESC';
                break;
            case 'newest':
            default:
                $order_by = 'created_at DESC';
                break;
        }

        echo "<br>";
        echo "<div class='container'>";
        echo "<div class='sorting-options mb-4 '>";
        echo "<span class='me-2'>Сортировать:</span>";
        echo "<div class='btn-group'>";
        echo "<a href='?page=news&sort=newest' class='btn btn-sm btn-outline-secondary" . ($sort == 'newest' ? ' active' : '') . "'>Новые</a>";
        echo "<a href='?page=news&sort=price_asc' class='btn btn-sm btn-outline-secondary" . ($sort == 'price_asc' ? ' active' : '') . "'>Дешевые</a>";
        echo "<a href='?page=news&sort=price_desc' class='btn btn-sm btn-outline-secondary" . ($sort == 'price_desc' ? ' active' : '') . "'>Дорогие</a>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        $sql = "SELECT * FROM news ORDER BY $order_by";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<h1 class='mt-4'>Торговая площадка</h1>";
            echo "<h4 class='mt-4'>Товары от пользователей</h4>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<div class='news-item mb-4 p-3 border rounded bg-light'>";
                echo "<h2>" . htmlspecialchars($row["title"]) . "</h2>";
                
                if (isset($row['price']) && $row['price'] > 0) {
                    echo "<div class='price mb-2'><strong>Цена: " . number_format($row['price'], 2, '.', ' ') . " руб.</strong></div>";
                }
                
                if (!empty($row["image_path"])) {
                    echo "<img src='" . htmlspecialchars($row["image_path"]) . "' alt='" . htmlspecialchars($row["title"]) . "' class='img-fluid mb-3' style='max-width: 300px;'>";
                }
                
                echo "<div class='news-content mb-3'>" . htmlspecialchars($row["content"]) . "</div>";
                echo "<small class='text-muted d-block mb-3'>Опубликовано: " . htmlspecialchars($row["created_at"]) . "</small>";
                
                if (is_user_logged_in()) {
                    echo "<form action='add_to_cart.php' method='POST'>";
                    echo "<input type='hidden' name='product_id' value='" . htmlspecialchars($row['id']) . "'>";
                    echo "<input type='hidden' name='product_title' value='" . htmlspecialchars($row['title']) . "'>";
                    echo "<input type='hidden' name='product_price' value='" . $row['price'] . "'>";
                    echo "<input type='hidden' name='product_image' value='" . htmlspecialchars($row['image_path']) . "'>";
                    echo "<button type='submit' name='add_to_cart'>Добавить в корзину</button>";
                    echo "</form>";
                } else {
                    echo "<div class='alert alert-info'>Чтобы добавить товар в корзину, <a href='login.php'>войдите</a> или <a href='register.php'>зарегистрируйтесь</a></div>";
                }
                
                echo "<div class='comments-section mt-4'>";
                echo "<h5>Комментарии:</h5>";
                
               // В блоке вывода комментариев замените код на этот:
                    $comments_sql = "SELECT c.*, u.id as user_id, u.username, u.avatar FROM comments c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.news_id = ? 
                    ORDER BY c.created_at DESC";
                    $stmt = $conn->prepare($comments_sql);
                    $stmt->bind_param("i", $row['id']);
                    $stmt->execute();
                    $comments_result = $stmt->get_result();

                    if ($comments_result->num_rows > 0) {
                    while ($comment = $comments_result->fetch_assoc()) {
                    echo "<div class='comment mb-3 p-3 bg-light rounded d-flex'>";

                    // Кликабельная аватарка
                    echo "<div class='comment-avatar mr-3'>";
                    echo "<a href='profile.php?user_id=".$comment['user_id']."'>";
                    if (!empty($comment['avatar'])) {
                    echo "<img src='data:image/jpeg;base64,".base64_encode($comment['avatar'])."' 
                    class='rounded-circle' width='50' height='50' alt='".htmlspecialchars($comment['username'])."'>";
                    } else {
                    echo "<img src='https://bootdey.com/img/Content/avatar/avatar7.png' 
                    class='rounded-circle' width='50' height='50' alt='".htmlspecialchars($comment['username'])."'>";
                    }
                    echo "</a>";
                    echo "</div>";

                    // Содержимое комментария
                    echo "<div class='comment-content'>";
                    echo "<div class='comment-header mb-2'>";
                    echo "<strong class='d-block'><a href='profile.php?user_id=".$comment['user_id']."'>" . htmlspecialchars($comment['username']) . "</a></strong>";
                    echo "<small class='text-muted'>" . date("d.m.Y H:i", strtotime($comment['created_at'])) . "</small>";
                    echo "</div>";
                    echo "<p class='mb-0'>" . htmlspecialchars($comment['comment_text']) . "</p>";
                    echo "</div>";

                    echo "</div>";
                    }
                    } else {
                    echo "<p class='text-muted'>Пока нет комментариев</p>";
                    }
                
                if (is_user_logged_in()) {
                    echo "<form method='post' class='mt-3 add-comment-form'>";
                    echo "<input type='hidden' name='news_id' value='" . $row['id'] . "'>";
                    echo "<div class='form-group'>";
                    echo "<textarea class='form-control' name='comment_text' rows='2' placeholder='Ваш комментарий...' required></textarea>";
                    echo "</div>";
                    echo "<button type='submit' name='add_comment' class='btn btn-sm btn-primary'>Добавить комментарий</button>";
                    echo "</form>";
                } else {
                    echo "<div class='alert alert-info py-2 px-3'>Чтобы оставить комментарий, <a href='login.php'>войдите</a> или <a href='register.php'>зарегистрируйтесь</a></div>";
                }
                
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='mt-4'>Товаров нет.</p>";
        }
    } elseif (isset($_GET['page']) && $_GET['page'] == 'create_news' && is_user_logged_in()) {
        include 'create_news_form.php';
        
        if ($news_success) {
            echo "<div class='alert alert-success' role='alert'>Товар успешно создан!</div>";
        }
        if ($news_error) {
            echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($news_error) . "</div>";
        }
    }
    ?>
        
    <?php
    if (isset($_GET['page']) && $_GET['page'] == 'news') {
    } elseif (isset($_GET['page']) && $_GET['page'] == 'create_news' && is_user_logged_in()) {
    } elseif (basename($_SERVER['PHP_SELF']) == 'index.php') {
        include 'carusel.php';
        include 'block_menu.php';
    }
    ?>
</div>
    
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>