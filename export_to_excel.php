<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Подключение к базе данных
$connection = new mysqli("localhost", "kasiposha", "1234", "events_db");
if ($connection->connect_error) {
    die("Ошибка соединения: " . $connection->connect_error);
}

// Запрос к базе данных для получения данных из таблицы архив
$sql = "SELECT a.*, GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS tags, GROUP_CONCAT(DISTINCT p.full_name SEPARATOR ', ') AS participants
        FROM archive a
        LEFT JOIN archive_tags et ON a.id = et.event_id
        LEFT JOIN tags t ON et.tag_id = t.id
        LEFT JOIN archive_participants ep ON a.id = ep.event_id
        LEFT JOIN participants p ON ep.participant_id = p.id
        GROUP BY a.id";
$result = $connection->query($sql);

// Создание нового объекта Spreadsheet
$spreadsheet = new Spreadsheet();

// Получение активного листа
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Архив');

// Добавление заголовков столбцов
$headerColumns = ['Название', 'Дата публикации', 'Дата начала', 'Время начала', 'Дата окончания', 'Время окончания', 'Описание', 'Теги', 'Сторонние участники'];
foreach ($headerColumns as $index => $columnTitle) {
    $cell = chr(65 + $index) . '1';
    $sheet->setCellValue($cell, $columnTitle);
    $sheet->getStyle($cell)->getFont()->setBold(true); // Задание жирного шрифта для заголовков
}

// Функция для удаления HTML-тегов из строки
function stripTags($html) {
    // Удаляем все HTML-теги из строки
    return preg_replace('/<[^>]*>/', '', $html);
}

function formatTimeForDisplay($time) {
    $parts = explode(':', $time);
    return "{$parts[0]}:{$parts[1]}"; 
}

function formatDateForDisplay($date) {
    $date_obj = date_create($date);
    return date_format($date_obj, 'd.m.Y'); 
}

// Добавление данных из базы данных в файл Excel
// Добавление данных из базы данных в файл Excel
if ($result->num_rows > 0) {
    $rowNumber = 2;
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNumber, $row['title']);
        $sheet->setCellValue('B' . $rowNumber, formatDateForDisplay($row['publish_date']));
        $sheet->setCellValue('C' . $rowNumber, formatDateForDisplay($row['start_date']));
        $sheet->setCellValue('D' . $rowNumber, formatTimeForDisplay($row['start_time']));
        $sheet->setCellValue('E' . $rowNumber, formatDateForDisplay($row['end_date']));
        $sheet->setCellValue('F' . $rowNumber, formatTimeForDisplay($row['end_time']));

        // Преобразование HTML-текста в читаемый формат
        $text = stripTags(html_entity_decode($row['text']));
        $sheet->setCellValue('G' . $rowNumber, $text);

        $sheet->setCellValue('H' . $rowNumber, $row['tags']);
        $sheet->setCellValue('I' . $rowNumber, $row['participants']);

        $rowNumber++;
    }
}

// Создание второго листа для отметок о присутствии
$attendanceSheet = $spreadsheet->createSheet();
$attendanceSheet->setTitle('Присутствие');

// Добавление заголовков столбцов для отметок о присутствии
$attendanceHeaderColumns = ['Название мероприятия', 'Присутствующие'];
foreach ($attendanceHeaderColumns as $index => $columnTitle) {
    $cell = chr(65 + $index) . '1';
    $attendanceSheet->setCellValue($cell, $columnTitle);
    $attendanceSheet->getStyle($cell)->getFont()->setBold(true); // Задание жирного шрифта для заголовков
}

// Запрос к базе данных для получения данных о присутствии пользователей
$sqlAttendance = "SELECT a.title AS event_title, GROUP_CONCAT(DISTINCT u.username SEPARATOR ', ') AS attendees
                  FROM archive a
                  LEFT JOIN archive_attendance aa ON a.id = aa.event_id
                  LEFT JOIN users u ON aa.user_id = u.id
                  WHERE aa.presence = 1
                  GROUP BY a.id";
$resultAttendance = $connection->query($sqlAttendance);

// Добавление данных о присутствии в файл Excel
if ($resultAttendance->num_rows > 0) {
    $attendanceRowNumber = 2;
    while ($row = $resultAttendance->fetch_assoc()) {
        $attendanceSheet->setCellValue('A' . $attendanceRowNumber, $row['event_title']);
        $attendanceSheet->setCellValue('B' . $attendanceRowNumber, $row['attendees']);
        $attendanceRowNumber++;
    }
}

// Автоматическое растягивание столбцов на обоих листах
foreach (range('A', 'I') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
    $attendanceSheet->getColumnDimension($column)->setAutoSize(true);
}

// Создание объекта записи и сохранение файла Excel
$filename = 'archive_data.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

// Закрытие соединения с базой данных
$connection->close();


// Отправка заголовков HTTP для скачивания файла
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Length: ' . filesize($filename));

// Отправка содержимого файла
readfile($filename);

// Удаление временного файла
unlink($filename);
?>
