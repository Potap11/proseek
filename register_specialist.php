<?php
session_start();
require_once 'connect.php';

// Определяем текущий шаг регистрации (по умолчанию 1)
$currentStep = $_GET['step'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Шаг 1: Ввод ФИО, телефона, email и пароля
    if ($currentStep == 1) {
        $surname     = trim($_POST['surname']);
        $firstName   = trim($_POST['firstName']);
        $patronymic  = trim($_POST['patronymic']);
        $phone       = trim($_POST['phone']);
        $email       = trim($_POST['email']);
        $password    = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirmPassword']);

        // Проверка на пустые поля
        if (empty($surname) || empty($firstName) || empty($phone) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = "Все поля обязательны для заполнения (отчество может быть пустым).";
        } elseif ($password !== $confirmPassword) {
            $error = "Пароли не совпадают.";
        } else {
            // Проверка на существующего пользователя по email и телефону
            $stmt = $conn->prepare("SELECT SpecialistID FROM specialists WHERE Email = ? OR Phone = ?");
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Пользователь с таким email или телефоном уже зарегистрирован.";
            } else {
                $_SESSION['reg_surname']    = $surname;
                $_SESSION['reg_firstName']  = $firstName;
                $_SESSION['reg_patronymic'] = $patronymic;
                $_SESSION['reg_phone']      = $phone;
                $_SESSION['reg_email']      = $email;
                $_SESSION['reg_password']   = password_hash($password, PASSWORD_DEFAULT);
                header("Location: register_specialist.php?step=2");
                exit;
            }
            $stmt->close();
        }
    }

    // Шаг 2: Выбор категории
    if ($currentStep == 2) {
        $categoryID = intval($_POST['category']);

        if ($categoryID <= 0) {
            $error = "Выберите категорию.";
        } else {
            $_SESSION['reg_categoryID'] = $categoryID;
            header("Location: register_specialist.php?step=3");
            exit;
        }
    }

    // Шаг 3: Ввод адреса и загрузка фото
    if ($currentStep == 3) {
        $address = trim($_POST['address']);
        if (empty($address)) {
            $error = "Укажите ваш адрес.";
        }

        // Обработка загрузки фото
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $tmpName   = $_FILES['photo']['tmp_name'];
            $fileName  = $_FILES['photo']['name'];
            $fileSize  = $_FILES['photo']['size'];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($extension, $allowedExts)) {
                $error = "Недопустимый формат файла. Допустимые форматы: jpg, jpeg, png, gif.";
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $error = "Файл слишком большой. Максимальный размер - 5 МБ.";
            } else {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $newFileName = uniqid('photo_', true) . '.' . $extension;
                $destination = $uploadDir . $newFileName;
                if (move_uploaded_file($tmpName, $destination)) {
                    $photoPath = $destination;
                } else {
                    $error = "Не удалось загрузить файл.";
                }
            }
        }

        if (!isset($error)) {
                // Запись специалиста в таблицу
                $stmt = $conn->prepare("INSERT INTO specialists (Surname, FirstName, Patronymic, Phone, Email, Address, Photo, Password, CategoryID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssssi",
                    $_SESSION['reg_surname'],
                    $_SESSION['reg_firstName'],
                    $_SESSION['reg_patronymic'],
                    $_SESSION['reg_phone'],
                    $_SESSION['reg_email'],
                    $address,
                    $photoPath,
                    $_SESSION['reg_password'],
                    $_SESSION['reg_categoryID']  // CategoryID передается из сессии
                );
                

                if ($stmt->execute()) {
                    // Сохраняем в сессии ID специалиста
                    $_SESSION['specialist_id'] = $conn->insert_id;  // Сохраняем ID специалиста
                    $_SESSION['specialist_name'] = $_SESSION['reg_firstName'];  // Сохраняем имя специалиста
                    $_SESSION['specialist_avatar'] = $photoPath;  // Сохраняем путь к фото
                    $_SESSION['specialist_categoryID'] = $_SESSION['reg_categoryID'];  // Сохраняем CategoryID в сессии
                
                    // Перенаправляем на страницу профиля специалиста
                    header("Location: market_orders.php");
                    exit;
                } else {
                    $error = "Ошибка при сохранении данных специалиста.";
                }
                

                $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Регистрация специалиста</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.6/css/inputmask.min.css">
  <style>
    /* Общие стили */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .form-container {
        width: 100%;
        max-width: 500px;
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .form-container h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    .form-container label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #555;
    }

    .form-container input[type="text"],
    .form-container input[type="tel"],
    .form-container input[type="email"],
    .form-container input[type="password"],
    .form-container textarea,
    .form-container select {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .form-container input[type="text"]:focus,
    .form-container input[type="tel"]:focus,
    .form-container input[type="email"]:focus,
    .form-container input[type="password"]:focus,
    .form-container textarea:focus,
    .form-container select:focus {
        border-color: #007bff;
        outline: none;
    }

    .form-container textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-container button {
        width: 100%;
        padding: 12px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .form-container button:hover {
        background-color: #0056b3;
    }

    .error {
        color: #ff4d4d;
        margin-bottom: 20px;
        text-align: center;
    }

    /* Стили для загрузки файла */
    .form-container input[type="file"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        background-color: #f9f9f9;
    }

    /* Стили для выпадающего списка */
    .form-container select {
        appearance: none;
        background-color: #f9f9f9;
        cursor: pointer;
    }
  </style>
</head>
<body>
<div class="form-container">
  <h2>Регистрация специалиста</h2>
  <?php if(isset($error)): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <?php if ($currentStep == 1): ?>
  <!-- Шаг 1: Ввод ФИО, телефона, email и пароля -->
  <form method="post" action="">
      <label for="surname">Фамилия:</label>
      <input type="text" name="surname" id="surname" required>
      <label for="firstName">Имя:</label>
      <input type="text" name="firstName" id="firstName" required>
      <label for="patronymic">Отчество:</label>
      <input type="text" name="patronymic" id="patronymic">
      <label for="phone">Телефон:</label>
      <input type="tel" name="phone" id="phone" required>
      <label for="email">Электронная почта:</label>
      <input type="email" name="email" id="email" required>
      <label for="password">Пароль:</label>
      <input type="password" name="password" id="password" required>
      <label for="confirmPassword">Подтвердите пароль:</label>
      <input type="password" name="confirmPassword" id="confirmPassword" required>
      <button type="submit">Далее</button>
  </form>
  <?php elseif ($currentStep == 2): ?>
  <!-- Шаг 2: Выбор категории -->
  <form method="post" action="">
      <label for="category">Категория:</label>
      <select name="category" id="category" required>
          <option value="">Выберите категорию</option>
          <?php
          $categories = $conn->query("SELECT CategoryID, CategoryName FROM specialist_categories");
          while ($row = $categories->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($row['CategoryID']); ?>">
                  <?php echo htmlspecialchars($row['CategoryName']); ?>
              </option>
          <?php endwhile; ?>
      </select>
      <button type="submit">Далее</button>
  </form>
  <?php elseif ($currentStep == 3): ?>
  <!-- Шаг 3: Ввод адреса и загрузка фото -->
  <form method="post" action="" enctype="multipart/form-data">
      <label for="address">Ваш адрес:</label>
      <textarea name="address" id="address" required></textarea>
      <label for="photo">Загрузите ваше фото:</label>
      <input type="file" name="photo" id="photo" accept=".jpg, .jpeg, .png, .gif" required>
      <button type="submit">Зарегистрироваться</button>
  </form>
  <?php endif; ?>
</div>

<!-- Подключение библиотеки Inputmask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.6/jquery.inputmask.min.js"></script>
<script>
  // Инициализация маски телефона
  document.addEventListener('DOMContentLoaded', function () {
    Inputmask({
      mask: '+7 (999) 999-99-99',
      placeholder: '_',
      showMaskOnHover: false,
      showMaskOnFocus: true,
    }).mask(document.getElementById('phone'));
  });
</script>
</body>
</html>