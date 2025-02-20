<?php
// send_email.php
// Подключаем автозагрузчик Composer (если используете PHPMailer)
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Функция отправки кода подтверждения
 *
 * @param string $email
 * @param string|int|null $verificationCode Если не передан, генерируется случайный
 * @return array Массив с ключами success и code или error
 */
function sendVerificationCode($email, $verificationCode = null) {
    if ($verificationCode === null) {
        $verificationCode = rand(100000, 999999);
    }

    $mail = new PHPMailer(true);

    try {
        // Настройки SMTP для Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // SMTP-сервер Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'vx454992@gmail.com'; // Ваш Gmail-адрес
        $mail->Password   = 'hwak qrqo smll aqca'; // Пароль приложения или основной пароль
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Отправитель и получатель
        $mail->setFrom('vx454992@gmail.com', 'ProSeek');
        $mail->addAddress($email);

        // Текст письма
        $mail->isHTML(false); // Отправляем текст, а не HTML
        $mail->Subject = 'Код подтверждения';
        $mail->Body    = "Ваш код подтверждения: $verificationCode";

        // Отправка письма
        $mail->send();

        return ['success' => true, 'code' => $verificationCode];
    } catch (Exception $e) {
        error_log("Ошибка отправки письма: " . $e->getMessage());
        return ['success' => false, 'error' => 'Ошибка при отправке письма: ' . $mail->ErrorInfo];
    }
}

// Если файл вызывается напрямую (например, через AJAX), выполняем обработку запроса
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data) || !isset($data['email'])) {
        echo json_encode(['success' => false, 'error' => 'Email не указан']);
        exit;
    }

    $email = trim($data['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Некорректный формат email']);
        exit;
    }

    // Можно либо генерировать код здесь, либо передать его, если требуется
    $response = sendVerificationCode($email);
    echo json_encode($response);
    exit;
}
?>
