<?php
// Подключаем конфигурацию и функции (предполагается, что они в index.php)
require_once 'index.php';

// Подключение к базе данных
$conn = new mysqli('localhost', 'root', '', 'games_news');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игровой мерч</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            padding-top: 20px;
            background-color: #f5f7fa;
        }
        .game-card {
            transition: transform 0.3s;
            margin-bottom: 30px;
            height: 100%;
        }
        .game-card:hover {
            transform: translateY(-5px);
        }
        .rating-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.1rem;
        }
        .genre-badge {
            margin-right: 5px;
        }
        .hero-section {
            background: 
            linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
            url('https://media.giphy.com/media/3o72F7RqPJ2qVYxK1G/giphy.gif')
            center/cover no-repeat;
            color: white;
            padding: 100px 0;
            margin-bottom: 40px;
            background-attachment: fixed;
            transition: background 0.3s ease;
        }
        .platform-icon {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #6c757d;
        }
        .size-option {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            cursor: pointer;
        }
        .size-option.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Герой-секция -->
    <header class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Геймерские Мерч 2025</h1>
            <a href="#games" class="btn btn-primary btn-lg mt-3">Смотреть</a>
        </div>
    </header>

    <!-- Основной контент -->
    <main class="container" id="games">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="border-bottom pb-2">Мерч</h2>
                <p>Закажи игровой мерч на свой вкус</p>
            </div>
        </div>

        <!-- Карточки товаров -->
        <div class="row">
            <?php
            // Получаем все товары из базы данных
            $sql = "SELECT * FROM products";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    // Разделяем категории и размеры
                    $categories = explode(',', $product['categories']);
                    $sizes = explode(',', $product['sizes']);
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 game-card">
                    <!-- Картинка товара -->
                    <img src="<?= htmlspecialchars($product['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['title']) ?>" style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                        
                        <!-- Категории товара -->
                        <div class="mb-2">
                            <?php foreach ($categories as $category): ?>
                                <?php 
                                $badge_class = 'bg-secondary';
                                if (trim($category) == 'Одежда') $badge_class = 'bg-primary';
                                if (trim($category) == 'Киберпанк') $badge_class = 'bg-info';
                                if (trim($category) == 'Эксклюзив') $badge_class = 'bg-warning';
                                ?>
                                <span class="badge <?= $badge_class ?> merch-badge"><?= trim($category) ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Описание товара -->
                        <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                        
                        <!-- Размеры -->
                        <div class="mb-3 sizes">
                            <?php foreach ($sizes as $size): ?>
                                <?php 
                                $size = trim($size);
                                $active_class = ($size == $product['default_size']) ? 'active' : '';
                                ?>
                                <span class="size-option <?= $active_class ?>" data-size="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Цена и кнопка -->
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="current-price fw-bold"><?= number_format($product['price'], 0, '', ' ') ?> ₽</span>
                            <form method="post" action="add_to_cart.php" class="add-to-cart-form">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <input type="hidden" name="product_title" value="<?= htmlspecialchars($product['title']) ?>">
    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
    <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['image_path']) ?>">
    
    <!-- Поле для размера -->
    <input type="hidden" name="selected_size" class="selected-size-input" value="<?= htmlspecialchars($product['default_size']) ?>">
    
    <!-- Если есть выбор цвета -->
    <?php if (isset($product['colors'])): ?>
        <input type="hidden" name="selected_color" class="selected-color-input" value="<?= htmlspecialchars($product['default_color']) ?>">
    <?php endif; ?>
    
    <button type="submit" name="add_to_cart" class="btn btn-primary">В корзину</button>
</form>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-info">Товары не найдены.</div></div>';
            }
            
            $conn->close();
            ?>
        </div>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Обработка выбора размера
    document.querySelectorAll('.size-option').forEach(size => {
        size.addEventListener('click', function() {
            // Находим родительский элемент карточки
            const card = this.closest('.game-card');
            
            // Удаляем активный класс у всех размеров в этой карточке
            card.querySelectorAll('.size-option').forEach(el => {
                el.classList.remove('active');
            });
            
            // Добавляем активный класс текущему размеру
            this.classList.add('active');
            
            // Устанавливаем выбранный размер в скрытое поле формы
            card.querySelector('.selected-size-input').value = this.dataset.size;
        });
    });

    // Обработка отправки формы
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Проверяем, выбран ли размер (если есть выбор размеров)
            const sizeInput = this.querySelector('.selected-size-input');
            if (sizeInput && !sizeInput.value) {
                e.preventDefault();
                alert('Пожалуйста, выберите размер');
            }
        });
    });
</script>
</body>
</html>