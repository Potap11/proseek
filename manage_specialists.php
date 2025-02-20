<?php
session_start();
require_once 'connect.php';

// Проверка, что администратор авторизован
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// Извлекаем специалистов
$stmt = $conn->prepare("SELECT SpecialistID, Surname, FirstName, Email, Phone, CategoryID FROM specialists");
$stmt->execute();
$result = $stmt->get_result();
$specialists = [];
while ($row = $result->fetch_assoc()) {
    $specialists[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление специалистами</title>
</head>
<body>
    <h2>Управление специалистами</h2>
    <table>
        <tr>
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Электронная почта</th>
            <th>Телефон</th>
            <th>Категория</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($specialists as $specialist): ?>
        <tr>
            <td><?php echo htmlspecialchars($specialist['Surname']); ?></td>
            <td><?php echo htmlspecialchars($specialist['FirstName']); ?></td>
            <td><?php echo htmlspecialchars($specialist['Email']); ?></td>
            <td><?php echo htmlspecialchars($specialist['Phone']); ?></td>
            <td><?php echo htmlspecialchars($specialist['CategoryID']); ?></td>
            <td>
                <a href="edit_specialist.php?specialist_id=<?php echo htmlspecialchars($specialist['SpecialistID']); ?>">Редактировать</a> | 
                <a href="delete_specialist.php?specialist_id=<?php echo htmlspecialchars($specialist['SpecialistID']); ?>" onclick="return confirm('Вы уверены, что хотите удалить этого специалиста?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
