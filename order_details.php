<?php
session_start();
require_once 'connect.php';

$orderID = $_GET['order_id'] ?? null;
if ($orderID === null) {
    die("Ошибка: ID заказа не указан.");
}

// Получаем информацию о заказе
$stmt = $conn->prepare("SELECT o.*, c.CategoryName 
                        FROM orders o 
                        LEFT JOIN specialist_categories c ON o.CategoryID = c.CategoryID 
                        WHERE o.OrderID = ?");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}
$stmt->bind_param("i", $orderID);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Заказ не найден.");
}

// Получаем отклики специалистов на этот заказ
$stmt = $conn->prepare("SELECT r.ResponseID, r.SpecialistID, r.ResponseDate,
                               CONCAT(s.Surname, ' ', s.FirstName) AS SpecialistName, 
                               s.Photo AS SpecialistAvatar
                        FROM order_responses r
                        JOIN specialists s ON r.SpecialistID = s.SpecialistID
                        WHERE r.OrderID = ?
                        ORDER BY r.ResponseDate ASC");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}
$stmt->bind_param("i", $orderID);
$stmt->execute();
$responsesResult = $stmt->get_result();

$responses = [];
while ($row = $responsesResult->fetch_assoc()) {
    $responses[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали заказа</title>
    <link rel="stylesheet" href="./style/style.css">
    <style>
        .order-details {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .order-details h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .order-details p {
            margin: 10px 0;
            font-size: 16px;
        }
        .responses {
            margin-top: 30px;
        }
        .response-card {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .response-card img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .btn-choose {
            background-color: #28a745;
            color: #fff;
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 4px;
            margin-left: auto;
        }
        .btn-choose:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="order-details">
        <h2>Информация о заказе</h2>
        <p><strong>Сервис:</strong> <?php echo htmlspecialchars($order['Service']); ?></p>
        <p><strong>Описание:</strong> <?php echo htmlspecialchars($order['ProblemDescription']); ?></p>
        <p><strong>Цена:</strong> <?php echo htmlspecialchars($order['Price']); ?> руб.</p>
        <p><strong>Категория:</strong> <?php echo htmlspecialchars($order['CategoryName']); ?></p>
        <p><strong>Дата создания:</strong> <?php echo htmlspecialchars($order['CreatedAt']); ?></p>
        <p><strong>Статус заказа:</strong> <?php echo htmlspecialchars($order['Status']); ?></p>

        <h3>Отклики специалистов:</h3>
        <div class="responses">
            <?php if (count($responses) > 0): ?>
                <?php foreach ($responses as $response): ?>
                    <div class="response-card">
                        <img src="<?php echo htmlspecialchars($response['SpecialistAvatar'] ?: './img/default_avatar.png'); ?>" alt="Фото специалиста">
                        <div>
                            <p><strong><?php echo htmlspecialchars($response['SpecialistName']); ?></strong></p>
                            <p>Отклик: <?php echo htmlspecialchars($response['ResponseDate']); ?></p>
                        </div>
                        <!-- Если заказ имеет статус "ожидает", показываем кнопку "Выбрать" -->
                        <?php if ($order['Status'] === 'ожидает'): ?>
                            <a href="choose_specialist.php?order_id=<?php echo $orderID; ?>&specialist_id=<?php echo $response['SpecialistID']; ?>" class="btn-choose">Выбрать</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Откликов пока нет.</p>
            <?php endif; ?>
        </div>
        <a href="order.php" class="btn-choose" style="background-color: #007bff; margin-top: 20px; display: inline-block;">Вернуться к заказам</a>
    </div>
</body>
</html>
