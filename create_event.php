<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$notification = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $end_date = $_POST['end_date'];
    $end_time = $_POST['end_time'];
    $text = $_POST['text'];

    $rawTextFromEditor = $_POST['text'];
    $escapedTextForDatabase = htmlspecialchars($rawTextFromEditor);

    $targetDirectory = "uploads/";

    $imageFile = (!empty($_FILES["image"]["name"])) ? $targetDirectory . basename($_FILES["image"]["name"]) : "";
    $musicFile = (!empty($_FILES["music"]["name"])) ? $targetDirectory . basename($_FILES["music"]["name"]) : "";
    $videoFile = (!empty($_FILES["video"]["name"])) ? $targetDirectory . basename($_FILES["video"]["name"]) : "";

    if (!empty($_FILES["image"]["name"])) {
        move_uploaded_file($_FILES["image"]["tmp_name"], $imageFile);
    }

    if (!empty($_FILES["music"]["name"])) {
        move_uploaded_file($_FILES["music"]["tmp_name"], $musicFile);
    }

    if (!empty($_FILES["video"]["name"])) {
        move_uploaded_file($_FILES["video"]["tmp_name"], $videoFile);
    }

    $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");

    if ($connection->connect_error) {
        $notification = "<div class='notification error'>Ошибка соединения: " . $connection->connect_error . "</div>";
    } else {
        $start_datetime = strtotime("$start_date $start_time");
        $current_datetime = strtotime("now");

        if ($start_datetime < $current_datetime + 600) {
            $notification = "<div class='notification error'>Нельзя создать мероприятие задним числом или менее чем за 10 минут до начала!</div>";
        } else {

            $sql = "INSERT INTO events (title, publish_date, start_date, start_time, end_date, end_time, text, image, music, video) VALUES ('$title', CURRENT_DATE, '$start_date', '$start_time', '$end_date', '$end_time', '$escapedTextForDatabase', '$imageFile', '$musicFile', '$videoFile')";

            if ($connection->query($sql) === TRUE) {
            $event_id = $connection->insert_id;

                if (!empty($_POST['tags'])) {
                    $tags = $_POST['tags'];
                    foreach ($tags as $tag_id) {
                        $sql = "INSERT INTO event_tags (event_id, tag_id) VALUES ('$event_id', '$tag_id')";
                        $connection->query($sql);
                    }
                }

                if (!empty($_POST['participants'])) {
                    $participants = $_POST['participants'];
                    foreach ($participants as $participant_id) {
                        $sql = "INSERT INTO event_participants (event_id, participant_id) VALUES ('$event_id', '$participant_id')";
                        $connection->query($sql);
                    }
                }

                $notification = "<div class='notification success'>Мероприятие успешно создано!</div>";
            } else {
                $notification = "<div class='notification error'>Ошибка: " . $sql . "<br>" . $connection->error . "</div>";
            }

            $connection->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание мероприятия</title>
    <link rel="stylesheet" href="create_event.css">
    <!-- Подключение стилей MediumEditor -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/medium-editor@5.23.3/dist/css/medium-editor.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/medium-editor@5.23.3/dist/css/themes/default.min.css">

    <!-- Подключение скриптов MediumEditor и jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/medium-editor@5.23.3/dist/js/medium-editor.min.js"></script>

    <!-- Инициализация MediumEditor -->
    <script>
        $(document).ready(function () {
            var editor = new MediumEditor('.editable');
        });
    </script>
    <style>
        .notification {
            padding: 10px 20px;
            margin: 10px auto;
            border-radius: 4px;
            width: fit-content;
            max-width: 300px;
            text-align: center;
        }
        .notification.success {
            background-color: #00320b;
            color: white;
        }
        .notification.error {
            background-color: #3f0400;
            color: white;
        }
    </style>
</head>
<body>
<?php echo $notification; ?>

    <div class="container">
        <div class="left-panel">
            <a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>
        </div>
        <div class="right-panel">
        <h1>Создание мероприятия</h1>
            <form action="create_event.php" method="post" enctype="multipart/form-data">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="title" required>
                <div class="column-container">
                    <div class="column">
                        <label for="start_date">Дата начала:</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    <div class="column">
                        <label for="start_time">Время начала:</label>
                        <br>
                        <input type="time" id="start_time" name="start_time" required>
                    </div>
                    <div class="column">
                        <label for="end_date">Дата окончания:</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                    <div class="column">
                        <label for="end_time">Время окончания:</label>
                        <input type="time" id="end_time" name="end_time" required>
                    </div>
                </div>
                <label for="text">Текст:</label>
                <textarea id="text" name="text" rows="4" required class="editable" contenteditable="true"></textarea>
                <div class="column-container">
                    <div class="column">
                        <label for="image">Изображение:</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    <div class="column">
                        <label for="music">Музыка:</label>
                        <input type="file" id="music" name="music" accept="audio/*">
                    </div>
                    <div class="column">
                        <label for="video">Видео:</label>
                        <input type="file" id="video" name="video" accept="video/*">
                    </div>
                </div>
                <div class="column-container">
                    <div class="column">
                        <label for="tags">Теги:</label><br>
                        <select id="tags" name="tags[]" multiple>
                            <?php
                                $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
                                if ($connection->connect_error) {
                                    die("Ошибка соединения: " . $connection->connect_error);
                                }

                                $sql = "SELECT * FROM tags";
                                $result = $connection->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                                    }
                                }
                                $connection->close();
                            ?>
                        </select>
                        <div>    <a href="add_tags.php">Добавить теги</a>    </div>
                    </div>
                    <div class="column">
                        <label for="participants">Сторонние участники:</label><br>
                        <select id="participants" name="participants[]" multiple>
                            <?php
                                $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
                                if ($connection->connect_error) {
                                    die("Ошибка соединения: " . $connection->connect_error);
                                }

                                $sql = "SELECT * FROM participants";
                                $result = $connection->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['full_name'] . "</option>";
                                    }
                                }
                                $connection->close();
                            ?>
                        </select>
                        <div>        <a href="add_participants.php">Добавить сторонних участников</a>   </div>
                    </div>
                </div>
                <button type="submit">Создать мероприятие</button>
            </form>
        </div>
    </div>
</body>
</html>