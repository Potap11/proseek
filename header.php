<?php
session_start(); // Начинаем сессию

// Проверяем, есть ли в сессии информация о пользователе
$userName = $_SESSION['user_name'] ?? null;
$userAvatar = $_SESSION['user_avatar'] ?? './img/photo.png'; // Фото по умолчанию, если не задано
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="./style/header.css">
    <link rel="stylesheet" href="./style/index.css">
</head>
<style>
    .btn_prof{
    display: flex;
    align-items: center;
    justify-content: center;
    background-color:#def1fc ;
    color: black;
    font-size: 18px;
    height: 20px;
    gap: 10px;
}
    nav li:hover .block_btn_head {
            display: block;
            display: flex;
            align-items: center;
            flex-direction: column;
            gap: 5px;
}
    .block_btn_head{
        top: 40px;
    }
</style>
<body>
    <header>
        <div class="logo-list">
           <a href="./index.php"> <img src="./img/Logo.png" alt="Logo"></a>
            <select name="city" id="city">
                <option value="almet">Альметьевск</option>
                <option value="aznak">Азнакаево</option>
                <option value="djal">Джалиль</option>
                <option value="zainsk">Заинск</option>          
                <option value="Nizh">Нижнекамск</option>
            </select>
        </div>
        <nav>
            <ul>
                <!-- Если пользователь не авторизован, показываем кнопки "Войти" и "Мои заказы" -->
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Войти клиенту</a></li>
                    <li><a href="login_spec.php">Войти специалисту</a></li>
                <?php else: ?>
                    <li><a href="login_spec.php">Войти специалисту</a></li>
                    <!-- Если пользователь авторизован, показываем имя и фото -->
                    <li>
                        <button class="btn_prof">
                            <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Фото профиля" style="width: 30px; height: 30px; border-radius: 50%;">
                            <?php echo htmlspecialchars($userName); ?>
                        </button>
                        <!-- Дополнительные ссылки -->
                        <div class="block_btn_head">
                            <a href="user_dashboard.php">Профиль и настройки</a>
                            <a href="order.php">Мои заказы</a>
                            <a href="logout.php">Выйти</a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
</body>
</html>
