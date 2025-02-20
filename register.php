<?php
session_start();
require_once 'connect.php';
require_once 'send_email.php'; // Файл, в котором определена функция sendVerificationCode()

// Получаем ошибки и введённые данные из сессии (если были)
$errors = $_SESSION['errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['form_data']);

// Определяем текущий шаг регистрации (по умолчанию 1)
$currentStep = $_GET['step'] ?? 1;

// Обработка POST-запросов по шагам
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $_SESSION['user_id'] = $user_id; // ID нового пользователя
    $_SESSION['user_name'] = $userName; // Имя пользователя
    $_SESSION['user_avatar'] = $userAvatar; // Аватар пользователя
    // --- Шаг 1: Ввод email ---
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);

        if (empty($email)) {
            $errors['email'] = 'Email обязателен для заполнения.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный формат email.';
        }

        if (empty($errors)) {
            // Проверяем уникальность email в БД
            $stmt = $conn->prepare("SELECT Email FROM users WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors['email'] = 'Email уже зарегистрирован.';
            } else {
                // Сохраняем email в сессии
                $_SESSION['email'] = $email;

                // Генерация кода подтверждения
                $verificationCode = rand(100000, 999999);

                // Отправка кода подтверждения
                $response = sendVerificationCode($email, $verificationCode);

                if ($response['success']) {
                    $_SESSION['verification_code'] = $verificationCode;
                    header("Location: register.php?step=2");
                    exit;
                } else {
                    $errors['general'] = 'Ошибка при отправке письма: ' . $response['error'];
                }
            }
        }

        $_SESSION['errors'] = $errors;
        header("Location: register.php?step=1");
        exit;
    }

    // --- Шаг 2: Подтверждение кода ---
    if (isset($_POST['verification_code'])) {
        $enteredCode = trim($_POST['verification_code']);
        $storedCode = $_SESSION['verification_code'] ?? '';

        if (empty($enteredCode)) {
            $errors['verification_code'] = 'Код подтверждения обязателен для ввода.';
        } elseif ($enteredCode != $storedCode) {  // нестрогое сравнение позволяет избежать проблем с типами
            $errors['verification_code'] = 'Неверный код подтверждения.';
        }

        if (empty($errors)) {
            $_SESSION['verified'] = true;
            header("Location: register.php?step=3");
            exit;
        }

        $_SESSION['errors'] = $errors;
        header("Location: register.php?step=2");
        exit;
    }

    // --- Шаг 3: Регистрация пользователя ---
    if (isset($_POST['firstName'])) {
        $formData = $_POST;

        // Валидация имени
        if (empty($_POST['firstName'])) {
            $errors['firstName'] = 'Имя обязательно для заполнения.';
        }

        // Валидация телефона
        $phone = trim($_POST['phone']);
        if (empty($phone)) {
            $errors['phone'] = 'Телефон обязателен для заполнения.';
        } elseif (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone)) {
            $errors['phone'] = 'Некорректный формат телефона.';
        }

        // Валидация пароля
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm-password'];
        if (empty($password)) {
            $errors['password'] = 'Пароль обязателен для заполнения.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm-password'] = 'Пароли не совпадают.';
        }

        // Проверяем наличие email в сессии
        if (!isset($_SESSION['email'])) {
            $errors['general'] = 'Произошла ошибка. Пожалуйста, повторите регистрацию.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $formData;
            header("Location: register.php?step=3");
            exit;
        }

        $email = $_SESSION['email'];
        $firstName = trim($_POST['firstName']);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Проверка уникальности email и телефона
        $stmt = $conn->prepare("SELECT Email, Phone FROM users WHERE Email = ? OR Phone = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors['general'] = 'Email или номер телефона уже зарегистрированы.';
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $formData;
            header("Location: register.php?step=3");
            exit;
        }

        // При регистрации устанавливаем аватарку по умолчанию
        $defaultAvatar = './img/photo.png';

        // Вставка данных в базу данных (включая путь к аватарке по умолчанию)
        $stmt = $conn->prepare("INSERT INTO users (FirstName, Email, Phone, Password, Avatar) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Ошибка подготовки запроса: " . $conn->error);
            $errors['general'] = 'Ошибка при регистрации.';
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $formData;
            header("Location: register.php?step=3");
            exit;
        }

        $stmt->bind_param("sssss", $firstName, $email, $phone, $hashedPassword, $defaultAvatar);

        if ($stmt->execute()) {
            // Сохраняем id нового пользователя в сессии
            $_SESSION['user_id'] = $conn->insert_id;
             // Получаем имя пользователя из POST-данных и сохраняем его в сессии
            $_SESSION['user_name'] = trim($_POST['firstName']);
            // Очистка сессионных данных регистрации
            unset($_SESSION['email'], $_SESSION['verification_code'], $_SESSION['verified']);
            header("Location: index.php");  // Перенаправление на главную страницу
            exit;
        } else {
            error_log("Ошибка выполнения запроса: " . $stmt->error);
            $errors['general'] = 'Ошибка при регистрации.';
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $formData;
            header("Location: register.php?step=3");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Регистрация</title>
  <style>
      body {
          font-family: Arial, sans-serif;
          background-color: #f4f4f9;
          margin: 0;
          padding: 0;
          display: flex;
          justify-content: center;
          align-items: center;
          height: 100vh;
      }
      .form-container {
          background: #fff;
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          width: 100%;
          max-width: 400px;
      }
      h2 {
          text-align: center;
          color: #333;
          margin-bottom: 20px;
      }
      input[type="email"],
      input[type="text"],
      input[type="tel"],
      input[type="password"],
      button {
          width: 100%;
          padding: 10px;
          margin: 10px 0;
          border: 1px solid #ccc;
          border-radius: 4px;
          font-size: 16px;
      }
      button {
          background-color: #007bff;
          color: #fff;
          border: none;
          cursor: pointer;
          transition: background-color 0.3s ease;
      }
      button:hover {
          background-color: #0056b3;
      }
      .toggle-password {
          position: relative;
      }
      .toggle-password span {
          position: absolute;
          right: 10px;
          top: 50%;
          transform: translateY(-50%);
          font-size: 18px;
          cursor: pointer;
      }
      .form-step {
          display: none;
      }
      .form-step.active {
          display: block;
      }
      .notification.error {
          color: red;
          margin-bottom: 10px;
      }
  </style>
</head>
<body>
  <div class="form-container">
      <h2>Регистрация</h2>
      
      <!-- Если есть общая ошибка, выводим её -->
      <?php if(isset($errors['general'])): ?>
          <div class="notification error"><?= htmlspecialchars($errors['general']) ?></div>
      <?php endif; ?>

      <?php if ($currentStep == 1): ?>
      <!-- Шаг 1: Ввод email -->
      <form action="register.php" method="POST" class="form-step active" id="step1">
          <?php if(isset($errors['email'])): ?>
              <div class="notification error"><?= htmlspecialchars($errors['email']) ?></div>
          <?php endif; ?>
          <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
          <button type="submit">Далее</button>
      </form>
      <?php endif; ?>

      <?php if ($currentStep == 2): ?>
      <!-- Шаг 2: Подтверждение кода -->
      <form action="register.php" method="POST" class="form-step active" id="step2">
          <?php if(isset($errors['verification_code'])): ?>
              <div class="notification error"><?= htmlspecialchars($errors['verification_code']) ?></div>
          <?php endif; ?>
          <input type="text" name="verification_code" placeholder="Введите код из письма" required>
          <button type="submit">Подтвердить</button>
      </form>
      <?php endif; ?>

      <?php if ($currentStep == 3): ?>
      <!-- Шаг 3: Заполнение данных -->
      <form action="register.php" method="POST" class="form-step active" id="step3" onsubmit="return validateForm()">
          <?php if(isset($errors['firstName'])): ?>
              <div class="notification error"><?= htmlspecialchars($errors['firstName']) ?></div>
          <?php endif; ?>
          <input type="text" name="firstName" placeholder="Имя" value="<?= htmlspecialchars($formData['firstName'] ?? '') ?>" required>
          
          <?php if(isset($errors['phone'])): ?>
              <div class="notification error"><?= htmlspecialchars($errors['phone']) ?></div>
          <?php endif; ?>
          <input type="tel" id="phone" name="phone" placeholder="+7 (___) ___-__-__" value="<?= htmlspecialchars($formData['phone'] ?? '') ?>" required>
          
          <div class="toggle-password">
              <?php if(isset($errors['password'])): ?>
                  <div class="notification error"><?= htmlspecialchars($errors['password']) ?></div>
              <?php endif; ?>
              <input type="password" id="password" name="password" placeholder="Пароль" required>
              <span class="togglePassword">🙈</span>
          </div>
          
          <div class="toggle-password">
              <?php if(isset($errors['confirm-password'])): ?>
                  <div class="notification error"><?= htmlspecialchars($errors['confirm-password']) ?></div>
              <?php endif; ?>
              <input type="password" id="confirm-password" name="confirm-password" placeholder="Подтвердите пароль" required>
              <span class="togglePassword">🙈</span>
          </div>
          
          <label>
              <input type="checkbox" name="agreement" required> Согласие на обработку данных
          </label>
          <button type="submit">Зарегистрироваться</button>
      </form>
      <?php endif; ?>
  </div>

  <!-- Подключаем jQuery и Inputmask, а также реализуем переключение отображения пароля -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
  <script>
      document.addEventListener('DOMContentLoaded', function () {
          const togglePasswordButtons = document.querySelectorAll('.togglePassword');
          togglePasswordButtons.forEach(button => {
              button.addEventListener('click', function () {
                  const passwordField = this.previousElementSibling;
                  const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                  passwordField.setAttribute('type', type);
                  this.textContent = type === 'password' ? '🙈' : '🐵';
              });
          });
          // Инициализация маски для телефона
          Inputmask("+7 (999) 999-99-99").mask("#phone");
      });

      function validateForm() {
          const password = document.getElementById('password').value;
          const confirmPassword = document.getElementById('confirm-password').value;

          if (password !== confirmPassword) {
              alert('Пароли не совпадают!');
              return false;
          }
          return true;
      }
  </script>
</body>
</html>
