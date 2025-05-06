<?php
// Подключаем конфигурацию и функции (предполагается, что они в index.php)
require_once 'index.php';
// 1. Чтение данных из JSON-файла
$json_data = file_get_contents("scripts/stopgame_news.json");
$news_data = json_decode($json_data, true);
// Проверка, что JSON был успешно распарсен
if ($news_data === null) {
    die("Ошибка при парсинге JSON: " . json_last_error_msg());
}
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
            <h1 class="display-4 fw-bold">Актуальные новости 2025</h1>
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
    <?php foreach ($news_data as $news_item): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card game-card h-100">
               
            <img src="<?php echo htmlspecialchars($news_item['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news_item['image_url']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($news_item['title']); ?></h5>
                   
                   
                    <a href="<?php echo htmlspecialchars($news_item['link']); ?>" class="btn btn-outline-primary">Подробнее</a>
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
