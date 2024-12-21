<!-- delete_event.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <title>Удаление мероприятий</title>
</head>
<body>
<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);

    $sqlDeleteFromEventParticipants = "DELETE FROM event_participants WHERE event_id = $event_id";
    $connection->query($sqlDeleteFromEventParticipants);

    $sqlDeleteFromEventTags = "DELETE FROM event_tags WHERE event_id = $event_id";
    $connection->query($sqlDeleteFromEventTags);

    $sqlDeleteFromAttendance = "DELETE FROM attendance WHERE event_id = $event_id";
    $connection->query($sqlDeleteFromAttendance);

    $sqlDeleteFromEvents = "DELETE FROM events WHERE id = $event_id";
    if ($connection->query($sqlDeleteFromEvents) === TRUE) {
        echo "Мероприятие успешно удалено";
    } else {
        echo "Ошибка при удалении мероприятия: " . $connection->error; 
    }    

    $connection->close();
    exit(); 
}
?>

</body>
</html>