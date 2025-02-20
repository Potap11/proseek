<?php
session_start();
require_once 'connect.php';

// Проверяем, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['user_id'];

// Если в URL передан specialist_id, используем его
if (isset($_GET['specialist_id'])) {
    $specialistID = $_GET['specialist_id'];
} else {
    $specialistID = null;
}

// Обработка формы отправки отзыва
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $specialistID = $_POST['specialist_id'];
    $rating = trim($_POST['rating']);
    $reviewText = trim($_POST['text']);
    
    // Проверка обязательных полей
    $errors = [];
    if (empty($specialistID)) {
        $errors[] = "Не выбран специалист.";
    }
    if (empty($rating)) {
        $errors[] = "Укажите оценку.";
    }
    if (empty($reviewText)) {
        $errors[] = "Введите отзыв.";
    }
    
    if (empty($errors)) {
        // Вставляем отзыв в таблицу reviews
        $stmt = $conn->prepare("INSERT INTO reviews (UserID, SpecialistID, Text, Rating) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("Ошибка подготовки запроса: " . $conn->error);
        }
        $stmt->bind_param("iisi", $userID, $specialistID, $reviewText, $rating);
        
        if ($stmt->execute()) {
            echo '<script>alert("Ваш отзыв успешно отправлен!"); window.location.href = "user_dashboard.php";</script>';
            exit;
        } else {
            echo '<script>alert("Ошибка при отправке отзыва: ' . $stmt->error . '");</script>';
        }
        $stmt->close();
    } else {
        foreach ($errors as $error) {
            echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>';
        }
    }
}

// Если специалист не выбран через GET, получаем список специалистов для выбора
$specialists = [];
if ($specialistID === null) {
    $result = $conn->query("SELECT SpecialistID, Surname, FirstName FROM specialists");
    while ($row = $result->fetch_assoc()) {
        $specialists[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оставить отзыв</title>
    <link rel="stylesheet" href="./style/style.css">
    <style>
        .form-container {
            width: 500px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-container label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        .form-container input[type="text"],
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-container button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Оставьте отзыв о специалисте</h2>
        <form action="create_review.php" method="POST">
            <?php if ($specialistID === null): ?>
                <select name="specialist_id" id="specialist_id" required>
                    <option value="">--Выберите специалиста--</option>
                    <?php foreach ($specialists as $spec): ?>
                        <option value="<?php echo $spec['SpecialistID']; ?>">
                            <?php echo htmlspecialchars($spec['Surname'] . ' ' . $spec['FirstName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="specialist_id" value="<?php echo htmlspecialchars($specialistID); ?>">
                <?php
                // Если specialist_id передан, можно отобразить имя специалиста:
                $stmt = $conn->prepare("SELECT Surname, FirstName FROM specialists WHERE SpecialistID = ?");
                $stmt->bind_param("i", $specialistID);
                $stmt->execute();
                $stmt->bind_result($specSurname, $specFirstName);
                $stmt->fetch();
                $stmt->close();
                ?>
                <p>Специалист: <?php echo htmlspecialchars($specSurname . ' ' . $specFirstName); ?></p>
            <?php endif; ?>

            <label for="rating">Оценка:</label>
            <select name="rating" id="rating" required>
                <option value="">--Выберите оценку--</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>

            <label for="text">Ваш отзыв:</label>
            <textarea name="text" id="text" rows="5" placeholder="Напишите ваш отзыв" required></textarea>

            <button type="submit">Отправить отзыв</button>
        </form>
    </div>
</body>
</html>
