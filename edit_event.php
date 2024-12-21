<?php
session_start();
    $image = null;
    $music = null;
    $video = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {

    $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
    if ($connection->connect_error) {
        die("Ошибка соединения: " . $connection->connect_error);
    }

    $event_id = $_GET['id'];

    $sql = "SELECT e.title, e.start_date, e.start_time, e.end_date, e.end_time, e.text, e.image, e.music, e.video FROM events e 
    LEFT JOIN event_tags t ON e.id = t.event_id
    LEFT JOIN event_participants p ON e.id = p.event_id
    WHERE e.id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event_data = $result->fetch_assoc();

        $title = $event_data['title'];
        $start_date = $event_data['start_date'];
        $start_time = $event_data['start_time'];
        $end_date = $event_data['end_date'];
        $end_time = $event_data['end_time'];
        $text = $event_data['text'];
        $rawTextFromEditor = $event_data['text']; 
        $escapedTextForDatabase = htmlspecialchars($rawTextFromEditor);
        $image = $event_data['image'];
        $music = $event_data['music'];
        $video = $event_data['video'];
        
        if (isset($event_data['tags'])) {
            $tags = $event_data['tags'];
        }
        if (isset($event_data['participants'])) {
            $participants = $event_data['participants'];
        }
    } else {
        echo "Мероприятие не найдено.";
        exit();
    }
} 
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $event_id = $_POST['id'];
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $end_date = $_POST['end_date'];
    $end_time = $_POST['end_time'];
    $text = $_POST['text'];
    $rawTextFromEditor = $_POST['text']; 
    $escapedTextForDatabase = htmlspecialchars($rawTextFromEditor);

    $targetDirectory = "uploads/";

    $imageFile = (!empty($_FILES["image"]["name"])) ? $targetDirectory . basename($_FILES["image"]["name"]) : $_POST['current_image'];

    if (!empty($_FILES["image"]["name"])) {
         $imageFile = $targetDirectory . basename($_FILES["image"]["name"]);
         move_uploaded_file($_FILES["image"]["tmp_name"], $imageFile);
    } elseif ($_POST['current_image'] !== null) {
        $imageFile = $_POST['current_image'];
    } else {
         $imageFile = null;
    }

    $musicFile = (!empty($_FILES["music"]["name"])) ? $targetDirectory . basename($_FILES["music"]["name"]) : $_POST['current_music'];

    if (!empty($_FILES["music"]["name"])) {
        $musicFile = $targetDirectory . basename($_FILES["music"]["name"]);
        move_uploaded_file($_FILES["music"]["tmp_name"], $musicFile);
    } elseif ($_POST['current_music'] !== null) {
        $musicFile = $_POST['current_music'];
    } else {
        $musicFile = null;
    }

    $videoFile = (!empty($_FILES["video"]["name"])) ? $targetDirectory . basename($_FILES["video"]["name"]) : $_POST['current_video'];

    if (!empty($_FILES["video"]["name"])) {
        $videoFile = $targetDirectory . basename($_FILES["video"]["name"]);
        move_uploaded_file($_FILES["video"]["tmp_name"], $videoFile);
    } elseif ($_POST['current_video'] !== null) {
        $videoFile = $_POST['current_video'];
    } else {
        $videoFile = null;
    }

    $tags = isset($_POST['tags']) ? $_POST['tags'] : array();
    $participants = isset($_POST['participants']) ? $_POST['participants'] : array();

    $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
    if ($connection->connect_error) {
        die("Ошибка соединения: " . $connection->connect_error);
    }

    $sql = "UPDATE events SET title=?, start_date=?, start_time=?, end_date=?, end_time=?, text=?, image=?, music=?, video=? WHERE id=?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssssssssi", $title, $start_date, $start_time, $end_date, $end_time, $text, $imageFile, $musicFile, $videoFile, $event_id);
    $stmt->execute();

    if (!empty($tags)) {
        $sql = "DELETE FROM event_tags WHERE event_id=?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();

        $sql = "INSERT INTO event_tags (event_id, tag_id) VALUES (?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ii", $event_id, $tag_id);
        foreach ($tags as $tag_id) {
            $stmt->execute();
        }
    }

    if (!empty($participants)) {
        $sql = "DELETE FROM event_participants WHERE event_id=?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();

        $sql = "INSERT INTO event_participants (event_id, participant_id) VALUES (?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ii", $event_id, $participant_id);
        foreach ($participants as $participant_id) {
            $stmt->execute();
        }
    }

    if ($stmt->affected_rows > 0) {
        header("Location: view_events.php"); 
        exit();
    }

    $stmt->close();
    $connection->close();
} else {
    echo "Ошибка: ID мероприятия не передан.";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="create_event.css">
    <script src="script.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/medium-editor@5.23.3/dist/css/medium-editor.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/medium-editor@5.23.3/dist/css/themes/default.min.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/medium-editor@5.23.3/dist/js/medium-editor.min.js"></script>

    <script>
        $(document).ready(function () {
            var editor = new MediumEditor('.editable');
        });
    </script>
    <title>Изменение мероприятия</title>
</head>
<body>
<div class="container">
        <div class="left-panel">
            <a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>
        </div>
        <div class="right-panel">
            <h1>Изменение мероприятия</h1>
    <form action="edit_event.php?id=<?php echo $event_id; ?>" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
        <input type="hidden" name="id" value="<?php echo $event_id; ?>">
        <input type="hidden" name="current_image" value="<?php echo $image; ?>">
        <input type="hidden" name="current_music" value="<?php echo $music; ?>">
        <input type="hidden" name="current_video" value="<?php echo $video; ?>">

        <label for="title">Заголовок:</label>
        <input type="text" id="title" name="title" value="<?php echo $title; ?>">
        <div class="column-container">
                    <div class="column">
        <label for="start_date">Дата начала:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
        </div>
                    <div class="column">
        <label for="start_time">Время начала:</label>
        <br>
        <input type="time" id="start_time" name="start_time" value="<?php echo $start_time; ?>">
        </div>
                    <div class="column">
        <label for="end_date">Дата окончания:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
        </div>
                    <div class="column">
        <label for="end_time">Время окончания:</label>
        <input type="time" id="end_time" name="end_time" value="<?php echo $end_time; ?>">
        </div>
                </div>    
        <label for="text">Текст:</label>
        <textarea id="text" name="text" rows="4" required class="editable" contenteditable="true"><?php echo $text; ?></textarea>
        <div class="column-container">
                    <div class="column">
        <label for="image">Изображение:</label>
        <input type="file" id="image" name="image" onchange="validateFileField('image')"><?php echo isset($image) ? $image : ''; ?>
        </div>
                    <div class="column">
        <label for="music">Музыка:</label>
        <input type="file" id="music" name="music" onchange="validateFileField('music')"><?php echo isset($music) ? $music : ''; ?>
        </div>
                    <div class="column">
        <label for="video">Видео:</label>
        <input type="file" id="video" name="video" onchange="validateFileField('video')"><?php echo isset($video) ? $video : ''; ?>
        
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
            ?>
        </select>
        <div><a href="add_tags.php">Добавить теги</a></div>
        </div>
                    <div class="column">
<button type="button" onclick="clearFileField('<?php echo $image?>')">Удалить файлы</button>

<script>
    function clearFileField(currentValue) {
        currentValue = null;
    }
</script>
        </div>
                    <div class="column">
        <label for="participants">Сторонние участники:</label>
        <select id="participants" name="participants[]" multiple>
            <?php
            $sql = "SELECT * FROM participants";
            $result = $connection->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['full_name'] . "</option>";
                }
            }
            ?>
        </select>
        <div><a href="add_participants.php">Добавить сторонних участников</a></div>
        </div>
                </div>
        <button type="submit">Изменить</button>
    </form>
    </div>
    </div>
</body>
</html>
