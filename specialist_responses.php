<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли специалист
if (!isset($_SESSION['specialist_id'])) {
    header("Location: login_spec.php");
    exit;
}

$specialist_id = $_SESSION['specialist_id'];

// Извлекаем заказы, принятые данным специалистом (например, со статусом "в процессе")
$stmt = $conn->prepare("SELECT OrderID, Service, ProblemDescription, Price, CreatedAt, Status 
                        FROM orders 
                        WHERE SpecialistID = ? 
                        ORDER BY CreatedAt DESC");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}
$stmt->bind_param("i", $specialist_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои отклики</title>
    <link rel="stylesheet" href="./style/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Мои отклики (принятые заказы)</h1>
        <?php if (count($orders) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>OrderID</th>
                        <th>Услуга</th>
                        <th>Описание</th>
                        <th>Цена</th>
                        <th>Дата создания</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($order['Service']); ?></td>
                            <td><?php echo htmlspecialchars($order['ProblemDescription']); ?></td>
                            <td><?php echo htmlspecialchars($order['Price']); ?> руб.</td>
                            <td><?php echo htmlspecialchars($order['CreatedAt']); ?></td>
                            <td><?php echo htmlspecialchars($order['Status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>У вас еще нет откликов на заказы.</p>
        <?php endif; ?>
    </div>
</body>
</html>
