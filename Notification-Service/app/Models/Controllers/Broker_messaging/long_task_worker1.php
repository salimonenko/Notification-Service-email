<?php
// Для отправки срочных сообщений (заготовка)

ob_implicit_flush(true);
//ob_end_flush();

defined('ACCESS') or define('ACCESS', 'permit');

require_once '../../../../config/determine_absolute_PATH.php'; // Определяем путь до основного каталога
defined('PATH_ABSOLUTE') or define('PATH_ABSOLUTE', PATH(__DIR__ , 'Notification-Service'));
require_once PATH_ABSOLUTE . '/config/parameters.php';
if(!function_exists('http_response_code')) {require_once PATH_ABSOLUTE . '/app/Http/http_response_code.php';}

// 1. Получаем GET-параметры
if (isset($_GET['path_ABSOLUTE'])) {
    $path_ABSOLUTE = $_GET['path_ABSOLUTE'];
} else {
    $path_ABSOLUTE = '';
    http_response_code(510);
    throw new ErrorException("Ошибка: cURL в GET-запросе почему-то не передала переменную path_ABSOLUTE.\n 1");
}



// 2. Подключаем модули
require_once $path_ABSOLUTE . '/app/Exceptions/errors_exceptions.php';
if (!function_exists('http_response_code')) {
    require_once $path_ABSOLUTE . '/app/Http/http_response_code.php';
}
require_once $path_ABSOLUTE . '/config/parameters.php';
//  соединение с базой данных
require $path_ABSOLUTE . '/database/database.php';
// Подключаем класс для обработки запросов и передачи их в БД
require_once $path_ABSOLUTE . '/app/Models/CRUD_methods.php';
require_once $path_ABSOLUTE . '/app/Models/Controllers/die_echo_JSON.php';
// Функция, непосредственно отправляющая e-mail (заглушка)
require_once 'send_repeat_email.php';

if (FLAG_MESS_ECHO) {
    echo "Начинаем итерацию работы срочной очереди рассылки сообщений... \n";
    flush();
}


function longRunningTask() {

    for ($i = 1; $i <= USERS_NUM_TO_TAKE; $i++) {

        if (FLAG_MESS_ECHO) {
            echo "Эмуляция рассылки срочных сообщений (о транзакциях и пр.), операция (worker1): шаг $i\n";
            flush();
        }

        usleep(1); // Чтобы не отправлять все срочные сообщения сразу без перерыва. Только для демонстрации асинхронности
    }
    echo "Эмуляция рассылки срочных сообщений - операция (worker1) завершена\n";
    flush();
}

longRunningTask();
