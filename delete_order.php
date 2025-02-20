<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем ID заказа из параметров URL
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Неверный ID заказа.");
}
$orderID = $_GET['order_id'];

// Удаляем заказ
$stmt = $conn->prepare("DELETE FROM orders WHERE OrderID = ? AND user_id = ?");
$stmt->bind_param("ii", $orderID, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo '<script>alert("Заказ успешно удален!"); window.location.href = "order.php";</script>';
    exit;
} else {
    echo '<script>alert("Ошибка при удалении заказа.");</script>';
}
$stmt->close();
?>