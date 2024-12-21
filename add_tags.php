<?php
$notification = "";

// Устанавливаем соединение с базой данных
$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tag_name = $_POST['tag_name'];
    $sql = "INSERT INTO tags (name) VALUES ('$tag_name')";
    if ($connection->query($sql) === TRUE) {
        $notification = "<div class='not success'>Тег успешно добавлен!</div>";
    } else {
        $notification = "<div class='not error'>Ошибка: " . $sql . "<br>" . $connection->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить теги</title>
    <link rel="stylesheet" href="form.css">
    <script src="script.js"></script>
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
            background-color: #00320b;
            color: white;
        }
        .not.error {
            background-color: #3f0400;
            color: white;
        }
    </style>
</head>
<body>
<?php echo $notification; ?>

    <div class="container">
        <div class="center">
            <h1>Добавить теги</h1>
            <form action="add_tags.php" method="post">
                <div class="txt_field">
                    <input type="text" id="tag_name" name="tag_name" required>
                    <span></span>
                    <label for="tag_name">Название тега</label>
                </div>
                <input name="submit" type="submit" value="Добавить тег">
                <br></br>
                <h2>Существующие теги:</h2>
                <div class="list">
                    <ul>
                        <?php
                        // SQL-запрос для получения всех тегов из таблицы tags
                            $sql = "SELECT * FROM tags";
                            $result = $connection->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<li>" . $row["name"] . "</li>";
                                }
                            } else {
                                echo "<li>Нет тегов</li>";
                            }
                        ?>
                    </ul>
                </div>
                <br><br>
                <a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>
                <br></br>
            </form>
        </div>
    </div>
    <?php $connection->close(); ?>
</body>
</html>
