<?php
session_start();
require_once 'connect.php';

// Проверка, что администратор авторизован
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// Извлекаем пользователей
$stmt = $conn->prepare("SELECT UserID, FirstName, Email, Phone FROM users");
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями</title>
</head>
<body>
    <h2>Управление пользователями</h2>
    <table>
        <tr>
            <th>Имя</th>
            <th>Электронная почта</th>
            <th>Телефон</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['FirstName']); ?></td>
            <td><?php echo htmlspecialchars($user['Email']); ?></td>
            <td><?php echo htmlspecialchars($user['Phone']); ?></td>
            <td>
                <a href="edit_user.php?user_id=<?php echo htmlspecialchars($user['UserID']); ?>">Редактировать</a> | 
                <a href="delete_user.php?user_id=<?php echo htmlspecialchars($user['UserID']); ?>" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
