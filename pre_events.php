<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pre_style.css">
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
<div class="content">

<?php include 'header.php'; ?>

<form action="pre_events.php" method="get" class="search">
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

<form action="pre_events.php" method="get" class="search">
    <label for="sort_by">Сортировать по дате начала: </label>
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

$upcoming_result = $connection->query("SELECT COUNT(*) AS count FROM events");
if ($upcoming_result === FALSE) {
    die("Ошибка при выполнении запроса: " . $connection->error);
}
$upcoming_count = $upcoming_result->fetch_assoc()['count'];

$past_result = $connection->query("SELECT COUNT(*) AS count FROM archive");
if ($past_result === FALSE) {
    die("Ошибка при выполнении запроса: " . $connection->error);
}
$past_count = $past_result->fetch_assoc()['count'];

$connection->close();
?>
<div class="line"></div>

<div class="nav-links">
<a href="pre_events.php" class="link active">Предстоящие мероприятия <?php echo $upcoming_count; ?></a>
<a href="pre_archive.php" class="link">Прошедшие мероприятия <?php echo $past_count; ?></a>
</div>

<?php
$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

$sql = "SELECT * FROM events WHERE end_date > CURDATE()"; 

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
        echo '<a href="event_details.php?id=' . $row['id'] . '" class="event-item">';
        if (!empty($row['image'])) {
            echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Изображение мероприятия">';
        } else {
            echo '<img src="uploads/cem.jpg" alt="Базовое изображение">';
        }
        echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
        echo '<p>' . htmlspecialchars(formatDateForDisplay($row['start_date'])) . ' ' . htmlspecialchars(formatTimeForDisplay($row['start_time'])) . ' - ' . htmlspecialchars(formatDateForDisplay($row['end_date'])) . ' ' . htmlspecialchars(formatTimeForDisplay($row['end_time'])) . '</p>';
        echo '<p>Дата публикации: ' . htmlspecialchars(formatDateForDisplay($row['publish_date'])) . '</p>';
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
        echo '</a>';
    }
} else {
    echo '<p>Нет предстоящих мероприятий.</p>';
}
echo '</div>';

$connection->close();
?>
    <?php include 'footer.php'; ?>
    </div>

<a href="login.php" id="goBackButton">Назад</a>
<button onclick="scrollToTop()" id="scrollBtn" title="Наверх">&#8679;</button>

</body>
</html>
