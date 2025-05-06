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
$sql = "SELECT * FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
}

$total_price = 0;
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа | Магазин товаров для дома</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
       
        body { 
            padding-top: 20px;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
     
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .checkout-container {
            display: flex;
            gap: 30px;
        }
        
        .checkout-form {
            flex: 2;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-summary {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        h1 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        h2 {
            font-size: 20px;
            margin-top: 25px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .row {
            display: flex;
            gap: 15px;
        }
        
        .row .form-group {
            flex: 1;
        }
        
        .payment-methods {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .payment-method {
            flex: 1;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: #3498db;
        }
        
        .payment-method.active {
            border-color: #3498db;
            background-color: #f0f8ff;
        }
        
        .payment-method input {
            margin-right: 10px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 18px;
            padding: 15px 0;
            border-top: 1px solid #eee;
            margin-top: 10px;
        }
        
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
       
        .cart-item { display: flex; margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .item-details { margin-left: 20px; flex-grow: 1; }
        .item-img img { max-width: 150px; height: auto; }
        .quantity-control { display: flex; align-items: center; margin: 10px 0; }
        .quantity-control input { width: 60px; text-align: center; margin: 0 10px; }
        
        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column;
            }
            
            .row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
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
    
    <div class="container">
  
            <div class="checkout-container">
                <div class="checkout-form">
                    <h1>Оформление заказа</h1>
                    
                    <h2>Контактная информация</h2>
                    <div class="form-group">
                        <label for="fullname">ФИО*</label>
                        <input type="text" id="fullname" required>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="email">Email*</label>
                            <input type="email" id="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Телефон*</label>
                            <input type="tel" id="phone" required>
                        </div>
                    </div>
                    
                    <h2>Адрес доставки</h2>
                    <div class="form-group">
                        <label for="address">Адрес*</label>
                        <input type="text" id="address" required>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="city">Город*</label>
                            <input type="text" id="city" required>
                        </div>
                        <div class="form-group">
                            <label for="postcode">Почтовый индекс</label>
                            <input type="text" id="postcode">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comment">Комментарий к заказу</label>
                        <textarea id="comment" rows="3"></textarea>
                    </div>
                    
                    <h2>Способ оплаты</h2>
                    <div class="payment-methods">
                        <label class="payment-method active">
                            <input type="radio" name="payment" checked> 
                            Картой онлайн
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment"> 
                            При получении
                        </label>
                    </div>
                    
                    <button type="submit" class="btn">Подтвердить заказ</button>
                </div>
                
                <div class="order-summary">
                    <h2>Ваш заказ</h2>
                    
                    <?php if (empty($cart)): ?>
                    <p>Ваша корзина пуста.</p>
                    <a href="index.php?page=news" class="btn btn-primary">Вернуться к товарам</a>
                <?php else: ?>
                    <?php foreach ($cart as $item): ?>
                        <?php 
                            $item_total = $item['product_price'] * $item['quantity'];
                            $total_price += $item_total;
                        ?>
                        <div class="cart-item">
                            <?php if (!empty($item['product_image'])): ?>
                                <div class="item-img">
                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_title']); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['product_title']); ?></h4>
                                <p>Цена: <?php echo number_format($item['product_price'], 2, '.', ' '); ?> руб.</p>
                                
                                <div class="quantity-control">
                                    <form method="post" action="update_cart.php" class="update-form">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        
                                        <!-- <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input">  -->
                                    </form>
                                </div>
                                
                                <p>Сумма: <?php echo number_format($item_total, 2, '.', ' '); ?> руб.</p>
                                <!-- <form method="post" action="remove_from_cart.php">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Удалить</button>
                                </form> -->
                            </div>
                        </div>
                    <?php endforeach; ?>

                
                <?php endif; ?>
                        
                        <div class="order-item">
                            <div>Доставка</div>
                            <div>Бесплатно</div>
                        </div>
                    
                    <div class="order-total">
                        <div>Итого</div>
                        <div><?php echo number_format($total_price, 2, '.', ' '); ?>  ₽</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Активация выбранного способа оплаты
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', () => {
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('active');
                });
                method.classList.add('active');
                method.querySelector('input').checked = true;
            });
        });
    </script>
</body>
</html>