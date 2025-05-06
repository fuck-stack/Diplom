
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Торговая площадка</a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="index.php?page=news">Товары</a></li>
            <?php if (is_user_logged_in()): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=create_news">Добавить товар</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Корзина</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Выйти</a></li>
                <li class="nav-item"><a class="nav-link" href="user.php">Профиль</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="login.php">Войти</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php">Регистрация</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>