<!-- archive_details.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель пользователя</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
</head>
<body>
<div class="content">

<?php include 'header.php'; ?>

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

<?php
// Установка соединения с базой данных
$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}
    // Функция для форматирования времени для отображения
    function formatTimeForDisplay($time) {
        $parts = explode(':', $time);
        return "{$parts[0]}:{$parts[1]}"; 
    }
    
    // Функция для форматирования даты для отображения
    function formatDateForDisplay($date) {
        $date_obj = date_create($date);
        return date_format($date_obj, 'd.m.Y'); 
    }

if(isset($_GET['id']) && !empty($_GET['id'])) {
    $event_id = $_GET['id'];

        // Запрос для получения данных о мероприятии из архива по его ID
    $sql = "SELECT * FROM archive WHERE id = $event_id";
    
    $result = $connection->query($sql);

        // Проверка наличия результатов запроса
    if ($result && $result->num_rows > 0) {
        $event_data = $result->fetch_assoc();
        
                // Вывод данных о мероприятии из архива
        echo '<div class="event-container">';
        echo '<h2>' . htmlspecialchars($event_data['title']) . '</h2>';
                // Отображение даты публикации, начала и окончания мероприятия
        echo '<p>Дата публикации: ' . htmlspecialchars(formatDateForDisplay($event_data['publish_date'])) . '</p>';
        echo '<p><strong>Дата начала мероприятия:</strong> ' . htmlspecialchars(formatDateForDisplay($event_data['start_date'])) . ' ' . htmlspecialchars(formatTimeForDisplay($event_data['start_time'])) . '</p>';
        echo '<p><strong>Дата окончания мероприятия:</strong> ' . htmlspecialchars(formatDateForDisplay($event_data['end_date'])) . ' ' . htmlspecialchars(formatTimeForDisplay($event_data['end_time'])) . '</p>';

                // Отображение текста мероприятия, если он есть
    if (!empty($event_data['text'])) {
        $escapedTextFromDatabase = $event_data['text'];
        $decodedTextForDisplay = htmlspecialchars_decode($escapedTextFromDatabase);
        echo '<p>' . $decodedTextForDisplay . '</p>';
    }
            // Проверка наличия изображений или видео для слайдера
    $hasContent = !empty($event_data['image']) || !empty($event_data['video']);

    if ($hasContent) {
        echo '<div class="slider">';
                    // Вывод изображений и видео для слайдера
        if (!empty($event_data['image'])) {
            echo '<div><img src="' . htmlspecialchars($event_data['image']) . '" alt="Изображение мероприятия"></div>';
        }
        if (!empty($event_data['video'])) {
            echo '<div><video controls><source src="' . htmlspecialchars($event_data['video']) . '" type="video/WEBM"></video></div>';
        }
        echo '</div>';
    }
            // Отображение аудио, если оно есть
    if (!empty($event_data['music'])) {
        echo '<audio controls><source src="' . htmlspecialchars($event_data['music']) . '" type="audio/MP3"></audio>' . '</p>';
    }
        // Получение и отображение тегов мероприятия
    $tags = ""; 
    $tags_sql = "SELECT tags.name FROM tags INNER JOIN archive_tags ON tags.id = archive_tags.tag_id WHERE archive_tags.event_id = " . $event_data['id'];
    $tags_result = $connection->query($tags_sql);
    if ($tags_result->num_rows > 0) {
        $tags .= "<strong>Теги:</strong> ";
        while ($tag_row = $tags_result->fetch_assoc()) {
            $tags .= htmlspecialchars($tag_row['name']) . ", ";
        }
        $tags = rtrim($tags, ", ");
        echo '<p>' . $tags . '</p>';
    }

            // Получение и отображение информации о сторонних участниках
    $participants_info = "";
    $participants_sql = "SELECT * FROM participants INNER JOIN archive_participants ON participants.id = archive_participants.participant_id WHERE archive_participants.event_id = " . $event_data['id'];
    $participants_result = $connection->query($participants_sql);
    if ($participants_result->num_rows > 0) {
        $participants_info .= "<strong>Сторонние участники:</strong><br>";
        while ($participant_row = $participants_result->fetch_assoc()) {
            $participants_info .= "Имя: " . htmlspecialchars($participant_row['full_name']) . "<br>";
            $participants_info .= "Организация: " . htmlspecialchars($participant_row['organization']) . "<br>";
            $participants_info .= "Должность: " . htmlspecialchars($participant_row['specialty']) . "<br>";
            $participants_info .= "<br>";
        }
        echo '<p>' . $participants_info . '</p>';
    }

    echo '</div>';
    } else {
        echo '<p>Мероприятие не найдено.</p>';
    }
} else {
    echo '<p>Некорректный запрос. Пожалуйста, вернитесь и попробуйте снова.</p>';
}
$connection->close();
?>
<br><br>
    <?php include 'footer.php'; ?>
    </div>

    <a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>
<button onclick="scrollToTop()" id="scrollBtn" title="Наверх">&#8679;</button>
</body>
</html>
