<?php
session_start();
require_once 'connect.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id']) && !isset($_SESSION['specialist_id'])) {
    header("Location: login.php");
    exit;
}

// Определяем роль текущего пользователя
if (isset($_SESSION['user_id'])) {
    $currentUserRole = 'user';  // Клиент
} else {
    $currentUserRole = 'specialist';
}

// Если клиент – выводим список специалистов, иначе выводим список клиентов
if ($currentUserRole === 'user') {
    $query = "SELECT SpecialistID, Surname, FirstName, Photo FROM specialists";
    $result = $conn->query($query);
    $partners = [];
    while ($row = $result->fetch_assoc()) {
        $partners[] = $row;
    }
    $result->free();
} else {
    // Если специалист – выводим список пользователей
    $query = "SELECT UserID, FirstName, Avatar FROM users";
    $result = $conn->query($query);
    $partners = [];
    while ($row = $result->fetch_assoc()) {
        $partners[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выберите собеседника для чата</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .partner-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .partner-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: 200px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .partner-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .partner-card h3 {
            margin: 10px 0;
            font-size: 18px;
            color: #333;
        }
        .partner-card a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .partner-card a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Выберите собеседника для чата</h1>
    <div class="partner-list">
        <?php if (!empty($partners)): ?>
            <?php foreach ($partners as $partner): ?>
                <div class="partner-card">
                    <?php if ($currentUserRole === 'user'): ?>
                        <img src="<?php echo htmlspecialchars($partner['Photo'] ?: './img/default_avatar.png'); ?>" alt="Фото специалиста">
                        <h3><?php echo htmlspecialchars($partner['Surname'] . ' ' . $partner['FirstName']); ?></h3>
                        <a href="chat.php?partner=<?php echo $partner['SpecialistID']; ?>">Начать чат</a>
                    <?php else: ?>
                        <img src="<?php echo htmlspecialchars($partner['Avatar'] ?: './img/default_avatar.png'); ?>" alt="Фото клиента">
                        <h3><?php echo htmlspecialchars($partner['FirstName']); ?></h3>
                        <a href="chat.php?partner=<?php echo $partner['UserID']; ?>">Начать чат</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Собеседников не найдено.</p>
        <?php endif; ?>
    </div>
</body>
</html>
