<!-- create_user.php -->

<?php
session_start();

// Проверяем, что пользователь аутентифицирован и имеет роль администратора. Если нет, перенаправляем на страницу входа.
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$notification = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];
    $position = $_POST['position'];

// Создаем новое соединение с базой данных MySQL
$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");

// Проверяем наличие ошибок при подключении к базе данных
if ($connection->connect_error) {
    $notification = "<div class='notification error'>Ошибка соединения: " . $connection->connect_error . "</div>";
} else {
    // Формируем SQL-запрос для вставки нового пользователя в таблицу users
    $sql = "INSERT INTO users (username, password, role, full_name, position) VALUES ('$username', '$password', '$role', '$full_name', '$position')";

    if ($connection->query($sql) === TRUE) {
        $notification = "<div class='not success'>Пользователь успешно создан!</div>";
    } else {
        $notification = "<div class='not error'>Ошибка при создании пользователя: " . $connection->error . "</div>";
    }
}

$connection->close();

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание пользователя</title>
    <link rel="stylesheet" href="form.css">
    <script src="script.js"></script>
    <style>
        .not {
            padding: 10px 20px;
            margin: 10px auto;
            border-radius: 4px;
            width: fit-content;
            max-width: 300px;
            text-align: center;
        }
        .not.success {
            background-color: #00320b;
            color: white;
        }
        .not.error {
            background-color: #3f0400;
            color: white;
        }
    </style>
</head>
<body>
<?php echo $notification; ?>
    <div class="container">
      <div class="center">
      <h1>Создание пользователя</h1>
      <form action="create_user.php" method="post">
        <div class="column-container">
            <div class="column">
                <div class="txt_field">
                    <input type="text" id="username" name="username" required>
                    <span></span>
                    <label for="username">Логин</label>
                </div>
            </div>
            <div class="column">
                <div class="txt_field">
                    <input type="password" id="password" name="password" required>
                    <span></span>
                    <label for="password">Пароль</label>
                </div>
            </div>
        </div>
        <div class="column-container">
            <div class="column">
                <div class="txt_field">
                    <input type="text" id="full_name" name="full_name" required>
                    <span></span>
                    <label for="full_name">ФИО</label>
                </div>
            </div>
            <div class="column">
                <div class="txt_field">
                    <input type="text" id="position" name="position" required>
                    <span></span>
                    <label for="position">Должность</label>
                </div>
            </div>
        </div>
            <div class="select_field">
                <select id="role" name="role">
                    <option value="user">Пользователь</option>
                    <option value="admin">Администратор</option>
                </select>                  
                <span></span>
                <label for="role">Роль</label>
            </div>

              <input name="submit" type="submit" value="Создать пользователя">
              <br></br>
              <a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>
              <br></br>
          </form>
      </div>
    </div>
  </body>
</html>