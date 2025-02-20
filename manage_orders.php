<?php
session_start();
require_once 'connect.php';

// Проверка, что администратор авторизован
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// Извлекаем все заказы
$stmt = $conn->prepare("SELECT o.OrderID, o.Service, o.ClientName, o.Status, o.Price, o.CreatedAt, u.FirstName AS UserFirstName FROM orders o LEFT JOIN users u ON o.user_id = u.UserID");
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
    <title>Управление заказами</title>
</head>
<body>
    <h2>Управление заказами</h2>
    <table>
        <tr>
            <th>Сервис</th>
            <th>Имя клиента</th>
            <th>Статус</th>
            <th>Цена</th>
            <th>Дата создания</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?php echo htmlspecialchars($order['Service']); ?></td>
            <td><?php echo htmlspecialchars($order['UserFirstName']); ?></td>
            <td><?php echo htmlspecialchars($order['Status']); ?></td>
            <td><?php echo htmlspecialchars($order['Price']); ?> руб.</td>
            <td><?php echo htmlspecialchars($order['CreatedAt']); ?></td>
            <td>
                <a href="edit_order.php?order_id=<?php echo htmlspecialchars($order['OrderID']); ?>">Редактировать</a> | 
                <a href="delete_order.php?order_id=<?php echo htmlspecialchars($order['OrderID']); ?>" onclick="return confirm('Вы уверены, что хотите удалить этот заказ?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
