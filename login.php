<?php
session_start();

// Подключаем файл connect.php для работы с базой данных
require_once 'connect.php';

// Обработка формы авторизации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы и очищаем их
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Сначала проверяем, есть ли пользователь среди обычных пользователей
    $stmt = $conn->prepare("SELECT UserID, FirstName, Avatar, Password FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Пользователь найден
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            // Успешная авторизация как обычный пользователь

            // Записываем в сессию ID пользователя, имя и аватар
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['user_name'] = $row['FirstName'];
            $_SESSION['user_avatar'] = $row['Avatar'] ?: './img/photo.png'; // Если аватар не задан, используем по умолчанию

            $_SESSION['role'] = 'user';

            // Перенаправляем на страницу пользователя
            header("Location: index.php");
            exit();
        } else {
            echo "Неверный пароль для пользователя!";
        }
    } 

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/login.css">
    <title>Авторизация</title>
</head>
<body>
    <div class="form-container">
        <h2>Авторизация</h2>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        <a href="register.php">Зарегистрироваться</a>
    </div>
</body>
</html>
