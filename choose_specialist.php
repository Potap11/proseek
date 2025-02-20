<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли пользователь (клиент)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$orderID = $_GET['order_id'] ?? null;
$specialistID = $_GET['specialist_id'] ?? null;

if ($orderID === null || $specialistID === null) {
    die("Ошибка: не указаны все необходимые параметры.");
}

// Обновляем заказ: устанавливаем SpecialistID и меняем статус на "в процессе"
$stmt = $conn->prepare("UPDATE orders SET SpecialistID = ?, Status = 'в процессе' WHERE OrderID = ?");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}
$stmt->bind_param("ii", $specialistID, $orderID);

if ($stmt->execute()) {
    $stmt->close();
    $_SESSION['success_message'] = "Вы выбрали специалиста для заказа. Заказ переведен в статус 'в процессе'.";
    header("Location: order.php");
    exit;
} else {
    die("Ошибка при выборе специалиста: " . $stmt->error);
}
$stmt->close();
$conn->close();
?>
