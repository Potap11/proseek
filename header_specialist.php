<?php
session_start(); // Начинаем сессию

// Проверяем, есть ли в сессии информация о специалисте
$specialistName = $_SESSION['specialist_name'] ?? null;
$specialistAvatar = $_SESSION['specialist_avatar'] ?? './img/photo.png'; // Фото по умолчанию, если не задано
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница специалиста</title>
    <link rel="stylesheet" href="./style/header.css">
    <link rel="stylesheet" href="./style/index.css">
</head>
<style>
    /* Стили для кнопки профиля */
    .btn_prof {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #def1fc;
        color: black;
        font-size: 18px;
        height: 20px;
        gap: 10px;
        cursor: pointer;
        border: none;
        padding: 10px;
        border-radius: 5px;
    }

    /* Стили для выпадающего меню */
    .block_btn_head {
        display: none;
        position: absolute;
        top: 50px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 10px;
        z-index: 1000;
    }

    .block_btn_head a {
        display: block;
        padding: 8px 16px;
        color: #333;
        text-decoration: none;
        white-space: nowrap;
    }

    .block_btn_head a:hover {
        background-color: #f1f1f1;
    }

    /* Показываем меню при наведении */
    nav li:hover .block_btn_head {
        display: block;
    }
</style>
<body>
    <header>
        <div class="logo-list">
            <a href=".market_orders.php"><img src="./img/Logo.png" alt="Logo"></a>
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
                <!-- Если специалист не авторизован, показываем кнопку "Войти" -->
                <?php if (!isset($_SESSION['specialist_id'])): ?>
                    <li><a href="login_spec.php">Войти специалисту</a></li>
                <?php else: ?>
                    <!-- Если специалист авторизован, показываем имя и фото -->
                    <li>
                        <button class="btn_prof">
                            <img src="<?php echo htmlspecialchars($specialistAvatar); ?>" alt="Фото профиля" style="width: 30px; height: 30px; border-radius: 50%;">
                            <?php echo htmlspecialchars($specialistName); ?>
                        </button>
                        <!-- Выпадающее меню -->
                        <div class="block_btn_head">
                            <a href="spec_dashboard.php">Профиль</a>
                            <a href="specialist_responses.php">Мои отклики</a>
                            <a href="logout.php">Выйти</a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
</body>
</html>