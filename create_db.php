<?php
$connection = new mysqli("localhost", "kasiposha", "1234", "");

if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

// Создание базы данных
$sql_create_db = "CREATE DATABASE IF NOT EXISTS events_db";
if ($connection->query($sql_create_db) === FALSE) {
    die("Ошибка создания базы данных: " . $connection->error);
}

// Выбор базы данных
$connection->select_db("events_db");

// Создание таблицы для тегов
$sql_create_tags_table = "CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
)";
if ($connection->query($sql_create_tags_table) === FALSE) {
    die("Ошибка создания таблицы тегов: " . $connection->error);
}

// Создание таблицы для сторонних участников
$sql_create_participants_table = "CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(50) NOT NULL,
    organization VARCHAR(60) NOT NULL,
    specialty VARCHAR(50) NOT NULL,
    contact_info VARCHAR(50) NOT NULL
)";
if ($connection->query($sql_create_participants_table) === FALSE) {
    die("Ошибка создания таблицы сторонних участников: " . $connection->error);
}

// Создание таблицы для мероприятий
$sql_create_events_table = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(60) NOT NULL,
    publish_date DATE DEFAULT CURRENT_DATE,
    start_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_date DATE NOT NULL,
    end_time TIME NOT NULL,
    text TEXT(65),
    image VARCHAR(255),
    music VARCHAR(255),
    video VARCHAR(255)
)";
if ($connection->query($sql_create_events_table) === FALSE) {
    die("Ошибка создания таблицы мероприятий: " . $connection->error);
}

// Таблица для связей между мероприятиями и тегами
$sql_create_events_table = "CREATE TABLE IF NOT EXISTS event_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    tag_id INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id)
)";
if ($connection->query($sql_create_events_table) === FALSE) {
    die("Ошибка создания таблицы связей между мероприятиями и тегами: " . $connection->error);
}

// Таблица для связей между мероприятиями и сторонними участниками
$sql_create_events_table = "CREATE TABLE IF NOT EXISTS event_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    participant_id INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (participant_id) REFERENCES participants(id)
)";
if ($connection->query($sql_create_events_table) === FALSE) {
    die("Ошибка создания таблицы связей между мероприятиями и сторонними участниками: " . $connection->error);
}

// Создание таблицы для пользователей
$sql_create_users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL,
    full_name VARCHAR(50) NOT NULL, 
    position VARCHAR(50) NOT NULL
)";
if ($connection->query($sql_create_users_table) === FALSE) {
    die("Ошибка создания таблицы пользователей: " . $connection->error);
}

// Создание таблицы для отметок о присутствии
$sql_create_users_table = "CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    presence BOOLEAN NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
if ($connection->query($sql_create_users_table) === FALSE) {
    die("Ошибка создания таблицы отметок о присутствии: " . $connection->error);
}
// Создание таблицы архива
$sqlCreateArchiveTable = "CREATE TABLE IF NOT EXISTS archive (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(60) NOT NULL,
    publish_date DATE DEFAULT CURRENT_DATE,
    start_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_date DATE NOT NULL,
    end_time TIME NOT NULL,
    text TEXT(65),
    image VARCHAR(255),
    music VARCHAR(255),
    video VARCHAR(255)
)";

if ($connection->query($sqlCreateArchiveTable) === FALSE) {
    echo "Ошибка при создании таблицы archive: " . $connection->error;
}

// Таблица для связей между прошедшими мероприятиями и тегами
$sql_create_events_table = "CREATE TABLE IF NOT EXISTS archive_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    tag_id INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES archive(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id)
)";
if ($connection->query($sql_create_events_table) === FALSE) {
    die("Ошибка создания таблицы связей между мероприятиями и тегами: " . $connection->error);
}

// Таблица для связей между прошедшими мероприятиями и сторонними участниками
$sql_create_events_table = "CREATE TABLE IF NOT EXISTS archive_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    participant_id INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES archive(id),
    FOREIGN KEY (participant_id) REFERENCES participants(id)
)";
if ($connection->query($sql_create_events_table) === FALSE) {
    die("Ошибка создания таблицы связей между мероприятиями и сторонними участниками: " . $connection->error);
}

// Создание таблицы для отметок о присутствии на прошедших мероприятиях
$sql_create_users_table = "CREATE TABLE IF NOT EXISTS archive_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    presence BOOLEAN NOT NULL,
    FOREIGN KEY (event_id) REFERENCES archive(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
if ($connection->query($sql_create_users_table) === FALSE) {
    die("Ошибка создания таблицы отметок о присутствии: " . $connection->error);
}

$connection->close();
?>