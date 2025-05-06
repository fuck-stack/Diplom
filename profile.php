<?php
session_start();

require_once 'db_config.php';
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

// Получаем ID пользователя из параметра URL
$profile_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Запрос данных пользователя
$sql = "SELECT id, username, email, avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Пользователь не найден");
}

// Запрос товаров пользователя
$products_sql = "SELECT * FROM news WHERE id = ? ORDER BY created_at DESC";
$products_stmt = $conn->prepare($products_sql);
$products_stmt->bind_param("i", $user_id);
$products_stmt->execute();
$products = $products_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Профиль <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-card {
            margin-top: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .product-card {
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'navbar.php'; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card profile-card">
                    <div class="card-body text-center">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['avatar']); ?>" 
                                 class="rounded-circle profile-avatar mb-3" alt="<?php echo htmlspecialchars($user['username']); ?>">
                        <?php else: ?>
                            <img src="https://bootdey.com/img/Content/avatar/avatar7.png" 
                                 class="rounded-circle profile-avatar mb-3" alt="<?php echo htmlspecialchars($user['username']); ?>">
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                        <p class="text-muted">Участник с <?php echo date("d.m.Y", strtotime(isset($user['created_at']) ? $user['created_at'] : 'now')); ?></p>
                        
                       
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>О пользователе</h5>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-envelope mr-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <!-- Дополнительная информация о пользователе -->
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Товары пользователя</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($products->num_rows > 0): ?>
                                <?php while ($product = $products->fetch_assoc()): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card product-card h-100">
                                            <?php if (!empty($product['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                                <?php if ($product['price'] > 0): ?>
                                                    <p class="card-text text-success font-weight-bold"><?php echo number_format($product['price'], 2); ?> руб.</p>
                                                <?php endif; ?>
                                                <a href="news.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Подробнее</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <p class="text-muted">Пользователь пока не размещал товары</p>
                                </div>
                            <?php endif; ?>
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

<?php
$stmt->close();
$products_stmt->close();
$conn->close();
?>