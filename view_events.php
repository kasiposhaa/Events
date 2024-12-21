<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">

    <title>Список мероприятий</title>
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
<h1>Список мероприятий</h1>

<form action="view_events.php" method="get" class="search">
    <input type="text" id="title" name="title" placeholder="Введите название мероприятия">
    <input type="date" id="date" name="date">

    <select id="tag" name="tag">
        <option value="">Выберите тег</option>
        <?php
        $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
        if ($connection->connect_error) {
            die("Ошибка соединения: " . $connection->connect_error);
        }
        $sqlTags = "SELECT * FROM tags";
        $resultTags = $connection->query($sqlTags);
        if ($resultTags->num_rows > 0) {
            while ($rowTag = $resultTags->fetch_assoc()) {
                echo "<option value='" . $rowTag['id'] . "'>" . $rowTag['name'] . "</option>";
            }
        }
        $connection->close();
        ?>
    </select>

    <button type="submit">Искать</button>
</form>

<form action="view_events.php" method="get" class="search">
    <label for="sort_by">Сортировать по дате начала:</label>
    <select id="sort_by" name="order_by">
        <option value="start_date">По возрастанию</option>
        <option value="start_date_desc">По убыванию</option>
    </select>
    <button type="submit">Применить</button>
</form>

<?php
$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

$sql = "SELECT * FROM events WHERE end_date >= CURDATE()"; 

$conditions = array();
if (!empty($_GET['title'])) {
    $conditions[] = "title LIKE '%" . $_GET['title'] . "%'";
}

if (!empty($_GET['date'])) {
    $conditions[] = "(start_date <= '" . $_GET['date'] . "' AND end_date >= '" . $_GET['date'] . "')";
}

if (!empty($_GET['tag'])) {
    $sql_tags = "SELECT event_id FROM event_tags WHERE tag_id = '" . $_GET['tag'] . "'";
    $result_tags = $connection->query($sql_tags);
    if ($result_tags->num_rows > 0) {
        $tag_conditions = array();
        while ($row_tag = $result_tags->fetch_assoc()) {
            $tag_conditions[] = "id = '" . $row_tag['event_id'] . "'";
        }
        $conditions[] = "(" . implode(" OR ", $tag_conditions) . ")";
    }
}

if (!empty($_GET['order_by'])) {
    if ($_GET['order_by'] == 'start_date') {
        $sql .= " ORDER BY start_date ASC";
    } elseif ($_GET['order_by'] == 'start_date_desc') {
        $sql .= " ORDER BY start_date DESC";
    }
}

if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

function formatTimeForDisplay($time) {
    $parts = explode(':', $time);
    return "{$parts[0]}:{$parts[1]}"; 
}

function formatDateForDisplay($date) {
    $date_obj = date_create($date);
    return date_format($date_obj, 'd.m.Y'); 
}

$result = $connection->query($sql);

echo '<div class="event-container">';
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
        
        $tags = ""; 
        $tags_sql = "SELECT tags.name FROM tags INNER JOIN event_tags ON tags.id = event_tags.tag_id WHERE event_tags.event_id = " . $row['id'];
        $tags_result = $connection->query($tags_sql);
        if ($tags_result->num_rows > 0) {
            $tags .= "<strong>Теги:</strong> ";
            while ($tag_row = $tags_result->fetch_assoc()) {
                $tags .= htmlspecialchars($tag_row['name']) . ", ";
            }
            $tags = rtrim($tags, ", ");
            echo '<p>' . $tags . '</p>';
        }

        $participants_info = ""; 
        $participants_sql = "SELECT * FROM participants INNER JOIN event_participants ON participants.id = event_participants.participant_id WHERE event_participants.event_id = " . $row['id'];
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


echo '<a href="edit_event.php?id=' . $row['id'] . '">Изменить</a>';

echo '<a href="#" onclick="deleteEvent(' . $row['id'] . ')">Удалить</a>';
    }
} else {
    echo '<p>Нет предстоящих мероприятий.</p>';
}
echo '</div>';

$connection->close();
?>

<a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>

<button onclick="scrollToTop()" id="scrollBtn" title="Наверх">&#8679;</button>

</body>
</html>
