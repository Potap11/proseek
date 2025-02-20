<?php
session_start();
require_once 'connect.php'; // Подключаем базу данных

// Если пользователь не авторизован, перенаправляем на страницу авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получаем данные пользователя
$user_id    = $_SESSION['user_id'];
$userName   = $_SESSION['user_name'] ?? '';

// Обработка формы заказа
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $service       = trim($_POST['service']);
    $description   = trim($_POST['description']);
    $executionType = trim($_POST['execution_type']);
    $category      = trim($_POST['category']);
    $address       = ($executionType === 'with_visit') ? trim($_POST['address']) : null;  // Если нет выезда, устанавливаем NULL
    $price         = isset($_POST['price']) ? floatval($_POST['price']) : null;           // Получаем цену

    // Валидация цены
    if ($price !== null && $price < 0) {
        die("Цена не может быть отрицательной.");
    }

    // Подготовка запроса
    $stmt = $conn->prepare("INSERT INTO orders (Service, ClientName, Address, DateTime, ProblemDescription, CreatedAt, CategoryID, user_id, Price) 
                            VALUES (?, ?, ?, NOW(), ?, NOW(), ?, ?, ?)");

    if ($stmt === false) {
        die("Ошибка при подготовке запроса: " . $conn->error);
    }

    // Исправленная строка формата и передача параметров
    $stmt->bind_param("ssssiid", $service, $userName, $address, $description, $category, $user_id, $price);

    if ($stmt->execute()) {
        echo '<script>alert("Заказ успешно создан!"); window.location.href = "order.php";</script>';
        exit;
    } else {
        echo '<script>alert("Ошибка при создании заказа: ' . $stmt->error . '");</script>';
    }
    $stmt->close();
}
?>

<?php
// Если пользователь авторизован, подключаем header.php
if (isset($_SESSION['user_id'])) {
    include('header.php');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание заказа</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            margin: 30px auto;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .cbtn {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            padding: 10px 20px;
            font-size: 16px;
        }
        .cbtn:hover {
            background-color: #0056b3;
        }
        label {
            font-weight: bold;
        }
        .address-field {
            display: none;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Создание заказа</h2>
    <form action="create_order.php" method="POST">
        <label for="service">Услуга:</label>
        <input type="text" id="service" name="service" placeholder="Например: Создание сайта" required>

        <label for="category">Категория специалиста:</label>
        <select id="category" name="category" required>
            <option value="">Выберите категорию</option>
            <?php
            // Получаем список категорий специалистов из базы данных
            $result = $conn->query("SELECT CategoryID, CategoryName FROM specialist_categories");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['CategoryID'] . "'>" . $row['CategoryName'] . "</option>";
            }
            ?>
        </select>

        <label for="description">Описание заказа:</label>
        <textarea id="description" name="description" rows="5" placeholder="Опишите ваш заказ подробно" required></textarea>

        <label for="execution_type">Выбор места исполнения:</label>
        <select id="execution_type" name="execution_type" required>
            <option value="remote">Дистанционно</option>
            <option value="with_visit">С выездом</option>
        </select>

        <!-- Поле для ввода адреса, если выбран вариант "с выездом" -->
        <div id="address-field" class="address-field" style="<?php echo $executionType === 'remote' ? 'display: none;' : ''; ?>">
            <label for="address">Адрес:</label>
            <input type="text" id="address" name="address" placeholder="Введите адрес">
        </div>

        <!-- Поле для ввода цены -->
        <label for="price">Цена:</label>
        <input type="number" id="price" name="price" step="0.01" placeholder="Укажите цену" min="0">

        <button type="submit" class="cbtn">Создать заказ</button>
    </form>
</div>

<script>
    // При изменении типа исполнения показываем/скрываем поле для адреса
    const executionTypeSelect = document.getElementById('execution_type');
    const addressField = document.getElementById('address-field');
    const addressInput = document.getElementById('address');

    executionTypeSelect.addEventListener('change', function() {
        if (executionTypeSelect.value === 'with_visit') {
            addressField.style.display = 'block';
            addressInput.required = true;
        } else {
            addressField.style.display = 'none';
            addressInput.required = false;
        }
    });
</script>
</body>
</html>