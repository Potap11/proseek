<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем контактную информацию пользователя из сессии или из базы, если её там нет
$userContact = $_SESSION['user_phone'] ?? '';

if (empty($userContact)) {
    // Если контакт не сохранён в сессии, извлекаем его из таблицы users
    $stmt = $conn->prepare("SELECT Phone FROM users WHERE UserID = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($userContact);
    $stmt->fetch();
    $stmt->close();
}

// Извлекаем заказы, созданные данным пользователем, фильтруя по user_id
$stmt = $conn->prepare("SELECT o.OrderID, o.Service, o.ClientName, o.Address, o.DateTime, o.ProblemDescription, o.CreatedAt, o.Status, o.Price
                        FROM orders o
                        WHERE o.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
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
    <title>Мои заказы</title>
    <link rel="stylesheet" href="./style/orders.css">
    <link rel="stylesheet" href="./style/style.css">
</head>
<style>
    .orders {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }
    .order {
        background-color: #ffffff;
        border-radius: 15px;
        margin: 15px;
        width: 700px;
        padding: 8px;
    }
    .order_block {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
    }
    .order_actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    .btn_create_order, .btn_edit, .btn_complete, .btn_delete, .btn_choose {
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 8px 12px;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .btn_create_order:hover, .btn_edit:hover, .btn_complete:hover, .btn_delete:hover, .btn_choose:hover {
        background-color: #0056b3;
    }
    .no_order {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
</style>
<body>
<?php include('header.php'); ?>

<!-- Выводим сообщения об успехе или ошибке -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="message success">
        <?php echo $_SESSION['success_message']; ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="message error">
        <?php echo $_SESSION['error_message']; ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<h2>Мои заказы</h2>
<a href="./create_order.php" class="btna">
    <button class="btn_create_order">
        <h3>Создать заказ</h3>
        <img src="./img/plus.png" alt="плюс">
    </button>
</a>
<div class="orders">
<?php if (count($orders) > 0): ?>
    <?php foreach ($orders as $order): ?>
        <div class="order">
            <div class="order_block">
                <div class="item_ob">
                    <p style="font-size: 20px; font-weight: bold;"><?php echo htmlspecialchars($order['Service']); ?></p>
                    <p>
                        <?php 
                        $status = htmlspecialchars($order['Status']);
                        // Здесь можно добавить логику для выбора цвета статуса
                        echo "<span style='font-weight: bold;'>Статус: $status</span>";
                        ?>
                    </p>
                </div>
                <div class="item_obt">
                    <p><strong>Цена:</strong> <?php echo htmlspecialchars($order['Price']) ?: 'Не указана'; ?> руб.</p>
                    <p><?php echo htmlspecialchars($order['CreatedAt']); ?></p>
                </div>
            </div>
            <p><strong>Описание заказа:</strong> <?php echo htmlspecialchars($order['ProblemDescription']); ?></p>
            <?php if (!empty($order['Address'])): ?>
                <p><strong>Адрес исполнения:</strong> <?php echo htmlspecialchars($order['Address']); ?></p>
            <?php endif; ?>
            
            <!-- Кнопки управления -->
            <div class="order_actions">
                <?php if ($order['Status'] === 'ожидает'): ?>
                    <a href="edit_order.php?order_id=<?php echo htmlspecialchars($order['OrderID']); ?>" class="btn_edit">Редактировать</a>
                    <!-- Кнопка для просмотра откликов и выбора специалиста -->
                    <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['OrderID']); ?>" class="btn_choose">Выбрать специалиста</a>
                <?php elseif ($order['Status'] === 'в процессе'): ?>
                    <a href="complete_order.php?order_id=<?php echo htmlspecialchars($order['OrderID']); ?>" class="btn_complete">Завершить заказ</a>
                <?php endif; ?>
                <a href="delete_order.php?order_id=<?php echo htmlspecialchars($order['OrderID']); ?>" class="btn_delete" onclick="return confirm('Вы уверены, что хотите удалить этот заказ?')">Удалить</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="no_order">
        <img src="./img/box.png" alt="Заказ">
        <h3>Заказов пока нет</h3>
    </div>
<?php endif; ?>
</div>
</body>
</html>
