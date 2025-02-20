<?php
session_start();

// Проверяем, авторизован ли специалист
if (!isset($_SESSION['specialist_id'])) {
    header("Location: login_spec.php");
    exit;
}

// Получаем ID заказа из параметров URL
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Неверный ID заказа.");
}
$orderID = $_GET['order_id'];

// Здесь можно добавить логику для обработки отклика на заказ
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отклик на заказ</title>
    <link rel="stylesheet" href="./style/style.css">
</head>
<body>
    <?php include('header_specialist.php'); ?>
    <h1>Отклик на заказ</h1>
    <p>Вы откликаетесь на заказ #<?php echo htmlspecialchars($orderID); ?>.</p>
    <!-- Форма для отправки отклика -->
    <form action="process_response.php" method="POST">
        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($orderID); ?>">
        <label for="message">Ваше сообщение:</label>
        <textarea id="message" name="message" rows="5" required></textarea>
        <button type="submit">Отправить отклик</button>
    </form>
</body>
</html>