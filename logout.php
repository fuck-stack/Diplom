<?php
// Подключаем конфигурацию и функции (предполагается, что они в index.php)
require_once 'index.php';

logout_user();
header("Location: index.php"); // Перенаправление на главную
exit();
?>