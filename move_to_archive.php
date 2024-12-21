<!-- move_to_archive.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <title>Перенос в архив</title>
</head>
<body>
<?php
    $connection = new mysqli("localhost", "kasiposha", "1234", "events_db");

    if ($connection->connect_error) {
        die("Ошибка соединения: " . $connection->connect_error);
    }

    $currentDate = date("Y-m-d");

    // Перемещение завершенных событий из таблицы events в таблицу archive
    $sqlMoveToArchive = "INSERT INTO archive (title, publish_date, start_date, start_time, end_date, end_time, text, image, music, video)
                        SELECT title, publish_date, start_date, start_time, end_date, end_time, text, image, music, video
                        FROM events
                        WHERE end_date < '$currentDate'";
    $connection->query($sqlMoveToArchive);

    $sqlMoveTagsToArchive = "INSERT INTO archive_tags (event_id, tag_id)
                            SELECT a.id, et.tag_id
                            FROM event_tags et
                            INNER JOIN events e ON et.event_id = e.id
                            INNER JOIN archive a ON a.title = e.title AND a.start_date = e.start_date
                            WHERE e.end_date < '$currentDate'";
    $connection->query($sqlMoveTagsToArchive);

    $sqlMoveParticipantsToArchive = "INSERT INTO archive_participants (event_id, participant_id)
                                     SELECT a.id, ep.participant_id
                                     FROM event_participants ep
                                     INNER JOIN events e ON ep.event_id = e.id
                                     INNER JOIN archive a ON a.title = e.title AND a.start_date = e.start_date
                                     WHERE e.end_date < '$currentDate'";
    $connection->query($sqlMoveParticipantsToArchive);

    $sqlMoveAttendanceToArchive = "INSERT INTO archive_attendance (event_id, user_id, presence)
                                   SELECT a.id, at.user_id, at.presence
                                   FROM attendance at
                                   INNER JOIN events e ON at.event_id = e.id
                                   INNER JOIN archive a ON a.title = e.title AND a.start_date = e.start_date
                                   WHERE e.end_date < '$currentDate'";
    $connection->query($sqlMoveAttendanceToArchive);

    // Удаление участников завершенных событий из таблицы event_participants
    $sqlDeleteFromEventParticipants = "DELETE FROM event_participants 
                                       WHERE event_id IN (SELECT id FROM events WHERE end_date < '$currentDate')";
    $connection->query($sqlDeleteFromEventParticipants);

    // Удаление тегов завершенных событий из таблицы event_tags
    $sqlDeleteFromEventTags = "DELETE FROM event_tags 
                               WHERE event_id IN (SELECT id FROM events WHERE end_date < '$currentDate')";
    $connection->query($sqlDeleteFromEventTags);

    // Удаление информации о посещаемости завершенных событий из таблицы attendance
    $sqlDeleteFromAttendance = "DELETE FROM attendance
                                WHERE event_id IN (SELECT id FROM events WHERE end_date < '$currentDate')";
    $connection->query($sqlDeleteFromAttendance);

    // Удаление завершенных событий из таблицы events
    $sqlDeleteFromEvents = "DELETE FROM events
                           WHERE end_date < '$currentDate'";
    $connection->query($sqlDeleteFromEvents);

    $connection->close();
?>
</body>
</html>