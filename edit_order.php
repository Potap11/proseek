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

// Извлекаем данные заказа
$stmt = $conn->prepare("SELECT * FROM orders WHERE OrderID = ? AND user_id = ?");
$stmt->bind_param("ii", $orderID, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Заказ не найден или вы не имеете прав на его редактирование.");
}

// Обработка формы редактирования
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service = trim($_POST['service']);
    $description = trim($_POST['description']);
    $address = isset($_POST['address']) ? trim($_POST['address']) : null;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : null; // Обработка цены

    // Валидация цены
    if ($price !== null && $price < 0) {
        die("Цена не может быть отрицательной.");
    }

    // Обновляем данные заказа
    $stmt = $conn->prepare("UPDATE orders SET Service = ?, ProblemDescription = ?, Address = ?, Price = ? WHERE OrderID = ?");
    $stmt->bind_param("sssdi", $service, $description, $address, $price, $orderID);

    if ($stmt->execute()) {
        echo '<script>alert("Заказ успешно обновлен!"); window.location.href = "order.php";</script>';
        exit;
    } else {
        echo '<script>alert("Ошибка при обновлении заказа.");</script>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование заказа</title>
    <link rel="stylesheet" href="./style/style.css">
</head>
<style>
    
    /* Общий стиль для формы */
.edit-order-container {
    max-width: 600px;
    margin: 50px auto;
    background-color: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Заголовок формы */
.edit-order-container h2 {
    text-align: center;
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

/* Стиль для полей ввода */
.edit-order-container input[type="text"],
.edit-order-container textarea,
.edit-order-container select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    resize: vertical; /* Позволяет изменять высоту textarea */
}

/* Стиль для описания заказа (textarea) */
.edit-order-container textarea {
    min-height: 100px;
}

/* Стиль для метки (label) */
.edit-order-container label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    color: #555;
}

/* Стиль для кнопок */
.edit-order-container button {
    display: inline-block;
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

/* Эффект при наведении на кнопку */
.edit-order-container button:hover {
    background-color: #0056b3;
}

/* Кнопка "Отмена" */
.edit-order-container .btn-cancel {
    background-color: #f44336;
}

.edit-order-container .btn-cancel:hover {
    background-color: #d32f2f;
}

/* Группировка кнопок */
.edit-order-container .button-group {
    display: flex;
    gap: 10px;
}

.edit-order-container .button-group button {
    flex: 1;
}

/* Стиль для сообщений об ошибках */
.error-message {
    color: red;
    font-size: 14px;
    margin-bottom: 10px;
}

/* Стиль для блока адреса */
.address-field {
    display: flex;
    flex-direction: column;
}

/* Стиль для выбора типа исполнения */
.execution-type {
    margin-bottom: 15px;
}

.execution-type label {
    font-weight: normal;
    margin-right: 10px;
}

.execution-type input[type="radio"] {
    margin-right: 5px;
}
.btn-cancel{
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    padding: 5px 25px; 
    border-radius: 5px;
}
</style>
<body>
<div class="edit-order-container">
    <h2>Редактирование заказа</h2>
    <form action="edit_order.php?order_id=<?php echo htmlspecialchars($orderID); ?>" method="POST">
        <!-- Название услуги -->
        <label for="service">Услуга:</label>
        <input type="text" id="service" name="service" value="<?php echo htmlspecialchars($order['Service']); ?>" required>

        <!-- Категория специалиста -->
        <label for="category">Категория специалиста:</label>
        <select id="category" name="category" required>
            <option value="">Выберите категорию</option>
            <?php
            $result = $conn->query("SELECT CategoryID, CategoryName FROM specialist_categories");
            while ($row = $result->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['CategoryID']); ?>" 
                        <?php if ($row['CategoryID'] == $order['CategoryID']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['CategoryName']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- Описание заказа -->
        <label for="description">Описание заказа:</label>
        <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($order['ProblemDescription']); ?></textarea>

        <!-- Поле для ввода цены -->
        <label for="price">Цена:</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($order['Price'] ?? ''); ?>">

        <!-- Выбор типа исполнения -->
        <label for="execution_type">Тип исполнения:</label>
        <select id="execution_type" name="execution_type" required>
            <option value="remote" <?php if ($order['Address'] === null) echo 'selected'; ?>>Дистанционно</option>
            <option value="with_visit" <?php if ($order['Address'] !== null) echo 'selected'; ?>>С выездом</option>
        </select>

        <!-- Поле для адреса (скрыто по умолчанию) -->
        <div id="address-field" class="address-field" style="<?php echo $order['Address'] === null ? 'display: none;' : ''; ?>">
            <label for="address">Адрес:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($order['Address'] ?? ''); ?>" required>
        </div>

            <div class="button-group">
        <button type="submit">Сохранить изменения</button>
        <a href="order.php" class="btn-cancel">Отменить</a>
    </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const executionTypeSelect = document.getElementById('execution_type');
        const addressField = document.getElementById('address-field');

        // Функция для обновления видимости поля адреса
        function updateAddressFieldVisibility() {
            if (executionTypeSelect.value === 'with_visit') {
                addressField.style.display = 'block'; // Показываем поле адреса
                document.getElementById('address').required = true; // Делаем поле обязательным
            } else {
                addressField.style.display = 'none'; // Скрываем поле адреса
                document.getElementById('address').required = false; // Убираем обязательность
            }
        }

        // Обработчик события при изменении выбора
        executionTypeSelect.addEventListener('change', updateAddressFieldVisibility);

        // Инициализация видимости при загрузке страницы
        updateAddressFieldVisibility();
    });
</script>
</body>
</html>