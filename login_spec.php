<?php
session_start();

require_once 'connect.php';

// Обработка формы авторизации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы и очищаем их
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Отладка: вывод введенных данных
    echo "Email: " . $email . "<br>";
    echo "Password: " . $password . "<br>";

    // Проверяем, есть ли специалист с таким email, включая поле CategoryID
    $stmt = $conn->prepare("SELECT SpecialistID, FirstName, Password, CategoryID FROM specialists WHERE Email = ?");
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Специалист найден
        $row = $result->fetch_assoc();
        echo "Найден специалист: " . print_r($row, true) . "<br>";

        // Проверяем пароль
        if (password_verify($password, $row['Password'])) {
            echo "Пароль верный.<br>";

            // Успешная авторизация как специалист
            $_SESSION['specialist_id'] = $row['SpecialistID'];
            $_SESSION['specialist_name'] = $row['FirstName'];
            $_SESSION['specialist_categoryID'] = $row['CategoryID']; // Сохраняем CategoryID
            $_SESSION['role'] = 'specialist';

            // Отладка: вывод данных сессии
            echo "ID специалиста: " . $_SESSION['specialist_id'] . "<br>";
            echo "Имя специалиста: " . $_SESSION['specialist_name'] . "<br>";
            echo "Категория специалиста: " . $_SESSION['specialist_categoryID'] . "<br>";
            echo "Роль: " . $_SESSION['role'] . "<br>";

            // Перенаправляем на страницу специалиста
            header("Location: market_orders.php");
            exit();
        } else {
            // Неверный пароль
            $error = "Неверный пароль!";
        }
    } else {
        // Специалист с таким email не найден
        $error = "Пользователь с таким email не найден!";
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
    <title>Авторизация специалиста</title>
</head>
<body>
    <div class="form-container">
        <h2>Авторизация </h2>

        <!-- Вывод сообщений об ошибках -->
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Форма авторизации -->
        <form action="login_spec.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>

        <!-- Кнопка регистрации -->
        <a href="register_specialist.php">Зарегистрироваться</a>
    </div>
</body>
</html>
