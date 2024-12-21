<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр присутствия пользователей</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>

    <h1>Просмотр присутствия пользователей</h1>

    <?php
        // Устанавливаем соединение с базой данных
    $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
    if ($connection->connect_error) {
        die("Ошибка соединения: " . $connection->connect_error);
    }

        // Запрос для получения данных о присутствии пользователей на мероприятиях
    $sql = "SELECT events.title AS event_title, GROUP_CONCAT(users.username SEPARATOR ', ') AS attendees
            FROM events
            LEFT JOIN attendance ON events.id = attendance.event_id
            LEFT JOIN users ON attendance.user_id = users.id
            WHERE attendance.presence = 1
            GROUP BY events.id";

    $result = $connection->query($sql);

        // Проверка наличия результатов запроса
    if ($result->num_rows > 0) {
        echo '<table class="table_col">';
        echo '<colgroup>
        <col style="    background: rgba(255, 255, 255, 0.1);">
    </colgroup>';
        echo '<tr><th>Название мероприятия</th><th>Присутствующие</th></tr>';
                // Вывод данных о присутствующих на мероприятиях
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['event_title']) . '</td>';
            echo '<td>' . htmlspecialchars($row['attendees']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
                // Сообщение, если нет данных о присутствии пользователей
        echo '<p>Нет данных о присутствии пользователей.</p>';
    }

        // Закрываем соединение с базой данных
    $connection->close();
    ?>

    <br><br>
        <!-- Ссылка для возврата на предыдущую страницу -->
    <a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>

</body>
</html>
