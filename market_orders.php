<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли специалист
if (!isset($_SESSION['specialist_id'])) {
    header("Location: login_spec.php");
    exit;
}

// Получаем данные специалиста из сессии
$specialistName = $_SESSION['specialist_name'];
$specialistAvatar = $_SESSION['specialist_avatar'] ?? './img/photo.png';
$specialistCategoryID = $_SESSION['specialist_categoryID']; // Категория специалиста

// Отладка: вывод CategoryID
 echo "CategoryID специалиста: " . $specialistCategoryID . "<br>";

// Проверяем, что CategoryID указан
if (empty($specialistCategoryID)) {
    die("Ошибка: CategoryID специалиста не указан.");
}

// Получаем параметры фильтров из GET-запроса
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

// Формируем SQL-запрос с использованием подготовленных выражений
$sql = "SELECT o.OrderID, o.Service, o.ProblemDescription, o.Price, o.CreatedAt, c.CategoryName
        FROM orders o
        JOIN specialist_categories c ON o.CategoryID = c.CategoryID
        WHERE o.Status = 'ожидает' AND o.CategoryID = ?";

if ($minPrice !== null) {
    $sql .= " AND o.Price >= ?";
}
if ($maxPrice !== null) {
    $sql .= " AND o.Price <= ?";
}
if ($search !== null && !empty($search)) {
    $sql .= " AND (o.Service LIKE ? OR o.ProblemDescription LIKE ?)";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

$params = [$specialistCategoryID];
$types = "i";

if ($minPrice !== null) {
    $params[] = $minPrice;
    $types .= "d";
}
if ($maxPrice !== null) {
    $params[] = $maxPrice;
    $types .= "d";
}
if ($search !== null && !empty($search)) {
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

$stmt->bind_param($types, ...$params);
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
    <title>Главная страница специалиста</title>
    <link rel="stylesheet" href="./style/style.css">
</head>
<style>
    /* Общие стили */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
        display: flex;
    }

    /* Шапка */
    header {
        background-color: #007bff;
        color: #fff;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
    }

    header .logo {
        font-size: 24px;
        font-weight: bold;
    }

    header .profile {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        cursor: pointer;
    }

    header .profile img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }

    header .profile .dropdown {
        display: none;
        position: absolute;
        top: 50px;
        right: 0;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        min-width: 150px;
        z-index: 1000;
    }

    header .profile .dropdown a {
        display: block;
        padding: 10px;
        color: #333;
        text-decoration: none;
    }

    header .profile .dropdown a:hover {
        background-color: #f1f1f1;
    }

    /* Фильтры */
    .filters {
        width: 250px;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: fixed;
        top: 70px;
        bottom: 0;
        overflow-y: auto;
    }

    .filters label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .filters input {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .filters button {
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .filters button:hover {
        background-color: #0056b3;
    }

    /* Основной контент */
    .container {
        margin-left: 270px;
        margin-top: 70px;
        flex: 1;
        padding: 20px;
    }

    h1 {
        text-align: center;
        color: #333;
    }

    /* Стили для списка заказов */
    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .order-card {
        background-color: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .order-card h3 {
        margin: 0 0 10px;
        font-size: 18px;
    }

    .order-card p {
        margin: 5px 0;
    }

    .order-card .price {
        font-weight: bold;
        color: #007bff;
    }

    .order-card .category {
        font-style: italic;
        color: #555;
    }

    .order-card .date {
        font-size: 14px;
        color: #777;
    }

    /* Кнопка отклика */
    .btn-respond {
        display: inline-block;
        padding: 8px 12px;
        background-color: #28a745;
        color: #fff;
        border-radius: 4px;
        text-decoration: none;
        margin-top: 10px;
        transition: background-color 0.3s ease;
    }

    .btn-respond:hover {
        background-color: #218838;
    }
</style>

<body>
    <!-- Шапка -->
    <header>
        <div class="logo">ProSeek</div>
        <div class="profile" id="profile">
            <img src="<?php echo htmlspecialchars($specialistAvatar); ?>" alt="Фото профиля">
            <span><?php echo htmlspecialchars($specialistName); ?></span>
            <div class="dropdown" id="dropdown">
                <a href="spec_dashboard.php">Профиль</a>
                <a href="specialist_responses.php">Мои отклики</a>
                <a href="logout.php">Выйти</a>
            </div>
        </div>
    </header>
    <!-- Фильтры -->
    <div class="filters">
        <form method="GET" action="">
            <label for="min_price">Цена от:</label>
            <input type="number" id="min_price" name="min_price" step="0.01" min="0"
                value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
            <label for="max_price">Цена до:</label>
            <input type="number" id="max_price" name="max_price" step="0.01" min="0"
                value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
            <label for="search">Поиск:</label>
            <input type="text" id="search" name="search" placeholder="Введите ключевые слова"
                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit">Применить фильтры</button>
        </form>
    </div>
    <!-- Основной контент -->
    <div class="container">
        <h1>Главная страница специалиста</h1>
        <!-- Список заказов -->
        <div class="orders-list">
    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <h3><?php echo htmlspecialchars($order['Service']); ?></h3>
                <p><?php echo htmlspecialchars($order['ProblemDescription']); ?></p>
                <p class="price">Цена: <?php echo htmlspecialchars($order['Price']); ?> руб.</p>
                <p class="category">Категория: <?php echo htmlspecialchars($order['CategoryName']); ?></p>
                <p class="date">Дата создания: <?php echo htmlspecialchars($order['CreatedAt']); ?></p>
                <!-- Кнопка отклика -->
                <a href="take_order.php?order_id=<?php echo $order['OrderID']; ?>" class="btn-respond">Откликнуться</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Заказов не найдено.</p>
    <?php endif; ?>
</div>

    </div>
    <script>
        // Управление выпадающим меню
        const profile = document.getElementById('profile');
        const dropdown = document.getElementById('dropdown');
        profile.addEventListener('click', function (event) {
            event.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function (event) {
            if (!profile.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>
