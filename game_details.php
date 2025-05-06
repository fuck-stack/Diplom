<?php
require_once 'db_config.php';
$conn->set_charset("utf8mb4");

// Получаем ID игры из URL
$game_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Запрос к базе данных
$sql = "SELECT * FROM games WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $game = $result->fetch_assoc();
    
    // Форматируем данные
    $game['release_date_formatted'] = date("d F Y", strtotime($game['release_date']));
    $game['genres'] = explode(", ", $game['genre']);
} else {
    die("Игра не найдена");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['name']); ?> - Детали</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .game-header {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6)), url('<?php echo htmlspecialchars($game['image_url']); ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            padding: 120px 0;
            margin-bottom: 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .game-title {
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .rating-badge {
            font-size: 1.2rem;
            padding: 8px 16px;
            border-radius: 20px;
        }
        
        .platform-icon {
            font-size: 2rem;
            margin-right: 15px;
            color: #6c757d;
            transition: all 0.3s;
        }
        
        .platform-icon:hover {
            color: #495057;
            transform: scale(1.1);
        }
        
        .game-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .game-card:hover {
            transform: translateY(-5px);
        }
        
        .info-list .list-group-item {
            border-left: none;
            border-right: none;
            padding: 15px 0;
        }
        
        .genre-badge {
            font-size: 0.9rem;
            padding: 8px 12px;
            margin-right: 8px;
            margin-bottom: 8px;
            border-radius: 20px;
        }
        
        .back-button {
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .back-button:hover {
            transform: translateX(-5px);
        }
        
        @media (max-width: 768px) {
            .game-header {
                padding: 80px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container"><?php include'index.php'?>
    <header class="game-header">
        <div class="container">
            <h1 class="game-title display-4 mb-3"><?php echo htmlspecialchars($game['name']); ?></h1>
            <div class="rating-badge badge bg-success">
                <?php echo htmlspecialchars($game['rating']); ?>/10
            </div>
        </div>
    </header>

    <main class="container mb-5">
        
        <div class="row">
            <div class="col-lg-8">
                <div class="mb-5">
                    <h2 class="mb-4">Описание</h2>
                    <p class="lead" style="line-height: 1.8;"><?php echo htmlspecialchars($game['description']); ?></p>
                </div>
                
                <div class="mb-5">
                    <h2 class="mb-4">Жанры</h2>
                    <div>
                        <?php foreach ($game['genres'] as $genre): ?>
                            <span class="badge bg-primary genre-badge"><?php echo htmlspecialchars(trim($genre)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card game-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Информация</h5>
                        <ul class="list-group list-group-flush info-list">
                            <li class="list-group-item">
                                <strong>Дата выхода:</strong> 
                                <span class="float-right"><?php echo htmlspecialchars($game['release_date_formatted']); ?></span>
                            </li>
                            <li class="list-group-item">
                                <strong>Платформы:</strong>
                                <div class="mt-3 text-center">
                                    <i class="fab fa-windows platform-icon" title="Windows"></i>
                                    <i class="fab fa-playstation platform-icon" title="PlayStation 5"></i>
                                    <i class="fab fa-xbox platform-icon" title="Xbox Series X"></i>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <strong>Цена:</strong> 
                                <span class="float-right text-success font-weight-bold"><?php echo number_format($game['price'], 2, '.', ' '); ?> ₽</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <a href="page_top_relis.php" class="btn btn-outline-secondary back-button">
                    <i class="fas fa-arrow-left mr-2"></i>Вернуться к списку
                </a>
            </div>
        </div>
    </main></div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>