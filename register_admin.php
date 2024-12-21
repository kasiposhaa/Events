<?php
$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");

// Проверяем наличие ошибок при подключении к базе данных
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

$username = "kasiposha";
$password = "i4w7t1K8m6";
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$role = "admin";
$full_name = "Косило Анна Александровна";
$position = "Администратор";

// Формируем SQL-запрос для вставки нового администратора в таблицу users
$sql = "INSERT INTO users (username, password, role, full_name, position) VALUES ('$username', '$passwordHash', '$role', '$full_name', '$position')";

// Выполняем SQL-запрос и проверяем успешность выполнения
if ($connection->query($sql) === TRUE) {
    echo "Администратор успешно зарегистрирован!";
} else {
    echo "Ошибка: ". $sql. "<br>". $connection->error;
}

$connection->close();
?>
