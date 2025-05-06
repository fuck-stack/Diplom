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

// Получаем товары из корзины
$sql = "SELECT cart.*, products.description 
        FROM cart 
        LEFT JOIN products ON cart.product_id = products.id 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
$total_price = 0;

while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
    $total_price += $row['product_price'] * $row['quantity'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            padding-top: 20px; 
            background-color: #f5f7fa;
        }
        .cart-item {
            display: flex; 
            margin-bottom: 20px; 
            padding: 15px; 
            border: 1px solid #ddd; 
            border-radius: 5px;
            background-color: white;
        }
        .item-details { 
            margin-left: 20px; 
            flex-grow: 1; 
        }
        .item-img img { 
            max-width: 150px; 
            height: auto;
            border-radius: 5px;
        }
        .quantity-control { 
            display: flex; 
            align-items: center; 
            margin: 10px 0; 
        }
        .quantity-control input { 
            width: 60px; 
            text-align: center; 
            margin: 0 10px; 
        }
        .size-badge {
            background-color: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-left: 10px;
        }
        .total-price {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="index.php">Торговая площадка</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php?page=news">Товары</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=create_news">Добавить товар</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Корзина</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Выйти</a></li>
                    <li class="nav-item"><a class="nav-link" href="user.php">Профиль</a></li>
                </ul>
            </div>
        </nav>

        <h1 class="mb-4">Ваша корзина</h1>

        <?php if (empty($cart)): ?>
            <div class="alert alert-info">
                Ваша корзина пуста.
                <a href="index.php?page=news" class="alert-link">Вернуться к товарам</a>
            </div>
        <?php else: ?>
            <?php foreach ($cart as $item): ?>
                <?php 
                    $item_total = $item['product_price'] * $item['quantity'];
                ?>
                <div class="cart-item">
                    <?php if (!empty($item['product_image'])): ?>
                        <div class="item-img">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_title']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="item-details">
                        <div class="d-flex align-items-center">
                            <h4 class="mb-0"><?php echo htmlspecialchars($item['product_title']); ?></h4>
                            <?php if (!empty($item['size'])): ?>
                                <span class="size-badge">Размер: <?php echo htmlspecialchars($item['size']); ?></span>
                            <?php endif; ?>

                            <?php if (!empty($item['color'])): ?>
                                <p>Цвет: <?= htmlspecialchars($item['color']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-muted mt-2">Цена: <?php echo number_format($item['product_price'], 2, '.', ' '); ?> ₽</p>
                        
                        <div class="quantity-control">
                            <form method="post" action="update_cart.php" class="update-form">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="button" class="btn btn-sm btn-outline-secondary minus-btn">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" class="quantity-input form-control">
                                <button type="button" class="btn btn-sm btn-outline-secondary plus-btn">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="submit" class="btn btn-sm btn-primary ml-2">
                                    <i class="fas fa-sync-alt"></i> Обновить
                                </button>
                            </form>
                        </div>
                        
                        <p class="font-weight-bold mt-2">Сумма: <?php echo number_format($item_total, 2, '.', ' '); ?> ₽</p>
                        
                        <form method="post" action="remove_from_cart.php" class="mt-3">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Удалить
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="total-price">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Общая сумма:</h4>
                    <h4 class="mb-0"><?php echo number_format($total_price, 2, '.', ' '); ?> ₽</h4>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php?page=news" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-left"></i> Продолжить покупки
                    </a>
                    <a href="checkout.php" class="btn btn-success btn-lg">
                        <i class="fas fa-credit-card"></i> Оформить заказ
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Обработка кнопок + и - для количества товаров
        $(document).ready(function() {
            $('.plus-btn').click(function() {
                const input = $(this).siblings('.quantity-input');
                input.val(parseInt(input.val()) + 1);
            });

            $('.minus-btn').click(function() {
                const input = $(this).siblings('.quantity-input');
                if (parseInt(input.val()) > 1) {
                    input.val(parseInt(input.val()) - 1);
                }
            });

            // AJAX обновление количества
            $('.update-form').submit(function(e) {
                e.preventDefault();
                
                const form = $(this);
                const formData = form.serialize();
                
                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: formData,
                    success: function(response) {
                        if (response === 'success') {
                            location.reload();
                        } else {
                            alert('Ошибка при обновлении корзины');
                        }
                    },
                    error: function() {
                        alert('Ошибка при обновлении корзины');
                    }
                });
            });
        });
    </script>
</body>
</html>