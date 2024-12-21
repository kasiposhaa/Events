<!-- admin_panel.php -->

<?php
session_start();

// Проверяем, авторизован ли пользователь и имеет ли он роль администратора
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель Администратора</title>
    <link rel="stylesheet" href="admpnlstyle.css">
    <script src="script.js"></script>
</head>
<body>
<h1>Панель Администратора</h1>

<p>Добро пожаловать, <?php echo $_SESSION['username']; ?>!</p>

<div class="container">
    <!-- Ссылка для создания нового мероприятия -->
    <a href="create_event.php" class="btn">
        <img src="icons/create_event_icon.png" alt="Create Event">
        Создать мероприятие
    </a>
        <!-- Ссылка для создания нового пользователя -->
    <a href="create_user.php" class="btn">
        <img src="icons/create_user_icon.png" alt="Create User">
        Создать пользователя
    </a>
        <!-- Ссылка для просмотра мероприятий -->
    <a href="view_events.php" class="btn">
        <img src="icons/view_events_icon.png" alt="View Events">
        Мероприятия
    </a>
            <!-- Ссылка для просмотра присутсвия на мероприятиях -->
    <a href="admin_view_attendance.php" class="btn">
        <img src="icons/admin_view_attendance_icon.png" alt="View Attendance">
        Просмотр присутствия пользователей
    </a>
            <!-- Ссылка для проверки мероприятий на актуальность-->
    <a href="#" onclick="moveToArchive(); return false;" class="btn">
        <img src="icons/move_to_archive_icon.png" alt="Move to Archive">
        Проверить мероприятия на актуальность
    </a>
            <!-- Ссылка для просмотра прошедших мероприятий -->
    <a href="archive.php" class="btn">
        <img src="icons/archive_icon.png" alt="Archive">
        Архив
    </a>
    <a href="logout.php" class="btn">
        <img src="icons/out.png" alt="Exit">
        Выйти
    </a>
    <a href="export_to_excel.php" class="btn">
        <img src="icons/export_to_excel_icon.png" alt="Export to Excel">
        Скачать данные из архива в формате Excel
    </a>

</div>
</body>
</html>