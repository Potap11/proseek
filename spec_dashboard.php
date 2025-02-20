<?php
session_start();
require_once 'connect.php'; 

// Проверяем, есть ли в сессии ID специалиста
if (!isset($_SESSION['specialist_id'])) {
    header("Location: register_specialist.php");
    exit;
}

// Получаем данные специалиста
$stmt = $conn->prepare("SELECT S.Surname, S.FirstName, S.Patronymic, S.Phone, S.Email, S.Specialty, S.Address, S.Photo 
                        FROM specialists S 
                        WHERE S.SpecialistID = ?");
$stmt->bind_param("i", $_SESSION['specialist_id']);
$stmt->execute();
$result = $stmt->get_result();
$specialist = $result->fetch_assoc();
$stmt->close();

if (!$specialist) {
    echo "Ошибка: специалист не найден.";
    exit;
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: market_orders.html");
        exit;
    }

    if (isset($_POST['delete_account'])) {
        $stmt = $conn->prepare("DELETE FROM specialists WHERE SpecialistID = ?");
        $stmt->bind_param("i", $_SESSION['specialist_id']);

        if ($stmt->execute()) {
            session_destroy();
            header("Location: market_orders.php");
            exit;
        } else {
            echo '<script>alert("Ошибка при удалении профиля.");</script>';
        }
        $stmt->close();
    }

    // Получаем новые данные из формы
    $surname  = trim($_POST['surname']);
    $firstName = trim($_POST['firstName']);
    $patronymic = trim($_POST['patronymic']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $specialty = trim($_POST['specialty']);
    $address = trim($_POST['address']);

    $errors = [];

    if (empty($surname)) $errors['surname'] = "Фамилия не может быть пустой.";
    if (empty($firstName)) $errors['firstName'] = "Имя не может быть пустым.";
    if (empty($phone)) $errors['phone'] = "Телефон не может быть пустым.";
    if (empty($email)) $errors['email'] = "Электронная почта не может быть пустой.";
    if (empty($specialty)) $errors['specialty'] = "Специализация не может быть пустой.";

    // Обработка загрузки фото
    $avatar_path = $specialist['Photo'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extension, $allowedExts)) {
            $errors['avatar'] = "Недопустимый формат файла.";
        } elseif ($fileSize > 5 * 1024 * 1024) {
            $errors['avatar'] = "Файл слишком большой (максимум 5 МБ).";
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $newFileName = uniqid('avatar_', true) . '.' . $extension;
            $destination = $uploadDir . $newFileName;
            if (move_uploaded_file($tmpName, $destination)) {
                $avatar_path = $destination;
            } else {
                $errors['avatar'] = "Ошибка загрузки файла.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE specialists SET Surname=?, FirstName=?, Patronymic=?, Phone=?, Email=?, Specialty=?, Address=?, Photo=? WHERE SpecialistID=?");
        $stmt->bind_param("ssssssssi", $surname, $firstName, $patronymic, $phone, $email, $specialty, $address, $avatar_path, $_SESSION['specialist_id']);

        if ($stmt->execute()) {
            echo '<script>alert("Изменения сохранены");</script>';
            $specialist = compact('surname', 'firstName', 'patronymic', 'phone', 'email', 'specialty', 'address');
            $specialist['Photo'] = $avatar_path;
        } else {
            echo '<script>alert("Ошибка при сохранении изменений.");</script>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль специалиста</title>
    <link rel="stylesheet" href="./style/style.css">
    <link rel="stylesheet" href="./style/dashboard.css">
</head>
<body>
<a href="./market_orders.php">
        <div class="go_back">
            <---- На главную
        </div>
    </a>
    <h1>Профиль специалиста</h1>
    <div class="prof_block">
        <div class="buttons">
            <ul>
                <li><a href="#"><img src="./img/user.png" alt="user"> Мой профиль</a></li>
                <li><a href="#"><img src="./img/papka.png" alt="folder"> Личные данные</a></li>
                <li><a href="#"><img src="./img/mess.png" alt="fb"> Отзывы специалистов</a></li>
                <li><a href="#"><img src="./img/settings.png" alt="set"> Действия с профилем</a></li>
            </ul>
        </div>
        <div class="profile">
            <div class="name_profil blocks_prof">
                <div class="name_stroke">
                    <img src="<?php echo !empty($specialist['Photo']) ? htmlspecialchars($specialist['Photo']) : './img/default_avatar.png'; ?>" alt="Фото_профиля">
                    <p><?php echo htmlspecialchars($specialist['FirstName']); ?></p>
                </div>
            </div>
            <div class="personal_information blocks_prof">
                <h2>Личные данные</h2>
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="avatar-container">
                        <label for="avatar">Фото профиля</label><br>
                        <input type="file" name="avatar" id="avatar" accept=".jpg, .jpeg, .png, .gif" onchange="document.querySelector('.form-actions').style.display = 'block';">
                    </div>
                    <div class="name_info">
                        <label for="surname">Фамилия</label>
                        <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($specialist['Surname']); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                    </div>
                    <div class="name_info">
                        <label for="firstName">Имя</label>
                        <input type="text" name="firstName" id="firstName" value="<?php echo htmlspecialchars($specialist['FirstName']); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                    </div>
                    <div class="name_info">
                        <label for="patronymic">Отчество</label>
                        <input type="text" name="patronymic" id="patronymic" value="<?php echo htmlspecialchars($specialist['Patronymic']); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                    </div>
                    <div class="tel_info">
                        <div class="inp_tel">
                            <img src="./img/rus.png" alt="Россия">
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($specialist['Phone']); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                        </div>
                    </div>
                    <div class="email_info">
                        <div class="email_block">
                            <label for="email">Электронная почта</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($specialist['Email']); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                        </div>
                    </div>
                    <div class="specialty_info">
                        <label for="specialty">Специализация</label>
                        <input type="text" name="specialty" id="specialty" value="<?php echo htmlspecialchars($specialist['Specialty']); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                    </div>
                    <div class="address_info">
                        <label for="address">Адрес</label>
                        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($specialist['Address']); ?>" oninput="document.querySelector('.form-actions').style.display = 'block';">
                    </div>
                    <div class="form-actions" style="display: none;">
                        <button type="submit" class="save_changes">Сохранить изменения</button>
                    </div>
                </form>
            </div>
            <div class="actions_profile blocks_prof">
                <form method="POST">
                    <button type="submit" name="logout" class="logout">Выйти</button>
                </form>
                <form method="POST" onsubmit="return confirm('Вы уверены? Это действие нельзя отменить.');">
                    <button type="submit" name="delete_account" class="delete_account">Удалить профиль</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
