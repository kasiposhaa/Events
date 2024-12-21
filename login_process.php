<?php
session_start();

$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");

// Проверяем наличие ошибок при подключении к базе данных
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Выполняем SQL-запрос для поиска пользователя по имени
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $connection->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $passwordHash = $row['password'];
        
        // Проверяем совпадение хеша пароля
        if (password_verify($password, $passwordHash)) {
            // Устанавливаем параметры сессии после успешной авторизации
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $row['role'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['position'] = $row['position'];
            
            if ($_SESSION['role'] == 'admin') {
                $response = ["status" => "success", "redirect" => "admin_panel.php"];
            } else {
                $response = ["status" => "success", "redirect" => "pre_events.php"];
            }
        } else {
            // Ошибка: неверный пароль
            $response = ["status" => "error", "message" => "Неверный пароль"];
        }
    } else {
        // Ошибка: пользователь не найден
        $response = ["status" => "error", "message" => "Пользователь не найден"];
    }
}

$connection->close();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
