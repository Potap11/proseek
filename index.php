<?php 
session_start(); // Начинаем сессию


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProSeek</title>
    <link rel="stylesheet" href="./style/index.css">
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>
    <?php

    // Проверяем, авторизован ли пользователь
    if (isset($_SESSION['user_id'])) {
        // Если пользователь авторизован, подключаем header.php
        include('header.php');
    } else {
        // Если пользователь не авторизован, показываем обычную шапку
    ?>
    <header>
        <div class="logo-list">
        <a href="./index.php"><img src="./img/Logo.png" alt="Logo"></a>
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
                <li><a href="./login_spec.php">Войти специалисту</a></li>
                <li><a href="./login.php">Войти клиенту</a></li>
            </ul>
        </nav>
    </header>
    <?php
    }
    ?>
    <div class="search-cards">
        <h2>Для любой проблемы есть эксперт</h2>
        <div class="search">
            <input type="text" name="search" id="search" placeholder="Услуга или специалист">
            <button>Поиск</button>
        </div>
        <div class="cards">
            <div class="santex card">
                <p>Сантехник</p>
                <img src="./img/santeh.png" alt="Сантехник">
            </div>
            <div class="yborka card">
                <p>Уборка</p>
                <img src="./img/yborka.png" alt="Уборка">
            </div>
            <div class="plitka card">
                <p>Плиточник</p>
                <img src="./img/plitka.png" alt="Плиточник">
            </div>
            <div class="ychitel card">
                <p>Репетитор</p>
                <img src="./img/ychitel.png" alt="Репетитор">
            </div>
        </div>
    </div>
    <div class="more-spec">
        <h3>Больше Специалистов</h3>
        <div class="one-line line">
            <div class="repetitor block_spec">
                <a href="#">
                    <h4>Репетиторы</h4>
                </a>
                <a href="#">Математика</a>
                <a href="#">Русский язык</a>
                <a href="#">Информатика</a>
                <a href="#">Английский язык</a>
                <b><a href="#">Все предметы</a></b>
            </div>
            <div class="master block_spec">
                <a href="#">
                    <h4>Мастера по ремонту</h4>
                </a>
                <a href="#">Сантехник</a>
                <a href="#">Электрик</a>
                <a href="#">Плиточник</a>
                <a href="#">Ремонт под ключ</a>
                <b><a href="#">Все услуги</a></b>
            </div>
            <div class="krasota block_spec">
                <a href="#">
                    <h4>Мастера красоты</h4>
                </a>
                <a href="#">Макияж</a>
                <a href="#">Маникюр</a>
                <a href="#">Прически</a>
                <a href="#">Эпиляция</a>
                <b><a href="#">Все услуги</a></b>
            </div>
            <div class="frilans block_spec">
                <a href="#">
                    <h4>Фрилансеры</h4>
                </a>
                <a href="#">Дизайнер</a>
                <a href="#">Копирайтер</a>
                <a href="#">Маркетинг</a>
                <a href="#">Системные администраторы</a>
                <b><a href="#">Все услуги</a></b>
            </div>
        </div>
        <div class="two-line line">
            <div class="repetitor block_spec">
                <a href="#">
                    <h4>Репетиторы</h4>
                </a>
                <a href="#">Математика</a>
                <a href="#">Русский язык</a>
                <a href="#">Информатика</a>
                <a href="#">Английский язык</a>
                <b><a href="#">Все предметы</a></b>
            </div>
            <div class="repetitor block_spec">
                <a href="#">
                    <h4>Репетиторы</h4>
                </a>
                <a href="#">Математика</a>
                <a href="#">Русский язык</a>
                <a href="#">Информатика</a>
                <a href="#">Английский язык</a>
                <b><a href="#">Все предметы</a></b>
            </div>
            <div class="repetitor block_spec">
                <a href="#">
                    <h4>Репетиторы</h4>
                </a>
                <a href="#">Математика</a>
                <a href="#">Русский язык</a>
                <a href="#">Информатика</a>
                <a href="#">Английский язык</a>
                <b><a href="#">Все предметы</a></b>
            </div>
            <div class="repetitor block_spec">
                <a href="#">
                    <h4>Репетиторы</h4>
                </a>
                <a href="#">Математика</a>
                <a href="#">Русский язык</a>
                <a href="#">Информатика</a>
                <a href="#">Английский язык</a>
                <b><a href="#">Все предметы</a></b>
            </div>
        </div>
        <div class="tree-line line">
            <div class="repetitor block_spec">
                <a href="#">
                    <h4>Репетиторы</h4>
                </a>
                <a href="#">Математика</a>
                <a href="#">Русский язык</a>
                <a href="#">Информатика</a>
                <a href="#">Английский язык</a>
                <b><a href="#">Все предметы</a></b>
            </div>
            <div class="repetitor block_spec">
                <a href="#">
                    <h4>Репетиторы</h4>
                </a>
                <a href="#">Математика</a>
                <a href="#">Русский язык</a>
                <a href="#">Информатика</a>
                <a href="#">Английский язык</a>
                <b><a href="#">Все предметы</a></b>
            </div>
            <div class="repetitor block_spec">
                <a href="#">
                    <h4>Репетиторы</h4>
                </a>
                <a href="#">Математика</a>
                <a href="#">Русский язык</a>
                <a href="#">Информатика</a>
                <a href="#">Английский язык</a>
                <b><a href="#">Все предметы</a></b>
            </div>
        </div>
    </div>
    <div class="animation">
        <h2>Как это работает</h2>
        <div class="anim_block location">
        </div>
        <div class="anim_block orders">
        </div>
        <div class="anim_block specialist">
        </div>
    </div>
</body>

</html>

