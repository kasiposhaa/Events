<?php
$notification = "";

$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $organization = $_POST['organization'];
    $specialty = $_POST['specialty'];
    $contact_info = $_POST['contact_info'];

    // Проверяем, существует ли участник с такими же данными
    $check_sql = "SELECT * FROM participants WHERE full_name = '$full_name' AND organization = '$organization' AND contact_info = '$contact_info'";
    $check_result = $connection->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $notification = "<div class='not error'>Участник с такими данными уже существует.</div>";
    } else {
            // Вставляем нового участника в базу данных
        $sql = "INSERT INTO participants (full_name, organization, specialty, contact_info) VALUES ('$full_name', '$organization', '$specialty', '$contact_info')";
        if ($connection->query($sql) === TRUE) {
            $notification = "<div class='not success'>Сторонний участник успешно добавлен!</div>";
        } else {
            $notification = "<div class='not error'>Ошибка: " . $sql . "<br>" . $connection->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить сторонних участников</title>
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
<body>            <?php echo $notification; ?>

    <div class="container">
        <div class="center">
            <h1>Добавить сторонних участников</h1>
            <form action="add_participants.php" method="post">
                <div class="column-container">
                    <div class="column">
                        <div class="txt_field">
                            <input type="text" id="full_name" name="full_name" required>
                            <span></span>
                            <label for="full_name">ФИО</label>
                        </div>
                    </div>
                    <div class="column">
                        <div class="txt_field">
                            <input type="text" id="organization" name="organization" required>
                            <span></span>
                            <label for="organization">Организация</label>
                        </div>
                    </div>
                </div>
                <div class="column-container">
                    <div class="column">
                        <div class="txt_field">
                            <input type="text" id="specialty" name="specialty" required>
                            <span></span>
                            <label for="specialty">Должность</label>
                        </div>
                    </div>
                    <div class="column">
                        <div class="txt_field">
                            <input type="email" id="contact_info" name="contact_info" required>
                            <span></span>
                            <label for="contact_info">Почта</label>
                        </div>
                    </div>
                </div>
                <input name="submit" type="submit" value="Добавить участника">
                <h2>Существующие участники:</h2>
                <div class="list">
                    <ul>
                        <?php
                        // Выводим список существующих участников
                            $sql = "SELECT * FROM participants";
                            $result = $connection->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<li>" . $row["full_name"] . " - " . $row["organization"] . "</li>";
                                }
                            } else {
                                echo "<li>Нет участников</li>";
                            }
                            $connection->close();
                        ?>
                    </ul>
                </div>
                <br>
                <a href="#" onclick="window.history.back(); return false;" id="goBackButton">Назад</a>
                <br>
            </form>
        </div>
    </div>
</body>
</html>
