<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли специалист
if (!isset($_SESSION['specialist_id'])) {
    header("Location: login_spec.php");
    exit;
}

// Получаем ID заказа из параметра URL
$orderID = $_GET['order_id'] ?? null;
if ($orderID === null) {
    die("Ошибка: ID заказа не указан.");
}

// Получаем ID специалиста из сессии
$specialistID = $_SESSION['specialist_id'];

// Проверяем, не откликался ли специалист уже на этот заказ
$stmt = $conn->prepare("SELECT * FROM order_responses WHERE OrderID = ? AND SpecialistID = ?");
$stmt->bind_param("ii", $orderID, $specialistID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Вы уже откликнулись на этот заказ.");
}
$stmt->close();

// Добавляем отклик специалиста на заказ
$stmt = $conn->prepare("INSERT INTO order_responses (OrderID, SpecialistID) VALUES (?, ?)");
$stmt->bind_param("ii", $orderID, $specialistID);

if ($stmt->execute()) {
    echo "Вы успешно откликнулись на заказ! Ожидайте, пока клиент выберет специалиста.";
} else {
    echo "Ошибка при отклике: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
