<?php
require_once 'connect.php';  // Подключаемся к базе данных

// Данные администратора (например, первый админ)
$email = "admin@gmail.com";
$password = "admin123";  // Пароль, который вы хотите установить для администратора
$firstName = "Администратор";
$lastName = "Системный";

// Хешируем пароль
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Вставляем данные в таблицу
$stmt = $conn->prepare("INSERT INTO admins (Email, Password, FirstName, LastName) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $hashedPassword, $firstName, $lastName);

if ($stmt->execute()) {
    echo "Администратор успешно добавлен!";
} else {
    echo "Ошибка при добавлении администратора: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
