<?php
session_start();

// Установка соединения с базой данных
$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");

// Проверка соединения на наличие ошибок
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

// Запрос к базе данных для получения данных о мероприятиях из архива, отсортированных по дате начала в обратном порядке
$sql = "SELECT * FROM archive ORDER BY start_date DESC";
$result = $connection->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Архив мероприятий</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">

</head>
<body>
<script>
    $(document).ready(function(){
    $('.slider').slick({
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: true,
        arrows: true,
        adaptiveHeight: true,
    });
});
</script>

    <h1>Архив прошедших мероприятий</h1>

    <div class="event-container">
        <?php
         if (isset($_SESSION['role'])) {
            $role = $_SESSION['role'];
            
        }

function formatTimeForDisplay($time) {
    $parts = explode(':', $time);
    return "{$parts[0]}:{$parts[1]}"; 
}

function formatDateForDisplay($date) {
    $date_obj = date_create($date);
    return date_format($date_obj, 'd.m.Y'); 
}

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="event-item">';
                echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                echo '<p>Дата публикации: ' . htmlspecialchars(formatDateForDisplay($row['publish_date'])) . '</p>';
        echo '<p><strong>Дата начала мероприятия:</strong> ' . htmlspecialchars(formatDateForDisplay($row['start_date'])) . ' ' . htmlspecialchars(formatTimeForDisplay($row['start_time'])) . '</p>';
        echo '<p><strong>Дата окончания мероприятия:</strong> ' . htmlspecialchars(formatDateForDisplay($row['end_date'])) . ' ' . htmlspecialchars(formatTimeForDisplay($row['end_time'])) . '</p>';

                if (!empty($row['text'])) {
                    $escapedTextFromDatabase = $row['text'];
                    $decodedTextForDisplay = htmlspecialchars_decode($escapedTextFromDatabase);
                    echo '<p>' . $decodedTextForDisplay . '</p>';
                }
        // Проверка наличия мультимедийного контента
        $hasContent = !empty($row['image']) || !empty($row['video']);

        if ($hasContent) {
            echo '<div class="slider">';
            if (!empty($row['image'])) {
                echo '<div><img src="' . htmlspecialchars($row['image']) . '" alt="Изображение мероприятия"></div>';
            }
            if (!empty($row['video'])) {
                echo '<div><video controls><source src="' . htmlspecialchars($row['video']) . '" type="video/WEBM"></video></div>';
            }
            echo '</div>';
        }
                if (!empty($row['music'])) {
                    echo '<audio controls><source src="' . htmlspecialchars($row['music']) . '" type="audio/MP3"></audio>' . '</p>';
                }

         // Получение и вывод тегов мероприятия
        $tags = ""; 
        $tags_sql = "SELECT tags.name FROM tags INNER JOIN archive_tags ON tags.id = archive_tags.tag_id WHERE archive_tags.event_id = " . $row['id'];
        $tags_result = $connection->query($tags_sql);
        if ($tags_result->num_rows > 0) {
            $tags .= "<strong>Теги:</strong> ";
            while ($tag_row = $tags_result->fetch_assoc()) {
                $tags .= htmlspecialchars($tag_row['name']) . ", ";
            }
            $tags = rtrim($tags, ", ");
            echo '<p>' . $tags . '</p>';
        }

        // Получение и вывод информации о сторонних участниках
        $participants_info = ""; 
        $participants_sql = "SELECT * FROM participants INNER JOIN archive_participants ON participants.id = archive_participants.participant_id WHERE archive_participants.event_id = " . $row['id'];
        $participants_result = $connection->query($participants_sql);
        if ($participants_result->num_rows > 0) {
            $participants_info .= "<strong>Обязательные участники:</strong><br>";
            while ($participant_row = $participants_result->fetch_assoc()) {
                $participants_info .= "Имя: " . htmlspecialchars($participant_row['full_name']) . "<br>";
                $participants_info .= "Организация: " . htmlspecialchars($participant_row['organization']) . "<br>";
                $participants_info .= "Специальность: " . htmlspecialchars($participant_row['specialty']) . "<br>";
                $participants_info .= "<br>";
            }
            echo '<p>' . $participants_info . '</p>';
        }
                echo '</div>';
            }
        } else {
            echo '<p>Нет прошедших мероприятий в архиве.</p>';
        }

        $connection->close();
        ?>

<br><br>

<a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>
<button onclick="scrollToTop()" id="scrollBtn" title="Наверх">&#8679;</button>

</body>
</html>
