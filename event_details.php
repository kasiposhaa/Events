<!-- event_details.php -->

<?php
session_start();

// Проверяем, вошел ли пользователь в систему
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Проверяем, передан ли идентификатор мероприятия
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Ошибка: Идентификатор мероприятия не указан.";
    exit();
}

$notification = "";

// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");

    if ($connection->connect_error) {
        error_log("Ошибка соединения: " . $connection->connect_error);
        exit("Ошибка соединения: " . $connection->connect_error);
    }

    // Получаем идентификатор мероприятия и идентификатор пользователя из формы
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['id'];
    $presence = isset($_POST['presence']) ? 1 : 0;

    // Подготавливаем запрос для проверки посещения мероприятия пользователем
    $check_sql = "SELECT * FROM attendance WHERE user_id = ? AND event_id = ?";
    $stmt = $connection->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    // Если пользователь уже отмечал свое присутствие на мероприятии, удаляем запись из БД
    if ($check_result->num_rows > 0) {
        $delete_sql = "DELETE FROM attendance WHERE user_id = ? AND event_id = ?";
        $stmt = $connection->prepare($delete_sql);
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();

        // Выводим уведомление об успешном удалении записи
        $notification = "<div class='not success'>Вы успешно отменили свое присутствие на мероприятии!</div>";
    } else {
        // Если пользователь еще не отмечал свое присутствие на мероприятии, добавляем запись в БД
        $insert_sql = "INSERT INTO attendance (user_id, event_id, presence) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($insert_sql);
        $stmt->bind_param("iii", $user_id, $event_id, $presence);
        $stmt->execute();

        // Выводим уведомление об успешном добавлении записи
        $notification = "<div class='not success'>Вы успешно отметили свое присутствие на мероприятии!</div>";
    }

    $stmt->close();
    $connection->close();
}
?>

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
    <style>
        .not {
            padding: 10px 20px;
            margin: 10px auto;
            border-radius: 4px;
            width: fit-content;
            max-width: 300px;
            text-align: center;
        }
        .not.success {
            background-color: rgb(31, 45, 76);
            color: white;
        }
        .not.error {
            background-color: #3f0400;
            color: white;
        }
    </style>
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
function formatTimeForDisplay($time) {
    $parts = explode(':', $time);
    return "{$parts[0]}:{$parts[1]}";
}

function formatDateForDisplay($date) {
    $date_obj = date_create($date);
    return date_format($date_obj, 'd.m.Y'); 
}

$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");

if(isset($_GET['id']) && !empty($_GET['id'])) {
    $event_id = $_GET['id'];

    $sql = "SELECT * FROM events WHERE id = $event_id";
    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        $event_data = $result->fetch_assoc();
        echo '<div class="event-container">';
        echo '<h2>' . htmlspecialchars($event_data['title']) . '</h2>';
        echo '<p>Дата публикации: ' . htmlspecialchars(formatDateForDisplay($event_data['publish_date'])) . '</p>';
        echo '<p><strong>Дата начала мероприятия:</strong> ' . htmlspecialchars(formatDateForDisplay($event_data['start_date'])) . ' ' . htmlspecialchars(formatTimeForDisplay($event_data['start_time'])) . '</p>';
        echo '<p><strong>Дата окончания мероприятия:</strong> ' . htmlspecialchars(formatDateForDisplay($event_data['end_date'])) . ' ' . htmlspecialchars(formatTimeForDisplay($event_data['end_time'])) . '</p>';

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
            
    if (!empty($event_data['music'])) {
        echo '<audio controls><source src="' . htmlspecialchars($event_data['music']) . '" type="audio/MP3"></audio>' . '</p>';
    }


        $tags = "";
        $tags_sql = "SELECT tags.name FROM tags INNER JOIN event_tags ON tags.id = event_tags.tag_id WHERE event_tags.event_id = " . $event_data['id'];
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
        $participants_sql = "SELECT * FROM participants INNER JOIN event_participants ON participants.id = event_participants.participant_id WHERE event_participants.event_id = " . $event_data['id'];
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

$check_sql = "SELECT * FROM attendance WHERE user_id = ? AND event_id = ?";
$stmt = $connection->prepare($check_sql);
$stmt->bind_param("ii", $_SESSION['id'], $event_id);
$stmt->execute();
$check_result = $stmt->get_result();

$attendance_checked = false; 
if ($check_result->num_rows > 0) {
    $attendance_checked = true; 
}

echo $notification;

$current_time = time();
$start_time = strtotime($event_data['start_date'] . ' ' . $event_data['start_time']);

// Добавляем 10 минут к времени начала мероприятия
$start_time_with_buffer = $start_time + 600;

// Проверяем, началось ли мероприятие и прошло ли уже 10 минут после начала
if ($current_time >= $start_time_with_buffer) {
    // Если да, то отображаем форму для отметки присутствия
    echo '<form method="post">';
    echo '<input type="hidden" name="event_id" value="' . htmlspecialchars($event_data['id']) . '">';
    echo '<label for="presence">Присутствие:</label>';
    echo '<input type="checkbox" id="presence" name="presence" ' . ($attendance_checked ? 'checked' : '') . '>';
    echo '<button type="submit">Отметить</button>';
    echo '</form>';
} else {
    // Если нет, выводим сообщение о том, что отметка будет доступна после начала мероприятия
    echo "<p>Отметка присутствия будет доступна после начала мероприятия.</p>";
}

        echo '</div>';
    } else {
        echo '<script>alert("Мероприятие не найдено.");</script>';
    }
} else {
    echo '<script>alert("Некорректный запрос. Пожалуйста, вернитесь и попробуйте снова.");</script>';
}
$connection->close();
?>

<?php include 'footer.php'; ?>
</div>

<a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>
<button onclick="scrollToTop()" id="scrollBtn" title="Наверх">&#8679;</button>
</body>
</html>
