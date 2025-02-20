<?php
session_start();
require_once 'connect.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем данные пользователя из БД, включая аватарку
$stmt = $conn->prepare("SELECT FirstName, Phone, Email, Avatar FROM users WHERE UserID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($dbFirstName, $dbPhone, $dbEmail, $dbAvatar);
$stmt->fetch();
$stmt->close();

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Если форма отправлена для выхода
    if (isset($_POST['logout'])) {
        // Уничтожаем сессию и переадресуем на главную страницу
        session_destroy();
        header("Location: index.php");
        exit;
    }
    
    // Если форма отправлена для удаления профиля
    if (isset($_POST['delete_account'])) {
        // Удаляем пользователя из базы данных
        $stmt = $conn->prepare("DELETE FROM users WHERE UserID = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
    
        if ($stmt->execute()) {
            session_destroy();
            echo '<script>alert("Ваш профиль был удален.");</script>';
            header("Location: index.php");
            exit;
        } else {
            echo '<script>alert("Ошибка при удалении профиля.");</script>';
        }
        $stmt->close();
    }

    // Получаем и очищаем значения из формы
    $name  = trim($_POST['name']);
    $phone = trim($_POST['telefon']);
    $email = trim($_POST['email']);
    
    $errors = []; // массив для ошибок

    // Проверяем, чтобы поля не были пустыми
    if (empty($name)) {
        $errors['name'] = "Имя не может быть пустым.";
    }
    if (empty($phone)) {
        $errors['phone'] = "Телефон не может быть пустым.";
    }
    if (empty($email)) {
        $errors['email'] = "Электронная почта не может быть пустой.";
    }

    // Если выбран файл для загрузки аватарки
    $avatar_path = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $tmpName   = $_FILES['avatar']['tmp_name'];
        $fileName  = $_FILES['avatar']['name'];
        $fileSize  = $_FILES['avatar']['size'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedExts)) {
            $errors['avatar'] = "Недопустимый формат файла. Допустимые форматы: jpg, jpeg, png, gif.";
        } elseif ($fileSize > 5 * 1024 * 1024) { // 5 МБ
            $errors['avatar'] = "Файл слишком большой. Максимальный размер - 5 МБ.";
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newFileName = uniqid('avatar_', true) . '.' . $extension;
            $destination = $uploadDir . $newFileName;
            
            if (move_uploaded_file($tmpName, $destination)) {
                $avatar_path = $destination;
            } else {
                $errors['avatar'] = "Не удалось загрузить файл.";
            }
        }
    }
    
    // Если ошибок нет, выполняем обновление данных пользователя
    if (empty($errors)) {
        // Если аватар загружен успешно, обновляем и поле Avatar
        if ($avatar_path !== null) {
            $stmt = $conn->prepare("UPDATE users SET FirstName = ?, Phone = ?, Email = ?, Avatar = ? WHERE UserID = ?");
            $stmt->bind_param("ssssi", $name, $phone, $email, $avatar_path, $_SESSION['user_id']);
        } else {
            $stmt = $conn->prepare("UPDATE users SET FirstName = ?, Phone = ?, Email = ? WHERE UserID = ?");
            $stmt->bind_param("sssi", $name, $phone, $email, $_SESSION['user_id']);
        }
        
        if ($stmt->execute()) {
            echo '<script>alert("Изменения сохранены");</script>';
            // Обновляем локальные переменные, чтобы отобразить новые данные в форме
            $dbFirstName = $name;
            $dbPhone     = $phone;
            $dbEmail     = $email;
            if ($avatar_path !== null) {
                $dbAvatar = $avatar_path;
            }
        } else {
            echo '<script>alert("Ошибка при сохранении изменений");</script>';
        }
        $stmt->close();
    } else {
        // Если есть ошибки, выводим их (например, через alert)
        foreach ($errors as $error) {
            echo '<script>alert("' . htmlspecialchars($error) . '");</script>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link rel="stylesheet" href="./style/style.css">
    <link rel="stylesheet" href="./style/dashboard.css">
</head>
<body>
    <a href="./index.php">
        <div class="go_back">
            <---- На главную
        </div>
    </a>
    <h1>Профиль</h1>
    <div class="prof_block">
        <div class="buttons">
            <ul>
                <li><a href="#"><img src="./img/user.png" alt="user"> Мой профиль</a></li>
                <li><a href="#"><img src="./img/papka.png" alt="folder"> Личные данные</a></li>
                <li><a href="#"><img src="./img/mess.png" alt="fb"> Отзывы специалистов</a></li>
                <li><a href="#"><img src="./img/kolokol.png" alt="kol"> Уведомления по заказам</a></li>
                <li><a href="#"><img src="./img/settings.png" alt="set"> Действия с профилем</a></li>
            </ul>
        </div>
        <div class="profile">
            <h2 class="h2">Так специалисты видят ваш профиль</h2>
            <div class="name_profil blocks_prof">
                <div class="name_stroke">
                    <img src="<?php echo !empty($dbAvatar) ? htmlspecialchars($dbAvatar) : './img/default_avatar.png'; ?>" alt="Фото_профиля">
                    <p><?php echo htmlspecialchars($dbFirstName); ?></p>
                </div>
            </div>
            <div class="personal_information blocks_prof">
                <h2>Личные данные</h2>
                <!-- Форма для редактирования личных данных с загрузкой аватарки -->
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="avatar-container">
                        <label for="avatar">Фото профиля</label><br>
                        <input type="file" name="avatar" id="avatar" accept=".jpg, .jpeg, .png, .gif" onchange="document.querySelector('.form-actions').style.display = 'block';">
                    </div>
                    <div class="name_info">
                        <label for="name">Имя</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($dbFirstName); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                    </div>
                    <div class="tel_info">
                        <div class="inp_tel">
                            <img src="./img/rus.png" alt="Россия">
                            <input type="tel" name="telefon" id="telefon" value="<?php echo htmlspecialchars($dbPhone); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                        </div>
                        <p>Специалисты не видят ваш номер. Вы сами выбираете, кому он будет доступен.</p>
                    </div>
                    <div class="email_info">
                        <div class="email_block">
                            <label for="email">Электронная почта</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($dbEmail); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                        </div>
                        <div class="checkbox">
                            <input type="checkbox" name="mail" id="mail">
                            <p>Получать рассылку</p>
                        </div>
                        <p>Будем присылать акции и новости</p>
                    </div>
                    <div class="form-actions" style="display: none;">
                        <button type="button" class="save-btn" onclick="saveChanges()">Сохранить</button>
                        <button type="button" class="cancel-btn" onclick="cancelChanges()">Отменить</button>
                    </div>
                </form>
            </div>
            <div class="feedback blocks_prof">
                <h2>Отзывы от специалистов</h2>
                <p>Отзывов пока нет</p>
                <div class="more_block">
                    <p class="more">Специалисты могут оставлять отзывы о работе с вами.</p>
                    <a href="#">Подробнее</a>
                </div>
            </div>
            <div class="orders blocks_prof">
                <h2>Уведомления по заказам</h2>
                <form>
                    <div class="r_one rad">
                        <input type="radio" name="sms" id="email_sms" checked>
                        <p>На почту и смс</p>
                    </div>
                    <div class="r_two rad">
                        <input type="radio" name="sms" id="email_mess">
                        <p>На почту</p>
                    </div>
                    <div class="r_three rad">
                        <input type="radio" name="sms" id="sms">
                        <p>По смс</p>
                    </div>
                                      
                    <div class="form-actions" style="display: none;">
                        <button type="button" class="save-btn" onclick="saveChanges()">Сохранить</button>
                        <button type="button" class="cancel-btn" onclick="cancelChanges()">Отменить</button>
                    </div>
                </form>
            </div>
            <div class="delete blocks_prof">
                <h2>Действия с профилем</h2>

                <form method="POST" action="">
                    <button type="submit" name="logout" class="log_out btn_prof">Выйти</button>
                </form>
                <form method="POST" action="" onsubmit="return confirm('Вы уверены, что хотите удалить свой профиль? Это действие необратимо.');" class="">
                    <button type="submit" name="delete_account" class="btn_delete btn_prof">Удалить профиль</button>
                    <p>Вы потеряете историю заказов</p>
                </form>
            </div>
        </div>
    </div>
  
    <!-- Подключаем jQuery и Inputmask -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <script>
        // Применяем маску ввода для телефона
        Inputmask("+7 (999) 999-99-99").mask("#telefon");

        // При любом изменении в полях показываем кнопки "Сохранить"/"Отменить"
        document.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
                document.querySelector('.form-actions').style.display = 'block';
            }
        });

        // Функция отправки формы
        function saveChanges() {
            document.querySelector('form').submit();
        }

        // Функция отмены изменений (перезагрузка страницы)
        function cancelChanges() {
            location.reload();
        }
    </script>
</body>
</html>
