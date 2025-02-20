<?php
session_start();
require_once 'connect.php'; // Подключаем базу данных

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $service = trim($_POST['service']);
    $clientName = $_SESSION['user_name'];  // Имя клиента, получаем из сессии
    $contactInfo = $_SESSION['user_phone'];  // Телефон клиента, получаем из сессии
    $problemDescription = trim($_POST['problem-description']);
    $executionType = $_POST['execution-type'];
    $address = isset($_POST['address']) ? trim($_POST['address']) : 'Не требуется выезд';
    
    // Вставляем данные в таблицу заказов
    $stmt = $conn->prepare("INSERT INTO orders (Service, ClientName, ContactInfo, Address, ProblemDescription, ExecutionType) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $service, $clientName, $contactInfo, $address, $problemDescription, $executionType);

    if ($stmt->execute()) {
        echo '<script>alert("Заказ успешно создан!"); window.location.href = "order.php";</script>';
    } else {
        echo '<script>alert("Ошибка при создании заказа.");</script>';
    }

    $stmt->close();
}
?>
