<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли пользователь (клиент)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$action = $_GET['action'] ?? null;
$orderID = $_GET['order_id'] ?? null;
$specialistID = $_GET['specialist_id'] ?? null;

if (!$action || !$orderID || !$specialistID) {
    die("Ошибка: не указаны все необходимые параметры.");
}

if ($action == 'choose') {
    // Клиент выбирает специалиста для заказа:
    // Обновляем заказ: устанавливаем SpecialistID и меняем статус на "в процессе"
    $stmt = $conn->prepare("UPDATE orders SET SpecialistID = ?, Status = 'в процессе' WHERE OrderID = ?");
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->bind_param("ii", $specialistID, $orderID);
    if ($stmt->execute()) {
        $stmt->close();
        // Обновляем отклики: для выбранного специалиста устанавливаем "accepted"
        $stmt = $conn->prepare("UPDATE order_responses SET ResponseStatus = 'accepted' WHERE OrderID = ? AND SpecialistID = ?");
        $stmt->bind_param("ii", $orderID, $specialistID);
        $stmt->execute();
        $stmt->close();
        // Обновляем остальные отклики этого заказа как "rejected"
        $stmt = $conn->prepare("UPDATE order_responses SET ResponseStatus = 'rejected' WHERE OrderID = ? AND SpecialistID <> ?");
        $stmt->bind_param("ii", $orderID, $specialistID);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_message'] = "Вы выбрали специалиста для заказа. Заказ переведен в статус 'в процессе'.";
        header("Location: order_details.php?order_id=" . $orderID);
        exit;
    } else {
        die("Ошибка при обновлении заказа: " . $stmt->error);
    }
} elseif ($action == 'reject') {
    // Клиент отклоняет отклик конкретного специалиста
    $stmt = $conn->prepare("UPDATE order_responses SET ResponseStatus = 'rejected' WHERE OrderID = ? AND SpecialistID = ?");
    $stmt->bind_param("ii", $orderID, $specialistID);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Отклик специалиста отклонен.";
        header("Location: order_details.php?order_id=" . $orderID);
        exit;
    } else {
        die("Ошибка при обновлении отклика: " . $stmt->error);
    }
} else {
    die("Неизвестное действие.");
}
?>
