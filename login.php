<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="form.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Авторизация</title>
</head>
<body>
    <div class="container">
        <div class="center">
            <h1>Авторизация</h1>
            <form id="loginForm" method="post">
                <div class="txt_field">
                    <input type="text" name="username" required>
                    <span></span>
                    <label>Логин</label>
                </div>
                <div class="txt_field">
                    <input type="password" name="password" required>
                    <span></span>
                    <label>Пароль</label>
                </div>
                <input name="submit" type="submit" value="Войти">
                <br></br>
            </form>
        </div>
    </div>
    <div class="notification" id="notification"></div>

    <script>
        $(document).ready(function() {
            // Обрабатываем отправку формы с помощью jQuery Ajax
            $('#loginForm').on('submit', function(event) {
                event.preventDefault(); // Предотвращаем стандартную отправку формы

                $.ajax({
                    type: 'POST',
                    url: 'login_process.php',
                    data: $(this).serialize(), // Сериализуем данные формы для отправки на сервер
                    dataType: 'json',
                    success: function(response) {
                        let notification = $('#notification');
                        if (response.status === 'success') {
                            // Успешная авторизация
                            notification.text('Успешный вход! Перенаправление...');
                            notification.removeClass('error').fadeIn();
                            notification.addClass('success').fadeIn();
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 2000);
                        } else {
                            // Ошибка авторизации
                            notification.text(response.message);
                            notification.removeClass('success').fadeIn();
                            notification.addClass('error').fadeIn();
                            setTimeout(function() {
                                notification.fadeOut();
                            }, 3000);
                        }
                    },
                    error: function() {
                        // Обработка ошибки Ajax-запроса
                        let notification = $('#notification');
                        notification.text('Произошла ошибка. Попробуйте снова.');
                        notification.removeClass('success').fadeIn();
                        notification.addClass('error').fadeIn();
                        setTimeout(function() {
                            notification.fadeOut();
                        }, 3000);
                    }
                });
            });
        });
    </script>
</body>
</html>
