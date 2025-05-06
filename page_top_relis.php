<?php
include_once 'index.php';
// Подключаем конфигурацию и функции (предполагается, что они в index.php)
include_once 'db_config.php';

if (!isset($conn)) {
    die("Database connection not established");
    
}
$conn->set_charset("utf8mb4");
// 2. Запрос к базе данных (получаем данные первой игры)
$sql = "SELECT * FROM games"; // Получаем только первую игру

// Выполняем запрос
$result = $conn->query($sql); // Эта строка отсутствовала в вашем коде
$games = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $games[] = [
            'id' => $row['id'],
            'title' => $row['name'],
            'rating' => $row['rating'],
            'genres' => explode(", ", $row['genre']),
            'description' => $row['description'],
            'descriptionfull' => $row['full_description'],
            'image_url' => $row['image_url'],
           
            'release_date_formatted' => date("d F Y", strtotime($row['release_date'])),
            // 'platforms' => explode(", ", $row['platform'])
        ];
    }
} else {
    echo "<div class='alert alert-warning'>Игр не найдено.</div>";
}


// $conn->query("UPDATE games SET name = 'Assassin’s Creed', description = $description WHERE id = 1");
// 4. Закрытие соединения с базой данных
$conn->close();
?>


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
            /* Затемнение поверх гифки (важно для читаемости текста) */
            linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
            /* Гифка с игровым моментом */
            url('https://media.giphy.com/media/3o72F7RqPJ2qVYxK1G/giphy.gif')
            center/cover no-repeat;
    
            color: white;
            padding: 100px 0;
            margin-bottom: 40px;
            
            /* Дополнительные параметры для плавности */
            background-attachment: fixed; /* Фиксированный фон при скролле */
            transition: background 0.3s ease; /* Плавное изменение фона */
        }
        .platform-icon {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #6c757d;
        }
    </style>
   
</head>
<body>
<div class="container">

    <!-- Герой-секция -->
    <header class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Лучшие игры апреля 2025</h1>
            <p class="lead">Топ-10 самых ожидаемых и впечатляющих игр этого месяца</p>
            <a href="#games" class="btn btn-primary btn-lg mt-3">Смотреть подборку</a>
        </div>
    </header>

    <!-- Основной контент -->
    <main class="container" id="games">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="border-bottom pb-2">Топ-10 игр месяца</h2>
                <p>Наши редакторы выбрали самые достойные игры, вышедшие в апреле 2025 года</p>
            </div>
        </div>

        <!-- Карточки игр -->
<div class="row">
    <?php foreach ($games as $game): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card game-card h-100">
                <span class="badge bg-success rating-badge"><?php echo $game['rating']; ?>/10</span>
                <img src="<?php echo $game['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($game['title']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($game['title']); ?></h5>
                    <div class="mb-2">
                        <?php foreach ($game['genres'] as $genre_item): ?>
                            <span class="badge bg-primary genre-badge"><?php echo htmlspecialchars(trim($genre_item)); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p class="card-text"><?php echo htmlspecialchars($game['description']); ?></p>
                    <div class="mb-3">
                    <i class="fab fa-windows platform-icon" title="Windows"></i>
                            <i class="fab fa-playstation platform-icon" title="PlayStation 5"></i>
                            <i class="fab fa-xbox platform-icon" title="Xbox Series X"></i>
                    </div>
                    <a href="game_details.php?id=<?php echo $game['id']; ?>" class="btn btn-outline-primary">Подробнее</a>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <small class="text-muted">Вышла <?php echo $game['release_date_formatted']; ?></small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
           

        <!-- Пагинация -->
        <nav aria-label="Page navigation" class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Назад</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Вперед</a>
                </li>
            </ul>
        </nav>
    </main>

    <!-- Футер -->

    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
