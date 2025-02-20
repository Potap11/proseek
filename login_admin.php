<?php
session_start();
require_once 'connect.php';


// Проверяем, авторизован ли администратор
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php"); // Перенаправляем на главную панель администратора
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminEmail = $_POST['email'] ?? '';
    $adminPassword = $_POST['password'] ?? '';

    // Проверяем введенные данные
    $stmt = $conn->prepare("SELECT AdminID, Email, Password FROM admins WHERE Email = ?");
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $stmt->bind_result($adminID, $storedEmail, $storedPassword);
    $stmt->fetch();
    $stmt->close();

    if ($storedEmail && password_verify($adminPassword, $storedPassword)) {
        $_SESSION['admin_id'] = $adminID;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Неверный логин или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
</head>
<body>
    <form method="POST" action="">
        <label for="email">Электронная почта:</label>
        <input type="email" name="email" required>
        <label for="password">Пароль:</label>
        <input type="password" name="password" required>
        <button type="submit">Войти</button>
    </form>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
</body>
</html>
