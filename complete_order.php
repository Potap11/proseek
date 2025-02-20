<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем ID заказа из GET-запроса
$orderID = $_GET['order_id'] ?? null;

if ($orderID) {
    // Проверяем, что заказ существует и его статус "в процессе"
    $stmt = $conn->prepare("SELECT Status, user_id FROM orders WHERE OrderID = ?");
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $stmt->bind_result($status, $userId);
    $stmt->fetch();
    $stmt->close();

    // Если заказ существует и у пользователя есть доступ к заказу
    if ($status === 'в процессе' && $userId == $_SESSION['user_id']) {
        // Обновляем статус заказа на "выполнено"
        $stmt = $conn->prepare("UPDATE orders SET Status = 'выполнено' WHERE OrderID = ?");
        $stmt->bind_param("i", $orderID);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Заказ успешно завершен!';
        } else {
            $_SESSION['error_message'] = 'Произошла ошибка при завершении заказа.';
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = 'Невозможно завершить заказ. Статус заказа не "в процессе" или вы не авторизованы для этого заказа.';
    }
} else {
    $_SESSION['error_message'] = 'Некорректный запрос.';
}

// Перенаправляем обратно на страницу с заказами
header("Location: order.php");  // Убедитесь, что путь верный и страница существует
exit;
?>
