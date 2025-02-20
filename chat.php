<?php
session_start();
require_once 'connect.php';

// Определяем текущего пользователя (если заказчик, то user_id; если специалист – specialist_id)
if(isset($_SESSION['user_id'])){
    $currentUserId = $_SESSION['user_id'];
} elseif(isset($_SESSION['specialist_id'])){
    $currentUserId = $_SESSION['specialist_id'];
} else {
    header("Location: login.php");
    exit;
}

// Получаем ID собеседника (партнера) из GET-параметра
if (!isset($_GET['partner'])) {
    die("Партнер не указан.");
}
$partnerId = intval($_GET['partner']);

// Обработка отправки нового сообщения
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $messageText = trim($_POST['message']);
    if (!empty($messageText)) {
        $stmt = $conn->prepare("INSERT INTO messages (SenderID, ReceiverID, Text, SentAt, IsRead) VALUES (?, ?, ?, NOW(), 0)");
        if ($stmt === false) {
            die("Ошибка подготовки запроса: " . $conn->error);
        }
        $stmt->bind_param("iis", $currentUserId, $partnerId, $messageText);
        $stmt->execute();
        $stmt->close();
        // Перезагружаем страницу для обновления списка сообщений
        header("Location: chat.php?partner=" . $partnerId);
        exit;
    }
}

// Извлекаем все сообщения между текущим пользователем и собеседником
$stmt = $conn->prepare("SELECT SenderID, ReceiverID, Text, SentAt FROM messages 
                        WHERE (SenderID = ? AND ReceiverID = ?) OR (SenderID = ? AND ReceiverID = ?)
                        ORDER BY SentAt ASC");
if ($stmt === false) {
    die("Ошибка подготовки запроса: " . $conn->error);
}
$stmt->bind_param("iiii", $currentUserId, $partnerId, $partnerId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .chat-header {
            background: #007bff;
            color: #fff;
            padding: 15px;
            text-align: center;
        }
        .chat-messages {
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
            border-bottom: 1px solid #ddd;
        }
        .message {
            margin-bottom: 10px;
            padding: 8px;
            border-radius: 4px;
        }
        .message.sent {
            background: #e6f7ff;
            text-align: right;
        }
        .message.received {
            background: #f0f0f0;
            text-align: left;
        }
        .message .time {
            font-size: 12px;
            color: #999;
        }
        .chat-input {
            padding: 15px;
        }
        .chat-input form {
            display: flex;
        }
        .chat-input input[type="text"] {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .chat-input button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            background: #007bff;
            color: #fff;
            margin-left: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .chat-input button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        <h2>Чат с партнером (ID: <?php echo $partnerId; ?>)</h2>
    </div>
    <div class="chat-messages">
        <?php if (count($messages) > 0): ?>
            <?php foreach ($messages as $msg): ?>
                <?php $msgClass = ($msg['SenderID'] == $currentUserId) ? 'sent' : 'received'; ?>
                <div class="message <?php echo $msgClass; ?>">
                    <p><?php echo htmlspecialchars($msg['Text']); ?></p>
                    <div class="time"><?php echo htmlspecialchars($msg['SentAt']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Чат пуст. Начните общение!</p>
        <?php endif; ?>
    </div>
    <div class="chat-input">
        <form action="chat.php?partner=<?php echo $partnerId; ?>" method="POST">
            <input type="text" name="message" placeholder="Введите сообщение..." required>
            <button type="submit">Отправить</button>
        </form>
    </div>
</div>
</body>
</html>
