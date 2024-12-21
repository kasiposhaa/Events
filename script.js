// Функция для прокрутки страницы вверх
function scrollToTop() {
    document.body.scrollTop = 0; // Для Safari
    document.documentElement.scrollTop = 0; // Для Chrome, Firefox, IE и Opera
}

// Показать кнопку при прокрутке страницы вниз
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("scrollBtn").style.display = "block";
    } else {
        document.getElementById("scrollBtn").style.display = "none";
    }
}

function redirectToPage() {
    window.location.href = 'view_events.php';
    return false; // Предотвращает стандартное поведение отправки формы
}

// Проверка уникальности значений в поле выбора тегов
function checkUniqueTags() {
    var tags = document.getElementById("tags");
    var selectedTags = [];
    for (var i = 0; i < tags.options.length; i++) {
        if (tags.options[i].selected) {
            if (selectedTags.includes(tags.options[i].value)) {
                alert("Теги не могут повторяться.");
                return false;
            }
            selectedTags.push(tags.options[i].value);
        }
    }
    return true;
}

// Проверка уникальности значений в поле выбора участников
function checkUniqueParticipants() {
    var participants = document.getElementById("participants");
    var selectedParticipants = [];
    for (var i = 0; i < participants.options.length; i++) {
        if (participants.options[i].selected) {
            if (selectedParticipants.includes(participants.options[i].value)) {
                alert("Участники не могут повторяться.");
                return false;
            }
            selectedParticipants.push(participants.options[i].value);
        }
    }
    return true;
}

// Проверка уникальности при отправке формы
function validateForm() {
    return checkUniqueTags() && checkUniqueParticipants();
}

function moveToArchive() {
    // Создание AJAX-запроса
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "move_to_archive.php", true); // Использование GET-запроса
    xhr.onload = function() {
        if (xhr.status == 200) {
            console.log("Мероприятия перенесены в архив"); // Сообщение об успешном выполнении
        } else {
            console.error("Ошибка при переносе: " + xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error("Ошибка сети");
    };
    xhr.send(); // Отправка запроса
}

function deleteEvent(eventId) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "delete_event.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                location.reload(); // Обновить страницу
            } else {
                alert("Ошибка при удалении мероприятия");
            }
        }
    };

    xhr.send("event_id=" + eventId); // Отправить идентификатор мероприятия
}

// Функция для валидации полей загрузки файлов
function validateFileField(inputId) {
    var fieldValue = document.getElementById(inputId).value;
    if (fieldValue !== null && fieldValue !== "" && !fieldValue.startsWith("uploads")) {
        // Если значение не соответствует условию, заменяем его на null
        document.getElementById(inputId).value = "null";
    }
}
