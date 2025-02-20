<?php
session_start();

// Проверка, что администратор авторизован
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
</head>
<body>
    <h2>Добро пожаловать в админ-панель</h2>
    <nav>
        <ul>
            <li><a href="manage_users.php">Управление пользователями</a></li>
            <li><a href="manage_specialists.php">Управление специалистами</a></li>
            <li><a href="manage_orders.php">Управление заказами</a></li>
            <li><a href="logout.php">Выйти</a></li>
        </ul>
    </nav>
</body>
</html>
